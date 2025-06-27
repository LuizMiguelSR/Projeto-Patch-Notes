<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatchNoteController;
use App\Http\Controllers\PatchNoteImportController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('patch-notes.index');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/patch-notes/calendar', [PatchNoteController::class, 'calendar'])->name('patch-notes.calendar');
Route::get('/patch-notes', [PatchNoteController::class, 'index'])->name('patch-notes.index');
Route::get('/patch-notes/{id}', [PatchNoteController::class, 'show'])->name('patch-notes.show');

Route::middleware('auth')->group(function () {
    Route::get('/patch-notes/{id}/edit', [PatchNoteController::class, 'edit'])->name('patch-notes.edit');
    Route::put('/patch-notes/{id}', [PatchNoteController::class, 'update'])->name('patch-notes.update');
    Route::post('/patch-notes/import', [PatchNoteImportController::class, 'import'])->name('patch-notes.import');
});

Route::get('/cron/import-patches', [PatchNoteImportController::class, 'runFromCron']);
