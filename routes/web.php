<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\AdminUserController;

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


Route::get('/', [HomeController::class, 'index'])->name('/');
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::name('admin.')->prefix('admin')->group(function () {
    Route::get('/', [AdminAuthController::class, 'index']);

    Route::get('login', [AdminAuthController::class, 'login'])->name('login');

    Route::post('login', [AdminAuthController::class, 'postLogin'])->name('login.post');

    Route::get('forget-password', [AdminAuthController::class, 'showForgetPasswordForm'])->name('forget.password.get');

    Route::post('forget-password', [AdminAuthController::class, 'submitForgetPasswordForm'])->name('forget.password.post');

    Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetPasswordForm'])->name('reset.password.get');

    Route::post('reset-password', [AdminAuthController::class, 'submitResetPasswordForm'])->name('reset.password.post');

    Route::middleware(['admin'])->group(function () {
    	Route::get('dashboard', [AdminAuthController::class, 'adminDashboard'])->name('dashboard');

        Route::get('change-password', [AdminAuthController::class, 'changePassword'])->name('change.password');

        Route::post('update-password', [AdminAuthController::class, 'updatePassword'])->name('update.password');

        Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('profile', [AdminAuthController::class, 'adminProfile'])->name('profile');

        Route::post('profile', [AdminAuthController::class, 'updateAdminProfile'])->name('update.profile');

        Route::name('users.')->group(function () {
            
            Route::get("users", [AdminUserController::class, 'index'])->name('index');
            
            Route::get("users/alluser", [AdminUserController::class, 'getallUser'])->name('alluser');

            Route::post("users/status", [AdminUserController::class, 'userStatus'])->name('status');

            Route::delete("users/delete/{id}", [AdminUserController::class, 'destroy'])->name('destroy');

            Route::get("users/{id}", [AdminUserController::class, 'show'])->name('show');
            
        });
        
        Route::name('contacts.')->group(function () {
            
            Route::get("contacts", [ContactController::class, 'index'])->name('index');

            Route::get("contacts/all", [ContactController::class, 'getallcontact'])->name('allcontact');

            Route::delete("contacts/delete/{id}", [ContactController::class, 'destroy'])->name('destroy');
        });

        Route::name('page.')->group(function () {
            
            Route::get("page/create/{key}", [PageController::class, 'create'])->name('create');
            
            Route::put("page/update/{key}", [PageController::class, 'update'])->name('update');
        });

        Route::name('notifications.')->group(function () {
            
            Route::get("notifications/index", [NotificationController::class, 'index'])->name('index');
            
            Route::get("notifications/clear", [NotificationController::class, 'clear'])->name('clear');
            
            Route::delete("notifications/delete/{id}", [NotificationController::class, 'destroy'])->name('destroy');
        });
    });

});

Route::middleware(['auth'])->group(function () {

});



