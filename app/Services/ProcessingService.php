<?php

namespace App\Services;

use App\Models\CargoWagonResource;
use App\Models\Location;
use App\Models\LocationResource;
use App\Models\Player;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessingService
{
    /**
     * Unload T0 resources from player's wagons and start T1 production.
     * Supports locations with one or two input resources.
     *
     * @param Player $player
     * @param Location $location
     * @param int $amount Amount of primary T0 resource to unload
     * @return array ['success' => bool, 'message' => string, 'output_amount' => int|null, 'second_input_amount' => int|null]
     */
    public function unloadResource(Player $player, Location $location, int $amount): array
    {
        $locationResource = $location->location_resource;

        if (!$locationResource) {
            return [
                'success' => false,
                'message' => 'location.error.no_resource',
                'output_amount' => null,
                'second_input_amount' => null,
            ];
        }

        // Check if this is a T1 location (has input_resource_id)
        if (!$locationResource->input_resource_id) {
            return [
                'success' => false,
                'message' => 'location.error.not_processing_location',
                'output_amount' => null,
                'second_input_amount' => null,
            ];
        }

        $inputResource = $locationResource->inputResource;
        if (!$inputResource) {
            return [
                'success' => false,
                'message' => 'location.error.input_resource_not_found',
                'output_amount' => null,
                'second_input_amount' => null,
            ];
        }

        // Check if player has enough primary T0 resource in wagons
        $availableAmount = $this->getInputResourceInWagons($player, $inputResource->id);

        if ($availableAmount < $amount) {
            return [
                'success' => false,
                'message' => 'location.error.not_enough_input_resource',
                'output_amount' => null,
                'second_input_amount' => null,
            ];
        }

        // Calculate how much of the second input resource is needed
        $secondInputAmount = 0;
        $hasSecondInput = (bool) $locationResource->input_resource_2_id;
        
        if ($hasSecondInput) {
            $inputResource2 = $locationResource->inputResource2;
            if (!$inputResource2) {
                return [
                    'success' => false,
                    'message' => 'location.error.input_resource_2_not_found',
                    'output_amount' => null,
                    'second_input_amount' => null,
                ];
            }

            // Calculate second input amount: ratio_input2 per batch of ratio_input primary
            // e.g., if ratio_input=2, ratio_input2=1, and amount=10, then second_input = (10 / 2) * 1 = 5
            $secondInputAmount = (int) ($amount * $locationResource->processing_ratio_input2 / $locationResource->processing_ratio_input);
            $secondInputAmount = max(1, $secondInputAmount); // At least 1 unit per operation

            // Check if player has enough second input resource
            $availableSecondAmount = $this->getInputResourceInWagons($player, $inputResource2->id);
            if ($availableSecondAmount < $secondInputAmount) {
                return [
                    'success' => false,
                    'message' => 'location.error.not_enough_input_resource_2',
                    'output_amount' => null,
                    'second_input_amount' => null,
                ];
            }
        }

        // Calculate T1 output amount
        $outputAmount = $this->calculateOutput(
            $amount,
            $locationResource->processing_ratio_input,
            $locationResource->processing_ratio_output
        );

        // Check if output would exceed max capacity
        $totalAfterProcessing = $locationResource->current_amount + $outputAmount;
        if ($totalAfterProcessing > $locationResource->max_capacity) {
            // Calculate max allowed output
            $maxOutput = max(0, $locationResource->max_capacity - $locationResource->current_amount);
            // Calculate max allowed input based on ratio
            $maxInput = (int) (($maxOutput * $locationResource->processing_ratio_input) / $locationResource->processing_ratio_output);
            if ($maxInput <= 0) {
                return [
                    'success' => false,
                    'message' => 'location.error.capacity_full',
                    'output_amount' => null,
                    'second_input_amount' => null,
                ];
            }
            $amount = $maxInput;
            $outputAmount = $this->calculateOutput(
                $amount,
                $locationResource->processing_ratio_input,
                $locationResource->processing_ratio_output
            );
            
            // Recalculate second input amount if adjusted
            if ($hasSecondInput) {
                $secondInputAmount = (int) ($amount * $locationResource->processing_ratio_input2 / $locationResource->processing_ratio_input);
                $secondInputAmount = max(1, $secondInputAmount);
            }
        }

        // Perform the unload and start processing
        DB::transaction(function () use ($player, $inputResource, $amount, $locationResource, $outputAmount, $hasSecondInput, &$secondInputAmount) {
            // Remove primary T0 from wagons
            $this->removeFromWagons($player, $inputResource->id, $amount);

            // Remove second T0 from wagons if needed
            if ($hasSecondInput && $secondInputAmount > 0) {
                $inputResource2 = $locationResource->inputResource2;
                if ($inputResource2) {
                    $this->removeFromWagons($player, $inputResource2->id, $secondInputAmount);
                }
            }

            // Add to processing queue
            $locationResource->processing_amount += $outputAmount;

            // Set processing start time if not already set
            if ($locationResource->processing_started_at === null) {
                $locationResource->processing_started_at = now();
            }

            $locationResource->save();
        });

        return [
            'success' => true,
            'message' => 'location.success.unloaded',
            'output_amount' => $outputAmount,
            'second_input_amount' => $secondInputAmount,
        ];
    }

    /**
     * Calculate T1 output amount from T0 input amount based on processing ratios.
     *
     * @param int $inputAmount Amount of T0 resource
     * @param int $ratioInput Input ratio (e.g., 1 for 1:2, 2 for 2:1)
     * @param int $ratioOutput Output ratio (e.g., 2 for 1:2, 1 for 2:1)
     * @return int Output amount of T1 resource
     */
    public function calculateOutput(int $inputAmount, int $ratioInput, int $ratioOutput): int
    {
        return (int) ($inputAmount * $ratioOutput / $ratioInput);
    }

    /**
     * Update production for a location resource, converting processing_amount to current_amount over time.
     * Integrates with existing LocationResource::produce() method.
     *
     * @param LocationResource $locationResource
     * @return void
     */
    public function produce(LocationResource $locationResource): void
    {
        // If there's no processing queue, use the standard produce method
        if ($locationResource->processing_amount <= 0) {
            $locationResource->produce();
            return;
        }

        // Calculate how much T1 has been produced from the processing queue
        $startedAt = Carbon::parse($locationResource->processing_started_at);
        $elapsedHours = $startedAt->diffInMinutes(now()) / 60;

        // Production rate: process all queued amount over time based on production_rate_per_hour
        $ratePerHour = $locationResource->production_rate_per_hour;
        if ($ratePerHour <= 0) {
            $ratePerHour = 10; // Default rate
        }

        // Calculate how much of the processing queue has been completed
        $producedFromQueue = (int) ($elapsedHours * $ratePerHour);
        $producedFromQueue = min($producedFromQueue, $locationResource->processing_amount);

        // Add produced amount to current_amount
        $total = $locationResource->current_amount + $producedFromQueue;
        if ($total >= $locationResource->max_capacity) {
            $producedFromQueue = max(0, $locationResource->max_capacity - $locationResource->current_amount);
            $total = $locationResource->max_capacity;
        }

        $locationResource->current_amount = $locationResource->current_amount + $producedFromQueue;
        $locationResource->processing_amount -= $producedFromQueue;

        // If all processing is done, reset processing_started_at
        if ($locationResource->processing_amount <= 0) {
            $locationResource->processing_started_at = null;
        }

        $locationResource->last_produced_at = now();
        $locationResource->save();
    }

    /**
     * Get processing status for UI display.
     *
     * @param LocationResource $locationResource
     * @return array Processing status information
     */
    public function getProcessingStatus(LocationResource $locationResource): array
    {
        $status = [
            'has_processing' => (bool) $locationResource->input_resource_id,
            'input_resource' => null,
            'input_resource_2' => null,
            'has_second_input' => false,
            'processing_amount' => $locationResource->processing_amount,
            'current_amount' => $locationResource->current_amount,
            'max_capacity' => $locationResource->max_capacity,
            'production_rate_per_hour' => $locationResource->production_rate_per_hour,
            'estimated_time_minutes' => 0,
            'progress_percent' => 0,
        ];

        if (!$locationResource->input_resource_id) {
            return $status;
        }

        $status['input_resource'] = $locationResource->inputResource;
        $status['ratio_input'] = $locationResource->processing_ratio_input;
        $status['ratio_output'] = $locationResource->processing_ratio_output;

        // Check for second input resource
        if ($locationResource->input_resource_2_id) {
            $status['has_second_input'] = true;
            $status['input_resource_2'] = $locationResource->inputResource2;
            $status['ratio_input2'] = $locationResource->processing_ratio_input2;
        }

        if ($locationResource->processing_amount > 0 && $locationResource->processing_started_at) {
            $startedAt = Carbon::parse($locationResource->processing_started_at);
            $elapsedMinutes = $startedAt->diffInMinutes(now());

            $ratePerHour = $locationResource->production_rate_per_hour;
            if ($ratePerHour <= 0) {
                $ratePerHour = 10;
            }

            // Total time needed to process all queued amount
            $totalMinutes = (int) (($locationResource->processing_amount + $elapsedMinutes * $ratePerHour / 60) / $ratePerHour * 60);
            $remainingMinutes = max(0, $totalMinutes - $elapsedMinutes);

            $status['estimated_time_minutes'] = $remainingMinutes;
            $status['progress_percent'] = min(100, (int) (($elapsedMinutes / 60 * $ratePerHour) / max(1, $locationResource->processing_amount + $elapsedMinutes / 60 * $ratePerHour) * 100));
        }

        return $status;
    }

    /**
     * Get the total amount of a specific T0 resource in player's wagons.
     *
     * @param Player $player
     * @param int $resourceId
     * @return int Total quantity of the resource in wagons
     */
    public function getInputResourceInWagons(Player $player, int $resourceId): int
    {
        $train = $player->train;
        if (!$train) {
            return 0;
        }

        $total = 0;
        foreach ($train->wagons->where('type', 'cargo') as $wagon) {
            $cargoWagon = $wagon->cargo_wagon;
            if (!$cargoWagon) {
                continue;
            }

            $cargoResource = CargoWagonResource::where('cargo_wagon_id', $cargoWagon->id)
                ->where('resource_id', $resourceId)
                ->first();

            if ($cargoResource && $cargoResource->quantity > 0) {
                $total += $cargoResource->quantity;
            }
        }

        return $total;
    }

    /**
     * Remove T0 resource from player's wagons.
     *
     * @param Player $player
     * @param int $resourceId
     * @param int $amount
     * @return void
     */
    protected function removeFromWagons(Player $player, int $resourceId, int $amount): void
    {
        $train = $player->train;
        if (!$train) {
            return;
        }

        $remaining = $amount;

        foreach ($train->wagons->where('type', 'cargo') as $wagon) {
            if ($remaining <= 0) {
                break;
            }

            $cargoWagon = $wagon->cargo_wagon;
            if (!$cargoWagon) {
                continue;
            }

            $cargoResource = CargoWagonResource::where('cargo_wagon_id', $cargoWagon->id)
                ->where('resource_id', $resourceId)
                ->first();

            if (!$cargoResource || $cargoResource->quantity <= 0) {
                continue;
            }

            $toRemove = min($remaining, $cargoResource->quantity);
            $cargoResource->quantity -= $toRemove;
            $remaining -= $toRemove;

            // Delete the record if quantity is 0
            if ($cargoResource->quantity <= 0) {
                $cargoResource->delete();
            } else {
                $cargoResource->save();
            }
        }
    }
}
