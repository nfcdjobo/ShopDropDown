<?php

use App\Http\Controllers\Admin\AdminAuthController;
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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques — Landing page & catalogue
|--------------------------------------------------------------------------
*/

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/catalogue/{product:slug}', [LandingController::class, 'show'])->name('products.show');

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
| ║  IMPORTANT : ces routes doivent être déclarées AVANT           ║
| ║  le groupe middleware(['auth', 'is.admin']) car elles sont     ║
| ║  publiques (login, forgot password…)                           ║
| ╚═══════════════════════════════════════════════════════════════╝
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/admin/login',  [AdminAuthController::class, 'showLogin'])->name('admin.login');
});

Route::prefix('z9qz-e3m')->name('admin.')->group(function () {

    // ── Routes guest (redirige si déjà connecté) ─────────────────────
    Route::middleware('guest')->group(function () {
        // ← NOMMÉE 'admin.login'

        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');

        Route::get('/mot-de-passe-oublie', [AdminAuthController::class, 'showForgotPassword'])->name('password.forgot');

        Route::post('/mot-de-passe-oublie', [AdminAuthController::class, 'sendResetLink'])->name('password.email');

        Route::get('/reinitialiser/{token}', [AdminAuthController::class, 'showResetPassword'])->name('password.reset');

        Route::post('/reinitialiser', [AdminAuthController::class, 'resetPassword'])->name('password.update');
    });

    // ── Logout (nécessite d'être connecté) ───────────────────────────
    Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth')->name('logout');

    // ── Routes protégées admin ────────────────────────────────────────
    Route::middleware(['auth', 'is.admin'])->group(function () {



        Route::prefix('banniere')->name('banniere.')->controller(BanniereController::class)->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::put('/', 'update')->name('update');
            Route::delete('/logo', 'supprimerLogo')->name('logo.supprimer');
        });

        // Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Commandes
        Route::resource('b5kz-y7r', AdminOrderController::class)->names('orders')->parameters(['b5kz-y7r' => 'order']);

        // Produits
        Route::resource('d1wz-u4n', AdminProductController::class)->names('products')->parameters(['d1wz-u4n' => 'product']);



        // Sous-produits du package
        Route::post('products/{product}/items', [AdminProductController::class, 'storeItem'])->name('products.items.store');
        Route::delete('products/{product}/items/{item}', [AdminProductController::class, 'destroyItem'])->name('products.items.destroy');

        // Avis curatés
        Route::post('products/{product}/reviews', [ProductReviewController::class, 'store'])->name('products.reviews.store');
        Route::delete('products/{product}/reviews/{review}', [ProductReviewController::class, 'destroy'])->name('products.reviews.destroy');

        // FAQs
        Route::post('products/{product}/faqs', [ProductFaqController::class, 'store'])->name('products.faqs.store');
        Route::delete('products/{product}/faqs/{faq}', [ProductFaqController::class, 'destroy'])->name('products.faqs.destroy');







        // Catégories
        Route::resource('j8cz-v2s', AdminCategoryController::class)->names('categories')->parameters(['j8cz-v2s' => 'category']);

        // Utilisateurs

        // Liste des utilisateurs
        Route::get('u7kz-p3x', [UserController::class, 'index'])->name('users.index');

        // Détail d’un utilisateur
        Route::get('u7kz-p3x/{user}', [UserController::class, 'show'])->name('users.show');


        Route::post('u7kz-p3x/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        Route::post('u7kz-p3x/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
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

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login');
})->middleware('auth')->name('logout');
