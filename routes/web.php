<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\BanniereController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductFaqController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PwaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques — Landing page & catalogue
|--------------------------------------------------------------------------
| /manifest.webmanifest, /offline et /sw.js sont conservés tels quels :
| ce sont des conventions PWA (le navigateur et le service worker les attendent).
*/

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');

Route::get('/sw.js', function () {
    return response(file_get_contents(public_path('sw.js')), 200, [
        'Content-Type'           => 'application/javascript; charset=UTF-8',
        'Service-Worker-Allowed' => '/',
        'Cache-Control'          => 'no-cache',
    ]);
})->withoutMiddleware(['web']);

// avant : /catalogue/{product:slug}
Route::get('/v3dz-k8m/{product:slug}', [LandingController::class, 'show'])->name('products.show');



/*
|--------------------------------------------------------------------------
| Panier
|--------------------------------------------------------------------------
*/

Route::prefix('kj5z-d5e')->name('cart.')->group(function () {
    Route::get('/',          [CartController::class, 'index'])->name('index');
    Route::post('/',         [CartController::class, 'store'])->name('store');
    Route::get('/k5ez-8xe',  [CartController::class, 'summary'])->name('summary');
    Route::delete('/',       [CartController::class, 'clear'])->name('clear');
    Route::patch('/{item}',  [CartController::class, 'update'])->name('update')->whereUuid('item');
    Route::delete('/{item}', [CartController::class, 'destroy'])->name('destroy')->whereUuid('item');
});

/*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/

Route::prefix('k2pz-dz1')->name('checkout.')->group(function () {
    Route::get('/',          [CheckoutController::class, 'index'])->name('index');
    Route::post('/lx3m-q7w', [CheckoutController::class, 'storeAsGuest'])->name('guest');
    Route::post('/p9rz-n4k', [CheckoutController::class, 'loginAsExistingCustomer'])->name('login');
    Route::post('/',         [CheckoutController::class, 'storeAsAuthenticated'])->middleware('auth')->name('store');
});

/*
|--------------------------------------------------------------------------
| ╔═══════════════════════════════════════════════════════════════╗
| ║  BACK-OFFICE ADMIN — Authentification (routes PUBLIQUES)      ║
| ║                                                               ║
| ║  IMPORTANT : ces routes doivent être déclarées AVANT          ║
| ║  le groupe middleware(['auth', 'is.admin']) car elles sont    ║
| ║  publiques (login, forgot password…)                          ║
| ╚═══════════════════════════════════════════════════════════════╝
|--------------------------------------------------------------------------
*/

// Nom 'password.reset' conservé (utilisé par la notification de reset de Laravel)
// avant : /reinitialiser/{token}
Route::middleware('guest')->group(function () {
    Route::get('/n8rz-b2q/{token}', [AdminAuthController::class, 'showResetPassword'])->name('password.reset');
});

