<?php

use App\Livewire\ApprovalSpvUser;
use App\Livewire\WorkOrderForm;
use App\Livewire\Dashboard;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Livewire\Auth\ForgotPassword;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/work-order/spv-approval', ApprovalSpvUser::class)->name('work-order.spv-approval');
    Route::get('/work-order/create', WorkOrderForm::class)->name('work-order.create');
    Route::get('/register', Register::class)->name('register');
});
// Route::get('/login',[AuthController::class,'showLogin'])->name('login');

Auth::routes($options = [
    'register' => false, // Disable default registration route
    'login' => false, // Disable default password reset routes
]);

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
});
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
