<?php

use App\Http\Controllers\CityController;
use App\Http\Controllers\LocomotiveController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChirpController;
//use App\Http\Controllers\TownController;
use App\Http\Controllers\ShopController;
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
    Route::post('/{locomotive}/upgrade', 'upgrade')->name('locomotive.upgrade');
    Route::post('/{locomotive}/repair', 'repair')->name('locomotive.repair');
    Route::patch('{locomotive}/rename', 'rename')->name('locomotive.rename');
});

Route::controller(WagonController::class)->prefix('wagon')->group(function () {
    Route::post('/{wagon}/upgrade', 'upgrade')->name('wagon.upgrade');
    Route::post('/{wagon}/repair', 'repair')->name('wagon.repair');
    Route::patch('{wagon}/rename', 'rename')->name('wagon.rename');
});

Route::controller(WeaponController::class)->prefix('weapon')->group(function () {
    Route::post('/{weapon}/upgrade', 'upgrade')->name('weapon.upgrade');
    Route::patch('{weapon}/rename', 'rename')->name('weapon.rename');
});

Route::controller(CityController::class)->group(function () {
    Route::get('/city/{city}', 'show')->name('city.show');
    Route::post('/travel/{route}', 'travel')->name('travel');
    Route::post('/city/refuel', 'refuel')->name('city.refuel');
});

Route::controller(WorkshopController::class)->prefix('workshop')->group(function () {
    Route::get('/city/', 'index')->name('workshop.index');
});

Route::controller(ShopController::class)->prefix('shop')->group(function () {
    Route::get('/city/', 'index')->name('shop.index');
    Route::post('/locomotive/{uuid}/buy', 'buyLocomotive')->name('shop.locomotive.buy');
    Route::post('/wagon/{uuid}/buy', 'buyWagon')->name('shop.wagon.buy');
    Route::delete('/wagon/{wagon}/sell', 'sellWagon')->name('shop.wagon.sell');
    Route::post('/weapon/{shop_uuid}/buy', 'buyWeapon')->name('shop.weapon.buy');
    Route::delete('/weapon/{weapon}/sell', 'sellWeapon')->name('shop.weapon.sell');
    Route::post('/buy/resource/{resource:slug}', 'buyResource')->name('shop.resource.buy');
    Route::delete('/sell/resource/{resource:slug}', 'sellResource')->name('shop.resource.sell');
});
