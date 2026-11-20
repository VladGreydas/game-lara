---
sessionId: session-261116-145310-1dok
---

# Requirements

### Overview & Goals
Створити повноцінну адмін-панель для керування всіма аспектами гри. Адмінка доступна тільки для користувачів з роллю `admin`.

### Admin Modules

| Модуль | Функціонал |
|---|---|
| **Dashboard** | Статистика: кількість гравців, міст, локацій, контрактів, активних подорожей |
| **Гравці** | Список, перегляд, редагування (рівень, гроші, досвід, позиція), видалення |
| **Міста** | Список, перегляд, редагування (рівень, ресурси, ціни), додавання/видалення |
| **Локації** | Список, перегляд, редагування (рівень, виробництво), додавання/видалення |
| **Маршрути** | Список, додавання/видалення маршрутів між містами та локаціями |
| **Контракти** | Список, створення, видалення, редагування контрактів |
| **Ресурси** | Список, редагування (ціни, пропорції переробки) |

### User Stories
- As an admin, I want to view all players and edit their stats so that I can manage the game.
- As an admin, I want to manage cities, locations, and routes so that I can configure the game world.
- As an admin, I want to view statistics so that I can monitor game health.

### Scope
**In Scope:**
- Окрема секція `/admin` з боковою навігацією
- 7 модулів: Dashboard, Гравці, Міста, Локації, Маршрути, Контракти, Ресурси
- CRUD операції для всіх модулів
- Темна тема (індустріальна/пост-апокаліпсис)
- Доступ тільки для адмінів

**Out of Scope:**
- API для адмінки
- Експорт/імпорт даних
- Логи дій адмінів

# Technical Design

### Current Implementation
- **User::isAdmin()** — перевірка ролі адміна через relцію з Role
- **routes/web.php** — існуючі маршрути гри
- **resources/views/layouts/app.blade.php** — основний layout з навігацією
- **Tailwind CSS** — індустріальна тема з темним фоном

### Key Decisions
1. **Окрема секція /admin** — всі маршрути з префіксом `/admin`
2. **Middleware** — створити `AdminMiddleware` для перевірки `User::isAdmin()`
3. **Окремий layout** — `admin.blade.php` з боковою навігацією
4. **Контролери** — `Admin\PlayerController`, `Admin\CityController` тощо
5. **View** — `admin/` папка з окремими view для кожного модуля

### Middleware
**`AdminMiddleware`** — перевірка чи користувач є адміном:
```php
public function handle($request, Closure $next):
    if (!auth()->user() || !auth()->user()->isAdmin()):
        abort(403);
    return $next($request);
```

### Controllers
**`Admin\DashboardController`** — статистика
**`Admin\PlayerController`** — CRUD гравців
**`Admin\CityController`** — CRUD міст
**`Admin\LocationController`** — CRUD локацій
**`Admin\RouteController`** — CRUD маршрутів
**`Admin\ContractController`** — CRUD контрактів
**`Admin\ResourceController`** — CRUD ресурсів

### Routes
```php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('players', PlayerController::class)->names('admin.players.*');
    Route::resource('cities', CityController::class)->names('admin.cities.*');
    Route::resource('locations', LocationController::class)->names('admin.locations.*');
    Route::resource('routes', RouteController::class)->names('admin.routes.*');
    Route::resource('contracts', ContractController::class)->names('admin.contracts.*');
    Route::resource('resources', ResourceController::class)->names('admin.resources.*');
});
```

### Views
**`admin/layout.blade.php`** — layout з боковою навігацією
**`admin/dashboard.blade.php`** — статистика
**`admin/players/index.blade.php`** — список гравців
**`admin/players/edit.blade.php`** — редагування гравця
**`admin/cities/index.blade.php`** — список міст
**`admin/cities/edit.blade.php`** — редагування міста
... (аналогічно для інших модулів)

### Architecture Diagram
```mermaid
graph LR
    A[Admin User] --> B[/admin middleware]
    B --> C{isAdmin?}
    C -->|Yes| D[Dashboard]
    C -->|No| E[403 Forbidden]
    D --> F[Players]
    D --> G[Cities]
    D --> H[Locations]
    D --> I[Routes]
    D --> J[Contracts]
    D --> K[Resources]
```