Route::prefix('z9qz-e3m')->name('admin.')->group(function () {

    // ── Routes guest (redirige si déjà connecté) ─────────────────────
    Route::middleware('guest')->group(function () {

        // avant : /admin/login (hors du préfixe) puis /login. Nom final : 'admin.login'
        Route::get('/a3lz-w9v',  [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/a3lz-w9v', [AdminAuthController::class, 'login'])->name('login.post');

        // avant : /mot-de-passe-oublie
        Route::get('/f7kz-m2y',  [AdminAuthController::class, 'showForgotPassword'])->name('password.forgot');
        Route::post('/f7kz-m2y', [AdminAuthController::class, 'sendResetLink'])->name('password.email');

        // avant : /reinitialiser/{token} et /reinitialiser
        Route::get('/e5pz-t8h/{token}', [AdminAuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/e5pz-t8h',        [AdminAuthController::class, 'resetPassword'])->name('password.update');
    });

    // ── Logout (nécessite d'être connecté) ───────────────────────────
    // avant : /logout
    Route::post('/x4nz-g6c', [AdminAuthController::class, 'logout'])->middleware('auth')->name('logout');

    // ── Routes protégées admin ────────────────────────────────────────
    // must.change.password : un administrateur créé avec un mot de passe provisoire est
    // redirigé vers /m7vz-c2j (PasswordChangeController) avant d'accéder au back-office.
    Route::middleware(['auth', 'is.admin', 'must.change.password'])->group(function () {

        // avant : banniere
        Route::prefix('q2mz-r9d')->name('banniere.')->controller(BanniereController::class)->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::put('/', 'update')->name('update');
            Route::delete('/y3tz-k6p', 'supprimerLogo')->name('logo.supprimer'); // avant : /logo
        });

        // Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Commandes
        Route::resource('b5kz-y7r', AdminOrderController::class)->names('orders')->parameters(['b5kz-y7r' => 'order']);

        // Produits
        Route::resource('d1wz-u4n', AdminProductController::class)->names('products')->parameters(['d1wz-u4n' => 'product']);

        // Sous-produits du package (avant : products/{product}/items)
        Route::post('d1wz-u4n/{product}/s3kz-a7j', [AdminProductController::class, 'storeItem'])->name('products.items.store');
        Route::delete('d1wz-u4n/{product}/s3kz-a7j/{item}', [AdminProductController::class, 'destroyItem'])->name('products.items.destroy');

        // Avis curatés (avant : products/{product}/reviews)
        Route::post('d1wz-u4n/{product}/t8hz-n5v', [ProductReviewController::class, 'store'])->name('products.reviews.store');
        Route::delete('d1wz-u4n/{product}/t8hz-n5v/{review}', [ProductReviewController::class, 'destroy'])->name('products.reviews.destroy');

        // FAQs (avant : products/{product}/faqs)
        Route::post('d1wz-u4n/{product}/w4rz-y2e', [ProductFaqController::class, 'store'])->name('products.faqs.store');
        Route::delete('d1wz-u4n/{product}/w4rz-y2e/{faq}', [ProductFaqController::class, 'destroy'])->name('products.faqs.destroy');


        // Catégories
        Route::resource('j8cz-v2s', AdminCategoryController::class)->names('categories')->parameters(['j8cz-v2s' => 'category']);

        // Utilisateurs

        // Liste des utilisateurs
        Route::get('u7kz-p3x', [UserController::class, 'index'])->name('users.index');

        // Détail d’un utilisateur
        Route::get('u7kz-p3x/{user}', [UserController::class, 'show'])->name('users.show');

        // avant : /toggle-active
        Route::post('u7kz-p3x/{user}/c9vz-l4b', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        // avant : /reset-password
        Route::post('u7kz-p3x/{user}/j3xz-q8f', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Administrateurs — réservé au super_admin (contrôle aussi fait dans le contrôleur : 403 sinon)
        Route::prefix('h4qz-t6n')->name('administrators.')->controller(AdministratorController::class)->group(function () {
            Route::get('/',                    'index')->name('index');
            Route::post('/',                   'store')->name('store');
            Route::put('/{id}',                'update')->name('update')->whereUuid('id');
            Route::post('/{id}/k6wz-h1s',      'toggleActive')->name('toggle-active')->whereUuid('id');  // avant : /toggle-active
            Route::post('/{id}/m5cz-d9t',      'resetAccess')->name('reset-access')->whereUuid('id');    // avant : /reset-access
            Route::delete('/{id}',             'destroy')->name('destroy')->whereUuid('id');            // suppression douce
            Route::post('/{id}/b8qz-n3r',      'restore')->name('restore')->whereUuid('id');             // avant : /restore
            Route::delete('/{id}/y7gz-e2u',    'forceDestroy')->name('force-destroy')->whereUuid('id'); // définitive (avant : /force)
        });
    });
});

/*
|--------------------------------------------------------------------------
| Changement de mot de passe forcé (commun admin + client)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/m7vz-c2j',  [PasswordChangeController::class, 'edit'])->name('password.change');
    Route::post('/m7vz-c2j', [PasswordChangeController::class, 'update'])->name('password.change.store');
});

/*
|--------------------------------------------------------------------------
| Espace client
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'must.change.password'])->prefix('r4bz-h8t')->name('customer.')->group(function () {
    Route::get('/',           [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/w6nz-s1e',   [CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/w6nz-s1e', [CustomerProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'must.change.password'])->prefix('f2tz-x5p')->name('customer.orders.')->group(function () {
    Route::get('/',                     [CustomerDashboardController::class, 'orders'])->name('index');
    Route::get('/{order:order_number}', [CustomerDashboardController::class, 'showOrder'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Logout global (Laravel Breeze / Fortify compatible)
|--------------------------------------------------------------------------
| Si vous utilisez Breeze ou Fortify, retirez cette route.
| Sinon elle sert de fallback pour le form de logout du layout admin.
*/

// avant : /logout
Route::post('/s9tz-v6k', function () {

    Auth::logout();

    request()->session()->invalidate();

    request()->session()->regenerateToken();

    return redirect()->route('admin.login');

})->middleware('auth')->name('logout');