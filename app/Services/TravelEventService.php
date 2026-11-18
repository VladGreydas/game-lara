<?php

namespace App\Services;

use App\Models\CargoWagonResource;
use App\Models\CargoWagon;
use App\Models\Player;
use App\Models\Resource;
use App\Models\Weapon;
use App\Models\Wagon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TravelEventService
{
    /**
     * Progress points where events can trigger.
     */
    public const EVENT_PROGRESS_POINTS = [20, 40, 60, 80];

    /**
     * Check if an event should be triggered and trigger it.
     * If the player was away and missed multiple events, trigger them sequentially.
     */
    public function checkAndTrigger(Player $player): void
    {
        if (!$player->isTraveling()) {
            return;
        }

        // Check if there's already an active unresolved event
        if ($this->getActiveEvent($player)) {
            return;
        }

        $progress = $this->getTravelProgress($player);
        if ($progress === null) {
            return;
        }

        // Find ALL missed event progress points (player was away)
        $missedPoints = [];
        foreach (self::EVENT_PROGRESS_POINTS as $point) {
            if ($progress >= $point) {
                $alreadyTriggered = DB::table('player_travel_events')
                    ->where('player_id', $player->id)
                    ->where('travel_start_at', $player->travel_starts_at)
                    ->where('event_progress', $point)
                    ->exists();

                if (!$alreadyTriggered) {
                    $missedPoints[] = $point;
                }
            }
        }

        if (empty($missedPoints)) {
            return;
        }

        // Trigger the first missed event (player will resolve sequentially)
        $firstMissed = $missedPoints[0];
        $eventSlug = $this->pickRandomEvent();
        if ($eventSlug === null) {
            return;
        }

        // Trigger the event
        $this->triggerEvent($player, $firstMissed, $eventSlug);
    }

    /**
     * Get the count of unresolved events for the current travel.
     */
    public function getUnresolvedEventCount(Player $player): int
    {
        return (int) DB::table('player_travel_events')
            ->where('player_id', $player->id)
            ->where('travel_start_at', $player->travel_starts_at)
            ->whereNull('resolved_at')
            ->count();
    }

    /**
     * Get the active unresolved event for the player.
     */
    public function getActiveEvent(Player $player): ?array
    {
        $record = DB::table('player_travel_events')
            ->where('player_id', $player->id)
            ->where('travel_start_at', $player->travel_starts_at)
            ->whereNull('resolved_at')
            ->orderBy('triggered_at', 'desc')
            ->first();

        if (!$record) {
            return null;
        }

        $eventConfig = config('travel_events.' . $record->event_slug);
        if (!$eventConfig) {
            return null;
        }

        return [
            'id' => $record->id,
            'slug' => $record->event_slug,
            'progress' => $record->event_progress,
            'type' => $eventConfig['type'],
            'title' => __($eventConfig['title_key']),
            'description' => __($eventConfig['description_key']),
            'choices' => $this->getAvailableChoices($player, $eventConfig),
        ];
    }

    /**
     * Resolve an event with the player's choice.
     */
    public function resolveEvent(Player $player, int $eventId, string $choiceLabel): array
    {
        $record = DB::table('player_travel_events')
            ->where('id', $eventId)
            ->where('player_id', $player->id)
            ->whereNull('resolved_at')
            ->first();

        if (!$record) {
            return ['success' => false, 'message' => 'Event not found.'];
        }

        $eventConfig = config('travel_events.' . $record->event_slug);
        if (!$eventConfig) {
            return ['success' => false, 'message' => 'Event config not found.'];
        }

        // Find the matching choice
        $choice = null;
        foreach ($eventConfig['choices'] as $c) {
            if (__($c['label_key']) === $choiceLabel) {
                $choice = $c;
                break;
            }
        }

        if (!$choice) {
            return ['success' => false, 'message' => 'Invalid choice.'];
        }

        // Calculate time delay from effects before applying
        $timeDelaySeconds = $this->calculateTimeDelay($choice['effect']);

        // Apply effects (without time delay — it's handled separately)
        $message = $this->applyEffects($player, $choice['effect'], $eventConfig);

        // Mark as resolved with time delay
        DB::table('player_travel_events')
            ->where('id', $eventId)
            ->update([
                'resolved_at' => now(),
                'choice_made' => $choiceLabel,
                'effects_applied' => json_encode($choice['effect']),
                'time_delay_seconds' => $timeDelaySeconds,
            ]);

        return ['success' => true, 'message' => $message];
    }

    /**
     * Get available choices filtered by conditions.
     */
    public function getAvailableChoices(Player $player, array $eventConfig): array
    {
        $choices = [];
        foreach ($eventConfig['choices'] as $choice) {
            if ($this->checkCondition($player, $choice['condition'])) {
                $choices[] = [
                    'label' => __($choice['label_key']),
                    'effect' => $choice['effect'],
                ];
            }
        }

        return $choices;
    }

    /**
     * Calculate time delay in seconds from an effect array.
     */
    protected function calculateTimeDelay(array $effect): int
    {
        if (empty($effect)) {
            return 0;
        }

        // Special effect types that may have time delays
        if (isset($effect['type'])) {
            if ($effect['type'] === 'treasure_hunt') {
                // 40% chance of 30 min delay
                if (rand(1, 100) > 60) {
                    return 1800; // 30 minutes
                }
            }
            return 0;
        }

        // Direct time delay
        if (isset($effect['time'])) {
            return $this->parseTimeToSeconds($effect['time']);
        }

        return 0;
    }

    /**
     * Parse time string to seconds. Supports formats: +0.5h, +30m, +1h, +2h
     */
    protected function parseTimeToSeconds(string $time): int
    {
        // Remove leading +
        $time = ltrim($time, '+');

        if (preg_match('/^(\d+(?:\.\d+)?)h$/', $time, $matches)) {
            return (int) round($matches[1] * 3600);
        }

        if (preg_match('/^(\d+(?:\.\d+)?)m$/', $time, $matches)) {
            return (int) round($matches[1] * 60);
        }

        if (preg_match('/^(\d+(?:\.\d+)?)s$/', $time, $matches)) {
            return (int) round($matches[1]);
        }

        return 0;
    }

    /**
     * Apply effects to the player.
     * Note: time delays are handled separately via calculateTimeDelay() and stored in time_delay_seconds.
     */
    protected function applyEffects(Player $player, array $effect, array $eventConfig): string
    {
        if (empty($effect)) {
            return '';
        }

        // Special effect types
        if (isset($effect['type'])) {
            return $this->applySpecialEffect($player, $effect, $eventConfig);
        }

        $messages = [];

        // Money
        if (isset($effect['money'])) {
            $amount = $effect['money'];
            $player->money = max(0, $player->money + $amount);
            $player->save();
        }

        // XP
        if (isset($effect['xp'])) {
            app(LevelingService::class)->addExperience($player, $effect['xp'], 'travel_event');
        }

        // Time delay is handled separately — stored in time_delay_seconds in resolveEvent()
        // Do NOT modify travel_finishes_at here, it's paused during events

        // Resource amount (specific resource)
        if (isset($effect['resource']) && isset($effect['resource_amount'])) {
            $resourceSlug = $effect['resource'];
            $amount = $effect['resource_amount'];
            if ($amount < 0) {
                $this->removeResource($player, $resourceSlug, abs($amount));
            }
        }

        // Resources percent (lose percentage of all cargo)
        if (isset($effect['resources_percent'])) {
            $percent = abs($effect['resources_percent']);
            $this->loseResourcePercent($player, $percent);
        }

        return implode(' ', $messages);
    }

    /**
     * Apply special effect types.
     */
    protected function applySpecialEffect(Player $player, array $effect, array $eventConfig): string
    {
        $type = $effect['type'];

        // Battle placeholder
        if ($type === 'battle_placeholder') {
            return __('travel_events.battle_placeholder');
        }

        // Treasure hunt
        if ($type === 'treasure_hunt') {
            if (rand(1, 100) <= 60) {
                // Found treasure
                $resource = Resource::inRandomOrder()->first();
                $amount = rand(5, 10);
                $money = 50;

                // Add resource to cargo
                $this->addResourceToCargo($player, $resource->slug, $amount);

                // Add money
                $player->money += $money;
                $player->save();

                // Add XP
                app(LevelingService::class)->addExperience($player, 15, 'travel_event');

                return __('travel_events.treasure_found', [
                    'amount' => $amount,
                    'resource' => $resource->name,
                    'money' => $money,
                ]);
            } else {
                // Nothing found — time delay handled by calculateTimeDelay()
                return __('travel_events.treasure_not_found');
            }
        }

        // Trade buy
        if ($type === 'trade_buy') {
            $resource = Resource::where('tier', 'T0')->inRandomOrder()->first();
            $amount = rand(5, 10);
            $price = (int) floor($resource->base_price * $amount * 0.7); // 30% discount

            $player->money = max(0, $player->money - $price);
            $player->save();

            $this->addResourceToCargo($player, $resource->slug, $amount);

            app(LevelingService::class)->addExperience($player, 5, 'travel_event');

            return __('travel_events.trade_buy', [
                'amount' => $amount,
                'resource' => $resource->name,
                'price' => $price,
            ]);
        }

        // Trade sell
        if ($type === 'trade_sell') {
            $resource = $this->getMostExpensiveResource($player);
            if ($resource) {
                $amount = min(rand(5, 10), $resource['quantity']);
                $price = (int) floor($resource['base_price'] * $amount * 1.3); // 30% bonus

                $player->money += $price;
                $player->save();

                $this->removeResource($player, $resource['slug'], $amount);

                app(LevelingService::class)->addExperience($player, 5, 'travel_event');

                return __('travel_events.trade_sell', [
                    'amount' => $amount,
                    'resource' => $resource['name'],
                    'price' => $price,
                ]);
            }
            return '';
        }

        return '';
    }

    /**
     * Check if a condition is met.
     */
    protected function checkCondition(Player $player, string $condition): bool
    {
        if ($condition === 'none') {
            return true;
        }

        // has_weapon
        if ($condition === 'has_weapon') {
            $weaponWagons = $player->train->wagons()
                ->where('type', 'weapon')
                ->with('weapon_wagon.weapons')
                ->get();

            foreach ($weaponWagons as $wagon) {
                if ($wagon->weapon_wagon && $wagon->weapon_wagon->weapons()->count() > 0) {
                    return true;
                }
            }
            return false;
        }

        // has_money:X
        if (str_starts_with($condition, 'has_money:')) {
            $amount = (int) substr($condition, strlen('has_money:'));
            return $player->money >= $amount;
        }

        // has_resource:Slug:Amount
        if (str_starts_with($condition, 'has_resource:')) {
            $parts = explode(':', substr($condition, strlen('has_resource:')));
            if (count($parts) === 2) {
                [$slug, $amount] = $parts;
                return $this->hasResource($player, $slug, (int) $amount);
            }
        }

        // has_any_resource:Amount
        if (str_starts_with($condition, 'has_any_resource:')) {
            $amount = (int) substr($condition, strlen('has_any_resource:'));
            return $this->hasAnyResource($player, $amount);
        }

        return true;
    }

    /**
     * Check if player has a specific resource.
     */
    protected function hasResource(Player $player, string $slug, int $amount): bool
    {
        $resource = Resource::where('slug', $slug)->first();
        if (!$resource) {
            return false;
        }

        $total = $this->getResourceQuantity($player, $resource->id);
        return $total >= $amount;
    }

    /**
     * Check if player has any resource with at least the given amount.
     */
    protected function hasAnyResource(Player $player, int $amount): bool
    {
        $cargoWagons = $player->train->wagons()
            ->where('type', 'cargo')
            ->with('cargo_wagon.resources')
            ->get();

        foreach ($cargoWagons as $wagon) {
            if (!$wagon->cargo_wagon) {
                continue;
            }
            foreach ($wagon->cargo_wagon->resources as $cr) {
                if ($cr->quantity >= $amount) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get total quantity of a resource in player's cargo wagons.
     */
    protected function getResourceQuantity(Player $player, int $resourceId): int
    {
        $cargoWagons = $player->train->wagons()
            ->where('type', 'cargo')
            ->with('cargo_wagon.resources')
            ->get();

        $total = 0;
        foreach ($cargoWagons as $wagon) {
            if (!$wagon->cargo_wagon) {
                continue;
            }
            foreach ($wagon->cargo_wagon->resources as $cr) {
                if ($cr->resource_id === $resourceId) {
                    $total += $cr->quantity;
                }
            }
        }

        return $total;
    }

    /**
     * Add resource to player's cargo wagons.
     */
    protected function addResourceToCargo(Player $player, string $slug, int $amount): void
    {
        $resource = Resource::where('slug', $slug)->first();
        if (!$resource) {
            return;
        }

        $cargoWagons = $player->train->wagons()
            ->where('type', 'cargo')
            ->with('cargo_wagon.resources')
            ->get();

        $remaining = $amount;
        foreach ($cargoWagons as $wagon) {
            if ($remaining <= 0 || !$wagon->cargo_wagon) {
                continue;
            }

            $freeSpace = $wagon->cargo_wagon->getRemainingCapacity();
            if ($freeSpace <= 0) {
                continue;
            }

            $toAdd = min($remaining, $freeSpace);
            $cr = CargoWagonResource::firstOrNew([
                'cargo_wagon_id' => $wagon->cargo_wagon->id,
                'resource_id' => $resource->id,
            ]);
            $cr->quantity = ($cr->quantity ?? 0) + $toAdd;
            $cr->save();

            $remaining -= $toAdd;
        }
    }

    /**
     * Remove resource from player's cargo wagons.
     */
    protected function removeResource(Player $player, string $slug, int $amount): void
    {
        $resource = Resource::where('slug', $slug)->first();
        if (!$resource) {
            return;
        }

        $cargoWagons = $player->train->wagons()
            ->where('type', 'cargo')
            ->with('cargo_wagon.resources')
            ->get();

        $remaining = $amount;
        foreach ($cargoWagons as $wagon) {
            if ($remaining <= 0 || !$wagon->cargo_wagon) {
                continue;
            }

            foreach ($wagon->cargo_wagon->resources as $cr) {
                if ($cr->resource_id === $resource->id && $cr->quantity > 0) {
                    $toRemove = min($remaining, $cr->quantity);
                    $cr->quantity -= $toRemove;
                    $cr->save();
                    $remaining -= $toRemove;

                    if ($cr->quantity <= 0) {
                        $cr->delete();
                    }

                    if ($remaining <= 0) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * Lose a percentage of all cargo resources.
     */
    protected function loseResourcePercent(Player $player, int $percent): void
    {
        $cargoWagons = $player->train->wagons()
            ->where('type', 'cargo')
            ->with('cargo_wagon.resources')
            ->get();

        foreach ($cargoWagons as $wagon) {
            if (!$wagon->cargo_wagon) {
                continue;
            }

            foreach ($wagon->cargo_wagon->resources as $cr) {
                $loss = (int) floor($cr->quantity * $percent / 100);
                if ($loss > 0) {
                    $cr->quantity -= $loss;
                    $cr->save();

                    if ($cr->quantity <= 0) {
                        $cr->delete();
                    }
                }
            }
        }
    }

    /**
     * Get the most expensive resource in player's cargo.
     */
    protected function getMostExpensiveResource(Player $player): ?array
    {
        $cargoWagons = $player->train->wagons()
            ->where('type', 'cargo')
            ->with('cargo_wagon.resources.resource')
            ->get();

        $mostExpensive = null;
        $maxPrice = 0;

        foreach ($cargoWagons as $wagon) {
            if (!$wagon->cargo_wagon) {
                continue;
            }

            foreach ($wagon->cargo_wagon->resources as $cr) {
                if ($cr->quantity >= 5 && $cr->resource) {
                    $price = $cr->resource->base_price;
                    if ($price > $maxPrice) {
                        $maxPrice = $price;
                        $mostExpensive = [
                            'slug' => $cr->resource->slug,
                            'name' => $cr->resource->name,
                            'base_price' => $cr->resource->base_price,
                            'quantity' => $cr->quantity,
                        ];
                    }
                }
            }
        }

        return $mostExpensive;
    }

    /**
     * Get travel progress as a percentage.
     */
    public function getTravelProgress(Player $player): ?int
    {
        if (!$player->isTraveling()) {
            return null;
        }

        $total = $player->travel_starts_at->diffInSeconds($player->travel_finishes_at);
        if ($total <= 0) {
            return 100;
        }

        $elapsed = $player->travel_starts_at->diffInSeconds(now());
        $progress = (int) min(100, ($elapsed / $total) * 100);

        return $progress;
    }

    /**
     * Pick a random event from config.
     */
    protected function pickRandomEvent(): ?string
    {
        $events = config('travel_events');
        if (empty($events)) {
            return null;
        }

        $slugs = array_keys($events);
        return $slugs[array_rand($slugs)];
    }

    /**
     * Trigger an event and pause the travel.
     */
    protected function triggerEvent(Player $player, int $progress, string $eventSlug): void
    {
        // Save the original travel_finishes_at before pausing
        $originalFinish = $player->travel_finishes_at->copy();

        // Set travel_finishes_at to far future (paused)
        $player->travel_finishes_at = $originalFinish->copy()->addHours(24);
        $player->save();

        // Store the event with the original finish time
        DB::table('player_travel_events')->insert([
            'player_id' => $player->id,
            'travel_start_at' => $player->travel_starts_at,
            'event_progress' => $progress,
            'event_slug' => $eventSlug,
            'original_travel_finishes_at' => $originalFinish,
            'time_delay_seconds' => 0,
            'triggered_at' => now(),
            'resolved_at' => null,
            'choice_made' => null,
            'effects_applied' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Resume travel after event resolution.
     * Restore the original travel_finishes_at and add any time delays from events.
     */
    public function resumeTravel(Player $player): void
    {
        if (!$player->isTraveling()) {
            return;
        }

        // Find all resolved events for this travel to accumulate time delays
        $resolvedEvents = DB::table('player_travel_events')
            ->where('player_id', $player->id)
            ->where('travel_start_at', $player->travel_starts_at)
            ->whereNotNull('resolved_at')
            ->orderBy('event_progress', 'asc')
            ->get();

        if ($resolvedEvents->isEmpty()) {
            return;
        }

        // Get the last resolved event to find the original finish time
        $lastEvent = $resolvedEvents->last();
        $originalFinish = Carbon::parse($lastEvent->original_travel_finishes_at);

        // Sum up all time delays from all resolved events
        $totalDelaySeconds = 0;
        foreach ($resolvedEvents as $event) {
            $totalDelaySeconds += $event->time_delay_seconds ?? 0;
        }

        // Set travel_finishes_at to the original time + accumulated delays
        $player->travel_finishes_at = $originalFinish->copy()->addSeconds($totalDelaySeconds);
        $player->save();
    }
}
