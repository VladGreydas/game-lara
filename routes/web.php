<?php

use App\Http\Controllers\CityController;
use App\Http\Controllers\LocomotiveController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WagonController;
use App\Http\Controllers\WeaponController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\SaloonController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlayerController as AdminPlayerController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Admin\RouteController as AdminRouteController;
use App\Http\Controllers\Admin\ContractController as AdminContractController;
use App\Http\Controllers\Admin\ResourceController as AdminResourceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Не забудьте додати цей use

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
| Auth group routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Профіль користувача
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
    Route::post('/{player}/scout', [PlayerController::class, 'scout'])->name('player.scout')->middleware('can:update,player');
    Route::delete('/{player}', [PlayerController::class, 'destroy'])->name('player.destroy');
});

Route::get('/map', [MapController::class, 'index'])->name('map.index');

Route::post('/locale/{locale}', function ($locale) {
    session()->put('locale', $locale);
    return back();
})->name('locale.set');

Route::controller(LocomotiveController::class)->prefix('locomotive')->group(function () {
    Route::post('/{locomotive}/upgrade', 'upgrade')->name('locomotive.upgrade');
    Route::post('/{locomotive}/repair', 'repair')->name('locomotive.repair');
    Route::patch('{locomotive}/rename', 'rename')->name('locomotive.rename');
});

    // Вагони
    Route::controller(WagonController::class)->prefix('wagon')->group(function () {
        Route::post('/{wagon}/upgrade', 'upgrade')->name('wagon.upgrade');
        Route::post('/{wagon}/repair', 'repair')->name('wagon.repair');
        Route::patch('{wagon}/rename', 'rename')->name('wagon.rename');
    });

    // Зброя
    Route::controller(WeaponController::class)->prefix('weapon')->group(function () {
        Route::post('/{weapon}/upgrade', 'upgrade')->name('weapon.upgrade');
        Route::patch('{weapon}/rename', 'rename')->name('weapon.rename');
    });

    // Міста
    Route::controller(CityController::class)->group(function () {
        Route::get('/city/{city?}', 'show')->name('city.show');
        // Маршрут travel тепер запускає подорож і оновлює статус гравця
        Route::post('/travel/{destination}', 'travel')->name('city.travel');
        Route::post('/city/refuel', 'refuel')->name('city.refuel');
        // ✅ Нові маршрути для покращення
        Route::post('/city/upgrade', 'upgradeCity')->name('city.upgrade');
        Route::post('/city/resource/{cityResource}/upgrade', 'upgradeResource')->name('city.resource.upgrade');
        Route::get('/city/{city}/saloon', [SaloonController::class, 'show'])->name('city.saloon.show');
    });

    // Контракти
    Route::controller(ContractController::class)->prefix('contracts')->group(function () {
        Route::post('/city/{city}/{contract}/accept', 'accept')->name('contracts.accept');
        Route::post('/{contract}/deliver', 'deliver')->name('contracts.deliver');
    });

    // Локації
    Route::controller(LocationController::class)->group(function () {
        Route::get('/locations/{location}', 'show')->name('locations.show');
        Route::post('/locations/{location}/collect', 'collect')->name('locations.collect');
        Route::post('/locations/{location}/refuel', 'refuel')->name('locations.refuel');
        Route::post('/locations/{location}/unload', 'unload')->name('locations.unload');
        Route::post('/locations/{location}/upgrade', 'upgrade')->name('locations.upgrade');
    });

    // Travel events
    Route::post('/travel-event/resolve', [LocationController::class, 'resolveEvent'])->name('travel_event.resolve');

    // Майстерня
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

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
 */
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Players
    Route::get('/players', [AdminPlayerController::class, 'index'])->name('admin.players.index');
    Route::get('/players/{player}/edit', [AdminPlayerController::class, 'edit'])->name('admin.players.edit');
    Route::patch('/players/{player}', [AdminPlayerController::class, 'update'])->name('admin.players.update');

    // Cities
    Route::get('/cities', [AdminCityController::class, 'index'])->name('admin.cities.index');
    Route::get('/cities/{city}/edit', [AdminCityController::class, 'edit'])->name('admin.cities.edit');
    Route::patch('/cities/{city}', [AdminCityController::class, 'update'])->name('admin.cities.update');

    // Locations
    Route::get('/locations', [AdminLocationController::class, 'index'])->name('admin.locations.index');
    Route::get('/locations/{location}/edit', [AdminLocationController::class, 'edit'])->name('admin.locations.edit');
    Route::patch('/locations/{location}', [AdminLocationController::class, 'update'])->name('admin.locations.update');

    // Routes
    Route::get('/routes', [AdminRouteController::class, 'index'])->name('admin.routes.index');
    Route::get('/routes/create', [AdminRouteController::class, 'create'])->name('admin.routes.create');
    Route::post('/routes', [AdminRouteController::class, 'store'])->name('admin.routes.store');
    Route::delete('/routes/{cityRoute}', [AdminRouteController::class, 'destroy'])->name('admin.routes.destroy');

    // Contracts
    Route::get('/contracts', [AdminContractController::class, 'index'])->name('admin.contracts.index');
    Route::get('/contracts/create', [AdminContractController::class, 'create'])->name('admin.contracts.create');
    Route::post('/contracts', [AdminContractController::class, 'store'])->name('admin.contracts.store');
    Route::delete('/contracts/{contract}', [AdminContractController::class, 'destroy'])->name('admin.contracts.destroy');

    // Resources
    Route::get('/resources', [AdminResourceController::class, 'index'])->name('admin.resources.index');
    Route::get('/resources/{resource}/edit', [AdminResourceController::class, 'edit'])->name('admin.resources.edit');
    Route::patch('/resources/{resource}', [AdminResourceController::class, 'update'])->name('admin.resources.update');
});