### File Structure
- **New:** `app/Http/Middleware/AdminMiddleware.php`
- **New:** `app/Http/Controllers/Admin/DashboardController.php`
- **New:** `app/Http/Controllers/Admin/PlayerController.php`
- **New:** `app/Http/Controllers/Admin/CityController.php`
- **New:** `app/Http/Controllers/Admin/LocationController.php`
- **New:** `app/Http/Controllers/Admin/RouteController.php`
- **New:** `app/Http/Controllers/Admin/ContractController.php`
- **New:** `app/Http/Controllers/Admin/ResourceController.php`
- **New:** `resources/views/admin/layout.blade.php`
- **New:** `resources/views/admin/dashboard.blade.php`
- **New:** `resources/views/admin/players/` — index, edit
- **New:** `resources/views/admin/cities/` — index, edit
- **New:** `resources/views/admin/locations/` — index, edit
- **New:** `resources/views/admin/routes/` — index, create, edit
- **New:** `resources/views/admin/contracts/` — index, create, edit
- **New:** `resources/views/admin/resources/` — index, edit
- **Modified:** `routes/web.php` — admin маршрути
- **Modified:** `app/Providers/RouteServiceProvider.php` — реєстрація middleware
- **New:** `resources/lang/en/admin.php` та `uk/admin.php`

# Testing

### Validation Approach
Перевірка синтаксису PHP для всіх змінених файлів.

### Key Scenarios
1. Адмін входить → бачить посилання на адмінку в меню
2. Звичайний користувач намагає відкрити /admin → 403 Forbidden
3. Адмін переглядає Dashboard → статистика відображається
4. Адмін редагує гравця → зміни зберігаються
5. Адмін додає місто → місто з'являється в списку
6. Адмін створює контракт → контракт доступний у грі

### Edge Cases
- Адмін намагає видалити себе → помилка
- Адмін редагує гравця під час подорожі → перевірка стану
- Видалення міста з маршрутами → каскадне видалення

# Delivery Steps

### ✓ Step 1: Middleware and layout
Створено middleware для перевірки адмінів та layout адмінки.
- `AdminMiddleware` — перевірка User::isAdmin(), abort(403) для неадмінів
- Зареєстровано в `app/Http/Kernel.php` як `'admin'`
- `admin/layout.blade.php` — layout з боковою навігацією, темна тема, посилання на вихід з адмінки

### ✓ Step 2: Dashboard and Players
Створено Dashboard та контролери для гравців та міст.
- `DashboardController` — статистика: гравці, міста, локації, контракти, активні подорожі
- `admin/dashboard.blade.php` — картки зі статистикою
- `AdminPlayerController` — index() список гравців, edit() форма, update() збереження
- `AdminCityController` — index() список міст, edit() форма, update() збереження

### ✓ Step 3: Cities, Locations, Routes, Contracts, Resources
Створено решту контролерів, view файлів та локалізацію.
- `AdminLocationController` — index(), edit(), update()
- `AdminRouteController` — index() з фільтрами (пошук, відстань, тип), групуванням двосторонніх маршрутів (city↔city, city↔location), create(), store(), destroy()
- `AdminContractController` — index(), create(), store(), destroy()
- `AdminResourceController` — index(), edit(), update()
- View файли для всіх модулів (12 файлів)
- Локалізація: en/admin.php та uk/admin.php

### ✓ Step 4: Pagination
Додано пагінацію з вибором кількості записів (5, 10, 20, 50, 100) для всіх списків адмінки.
- `PlayerController`, `CityController`, `LocationController`, `ContractController`, `ResourceController` — підтримка `?per_page` параметру
- `components/admin-pagination.blade.php` — Blade компонент з вибором кількості записів, номерами сторінок та інформацією про загальну кількість (переміщено з components/admin/ для сумісності з Laravel 13)
- Усі 5 index.blade.php файлів оновлено — використовують `<x-admin-pagination>`
- Локалізація: `per_page`, `showing`, `of` в en/admin.php та uk/admin.php

### ✓ Маршрути
Додано в `routes/web.php` — префікс `/admin`, middleware `['auth', 'admin']`
- admin.dashboard, admin.players.*, admin.cities.*, admin.locations.*
- admin.routes.*, admin.contracts.*, admin.resources.*