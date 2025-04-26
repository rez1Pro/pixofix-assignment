<?php

use App\Http\Controllers\FileClaimController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/order-management', fn() => Inertia::render('OrderManagement'))->name('order-management');

    // Orders Routes
    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/mark-completed', [OrderController::class, 'markAsCompleted'])->name('orders.mark-completed');
    Route::post('orders/{order}/approve', [OrderController::class, 'approve'])->name('orders.approve');

    // Files Routes
    Route::get('orders/{order}/files', [FileController::class, 'index'])->name('files.index');
    Route::get('orders/{order}/files/create', [FileController::class, 'create'])->name('files.create');
    Route::post('orders/{order}/files', [FileController::class, 'store'])->name('files.store');
    Route::get('files/{file}', [FileController::class, 'show'])->name('files.show');
    Route::patch('files/{file}', [FileController::class, 'update'])->name('files.update');
    Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
    Route::post('files/{file}/upload-processed', [FileController::class, 'uploadProcessed'])->name('files.upload-processed');

    // File Claims Routes
    Route::get('claims', [FileClaimController::class, 'index'])->name('claims.index');
    Route::post('orders/{order}/claim-files', [FileClaimController::class, 'claimFiles'])->name('claims.claim-files');
    Route::get('claims/{claim}', [FileClaimController::class, 'show'])->name('claims.show');
    Route::post('claims/{claim}/mark-completed', [FileClaimController::class, 'markCompleted'])->name('claims.mark-completed');
    Route::post('claims/{claim}/return-files', [FileClaimController::class, 'returnFiles'])->name('claims.return-files');
    Route::get('orders/{order}/claims', [FileClaimController::class, 'orderClaims'])->name('orders.claims');

    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::resource('/', UserController::class)->parameter('', 'user')->whereNumber('user');
        Route::resource('roles', RoleController::class)
            ->parameter('', 'role')
            ->whereNumber('role')
            ->except(['show']);
    });
});

require __DIR__ . '/auth.php';
