<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\GassController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// admin pages
Route::get('/dashboard', function () {return view('admin.index');})->middleware('auth')->name('dashboard');


Route::prefix('programs')->name('programs.')->group(function () {
    Route::get('/',             [ProgramController::class, 'index'])  ->name('index');
    Route::get('/create',       [ProgramController::class, 'create'])->name('create');
    Route::post('/',            [ProgramController::class, 'store']) ->name('store');
    Route::get('/{program}/edit',   [ProgramController::class, 'edit'])->name('edit');
    Route::put('/{program}',    [ProgramController::class, 'update'])->name('update');
    // Route::delete('/{program}', [ProgramController::class, 'destroy'])->name('destroy'); // later
});

//sidebar
Route::get('/target', function () {return view('admin.target');})->middleware('auth')->name('target');
Route::get('/gass', function () { return view('admin.gass.gass'); })->middleware('auth')->name('gass');
Route::get('/sto', function () { return view('admin.sto'); })->middleware('auth')->name('sto');
Route::get('/enf', function () { return view('admin.enf'); })->middleware('auth')->name('enf');
Route::get('/pa', function () { return view('admin.pa'); })->middleware('auth')->name('pa');
Route::get('/engp', function () { return view('admin.engp'); })->middleware('auth')->name('engp');
Route::get('/lands', function () { return view('admin.lands'); })->middleware('auth')->name('lands');
Route::get('/soilcon', function () { return view('admin.soilcon'); })->middleware('auth')->name('soilcon');
Route::get('/nra', function () { return view('admin.nra'); })->middleware('auth')->name('nra');
Route::get('/paria', function () { return view('admin.paria'); })->middleware('auth')->name('paria');
Route::get('/cobb', function () { return view('admin.cobb'); })->middleware('auth')->name('cobb');
Route::get('/continuing', function () { return view('admin.continuing'); })->middleware('auth')->name('continuing');
Route::get('/programs_view', function () {
    $programs = \App\Models\Program::latest()->get();
    return view('admin.programs', compact('programs'));
})->middleware('auth')->name('programs');

Route::get('/user',     [UserController::class, 'index']) ->name('user');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

Route::get('/target_form', [OfficeController::class, 'create'])
    ->middleware('auth')
    ->name('target_form');

Route::get('/gass', [GassController::class, 'overview'])->name('gass');
Route::prefix('admin/gass')->name('admin.gass.')->middleware('auth')->group(function () {

    Route::get('/physical/{program?}', [GassController::class, 'index'])
        ->name('physical');

        Route::post('/physical/save', [GassController::class, 'save'])
    ->name('physical.save');


Route::get('/indicators', [GassController::class, 'indicatorsIndex'])
        ->name('indicators');

    Route::get('/indicators/create', [GassController::class, 'createIndicator'])
        ->name('indicators.create');

    Route::post('/indicators', [GassController::class, 'storeIndicator'])
        ->name('indicators.store');

    Route::patch('/indicators/{indicator}', [GassController::class, 'update'])
        ->name('indicators.update');
});





Route::post('/targets', [OfficeController::class, 'store'])->name('targets.store');



Route::get('/offices/{penro}/cenros', [OfficeController::class, 'getCenros'])->name('offices.cenros');





Route::post('/logout', function (Request $request) {
    auth()->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');
