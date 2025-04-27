<?php

use App\Http\Controllers\FileClaimController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubfolderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderFilesController;
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

    Route::get('/dashboard', [OrderController::class, 'dashboard'])->name('dashboard');
    Route::get('/order-management', [OrderController::class, 'index'])->name('order-management');

    // Orders Routes
    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/mark-completed', [OrderController::class, 'markAsCompleted'])->name('orders.mark-completed');
    Route::post('orders/{order}/approve', [OrderController::class, 'approve'])->name('orders.approve');
    Route::get('orders/{order}/refresh-stats', [OrderController::class, 'refreshStats'])->name('orders.refresh-stats');

    // Folder management routes
    Route::post('orders/{order}/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::put('folders/{folder}/toggle-open', [FolderController::class, 'toggleOpen'])->name('folders.toggle-open');
    Route::put('folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');
    Route::get('folders/{folder}', [FolderController::class, 'show'])->name('folders.show');
    Route::get('folders/{folder}/data', [FolderController::class, 'getFolderData'])->name('folders.data');

    // Subfolder management routes
    Route::post('folders/{folder}/subfolders', [SubfolderController::class, 'store'])->name('subfolders.store');
    Route::put('subfolders/{subfolder}/toggle-open', [SubfolderController::class, 'toggleOpen'])->name('subfolders.toggle-open');
    Route::put('subfolders/{subfolder}', [SubfolderController::class, 'update'])->name('subfolders.update');
    Route::delete('subfolders/{subfolder}', [SubfolderController::class, 'destroy'])->name('subfolders.destroy');
    Route::get('subfolders/{subfolder}', [SubfolderController::class, 'show'])->name('subfolders.show');
    Route::get('subfolders/{subfolder}/data', [SubfolderController::class, 'getSubfolderData'])->name('subfolders.data');

    // File management routes
    Route::post('folders/{folder}/files', [FileController::class, 'uploadToFolder'])->name('files.upload-to-folder');
    Route::post('subfolders/{subfolder}/files', [FileController::class, 'uploadToSubfolder'])->name('files.upload-to-subfolder');
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::post('files/{file}/assign', [FileController::class, 'assignToSelf'])->name('files.assign');
    Route::post('files/assign-multiple', [FileController::class, 'assignMultipleToSelf'])->name('files.assign-multiple');
    Route::put('files/{file}/status', [FileController::class, 'updateStatus'])->name('files.update-status');
    Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::get('files/{file}/info', [FileController::class, 'getFileInfo'])->name('files.info');

    // Files Routes
    Route::get('orders/{order}/files', [FileController::class, 'index'])->name('files.index');
    Route::get('orders/{order}/files/create', [FileController::class, 'create'])->name('files.create');
    Route::post('orders/{order}/files', [FileController::class, 'store'])->name('files.store');
    Route::get('files/{file}', [FileController::class, 'show'])->name('files.show');
    Route::patch('files/{file}', [FileController::class, 'update'])->name('files.update');
    Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::post('orders/{order}/files/download-selected', [FileController::class, 'downloadSelected'])->name('files.download-selected');
    Route::get('files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
    Route::post('files/{file}/upload-processed', [FileController::class, 'uploadProcessed'])->name('files.upload-processed');
    Route::post('files/{file}/mark-completed', [FileController::class, 'markAsCompleted'])->name('files.mark-completed');
    Route::get('orders/{order}/directory-structure', [FileController::class, 'getDirectoryStructure'])->name('files.directory-structure');

    // Batch claim routes
    Route::post('orders/{order}/claim-batch', [FileController::class, 'claimBatch'])->name('files.claim-batch');

    // File Claims Routes
    Route::get('claims', [FileClaimController::class, 'index'])->name('claims.index');
    Route::post('orders/{order}/claim-files', [FileClaimController::class, 'claimFiles'])->name('claims.claim-files');
    Route::get('claims/{claim}', [FileClaimController::class, 'show'])->name('claims.show');
    Route::post('claims/{claim}/mark-completed', [FileClaimController::class, 'markCompleted'])->name('claims.mark-completed');
    Route::post('claims/{claim}/return-files', [FileClaimController::class, 'returnFiles'])->name('claims.return-files');
    Route::get('orders/{order}/claims', [FileClaimController::class, 'orderClaims'])->name('orders.claims');

    // Batch operations for file claims
    Route::resource('fileclaims', FileClaimController::class)->only(['show', 'update', 'destroy']);
    Route::post('fileclaims/{fileclaim}/complete', [FileClaimController::class, 'complete'])->name('fileclaims.complete');
    Route::post('fileclaims/{fileclaim}/release', [FileClaimController::class, 'release'])->name('fileclaims.release');

    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::resource('/', UserController::class)->parameter('', 'user')->whereNumber('user');
        Route::resource('roles', RoleController::class)
            ->parameter('', 'role')
            ->whereNumber('role')
            ->except(['show']);
    });

    // Order files management routes
    Route::post('orders/{order}/files/update-status', [OrderFilesController::class, 'updateStatus'])->name('orders.files.update-status');
    Route::post('orders/{order}/files/assign', [OrderFilesController::class, 'assignToUser'])->name('orders.files.assign');
    Route::post('orders/{order}/files/assign-to-self', [OrderFilesController::class, 'assignToSelf'])->name('orders.files.assign-to-self');
    Route::post('orders/{order}/files/download', [OrderFilesController::class, 'downloadFiles'])->name('orders.files.download');
});

require __DIR__ . '/auth.php';
