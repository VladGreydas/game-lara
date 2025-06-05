<?php

use App\Http\Controllers\CityController;
use App\Http\Controllers\LocomotiveController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChirpController;
//use App\Http\Controllers\TownController;
use App\Http\Controllers\WagonController;
use App\Http\Controllers\WeaponController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::view('/dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Chirps
|--------------------------------------------------------------------------
|
| Here are Chirps routes - some kind of chatting
| First project on Laravel, so I keep it
|
 */

Route::resource('chirps', ChirpController::class)
    ->only(['index', 'store', 'edit', 'update', 'destroy'])
    ->middleware(['auth', 'verified']);

/*
|--------------------------------------------------------------------------
| Auth group routes
|--------------------------------------------------------------------------
 */

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Game routes
|--------------------------------------------------------------------------
 */

Route::prefix('player')->group(function () {
    Route::get('/', [PlayerController::class, 'index'])->name('player.index');
    Route::post('store', [PlayerController::class, 'store'])->name('player.store');
    Route::get('/{player}/edit', [PlayerController::class, 'edit'])->name('player.edit');
    Route::patch('/{player}', [PlayerController::class, 'update'])->name('player.update');
    Route::post('/{player}/levelup', [PlayerController::class, 'levelUp'])->name('player.levelup')->middleware('can:update,player');
    Route::delete('/{player}', [PlayerController::class, 'destroy'])->name('player.destroy');
});

Route::controller(LocomotiveController::class)->prefix('locomotive')->group(function () {
    Route::patch('/{locomotive}/upgrade', 'upgrade')->name('locomotive.upgrade');
    Route::patch('/{locomotive}/purchase', 'purchase')->name('locomotive.purchase');
    Route::patch('{locomotive}/rename', 'rename')->name('locomotive.rename');
});

Route::controller(WagonController::class)->prefix('wagon')->group(function () {
//    Route::patch('/{wagon}/upgrade', 'upgrade')->name('wagon.upgrade');
//    Route::patch('/{train}/wagon/purchase', 'purchase')->name('wagon.purchase');
//    Route::delete('{wagon}/sell', 'sell')->name('wagon.sell');
    Route::patch('{wagon}/rename', 'rename')->name('wagon.rename');
});

Route::controller(WeaponController::class)->prefix('weapon')->group(function () {
//    Route::patch('/{weapon}/upgrade', 'upgrade')->name('weapon.upgrade');
//    Route::patch('/purchase', 'purchase')->name('weapon.purchase');
//    Route::delete('{weapon}/sell', 'sell')->name('weapon.sell');
    Route::patch('{weapon}/rename', 'rename')->name('weapon.rename');
});

Route::controller(CityController::class)->group(function () {
    Route::get('/city', 'show')->name('city.show');
    Route::post('/travel/{route}', 'travel')->name('travel');
    Route::post('/city/refuel', 'refuel')->name('city.refuel');
});

Route::controller(WorkshopController::class)->prefix('workshop')->group(function () {
    Route::get('/', 'index')->name('workshop.index');

    Route::post('/repair-locomotive', 'repairLocomotive')->name('workshop.repair.locomotive');

    Route::post('/upgrade-wagon/{wagon}', 'upgradeWagon')->name('workshop.upgrade.wagon');

    Route::post('/upgrade-weapon/{weapon}', 'upgradeWeapon')->name('workshop.upgrade.weapon');
});
