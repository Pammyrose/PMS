<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\GassController;
use App\Http\Controllers\StoController;
use App\Http\Controllers\EnfController;
use App\Http\Controllers\PaController;
use App\Http\Controllers\EngpController;
use App\Http\Controllers\LandsController;
use App\Http\Controllers\SoilconController;
use App\Http\Controllers\NraController;
use App\Http\Controllers\PariaController;
use App\Http\Controllers\CobbController;
use App\Http\Controllers\ContinuingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// admin pages
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');


Route::prefix('programs')->name('programs.')->group(function () {
    Route::post('/',            [ProgramController::class, 'store']) ->name('store');
    Route::delete('/{program}', [ProgramController::class, 'destroy'])->name('destroy');
});

//sidebar

Route::get('/gass_physical', [GassController::class, 'index'])->middleware('auth')->name('gass_physical');
Route::get('/sto', [StoController::class, 'index'])->middleware('auth')->name('sto');
Route::get('/enf', [EnfController::class, 'index'])->middleware('auth')->name('enf');
Route::get('/pa', [PaController::class, 'index'])->middleware('auth')->name('pa');
Route::get('/engp', [EngpController::class, 'index'])->middleware('auth')->name('engp');
Route::get('/lands', [LandsController::class, 'index'])->middleware('auth')->name('lands');
Route::get('/soilcon', [SoilconController::class, 'index'])->middleware('auth')->name('soilcon');
Route::get('/nra', [NraController::class, 'index'])->middleware('auth')->name('nra');
Route::get('/paria', [PariaController::class, 'index'])->middleware('auth')->name('paria');
Route::get('/cobb', [CobbController::class, 'index'])->middleware('auth')->name('cobb');
Route::get('/continuing', [ContinuingController::class, 'index'])->middleware('auth')->name('continuing');
Route::get('/user',     [UserController::class, 'index']) ->name('user');
Route::get('/history', [HistoryController::class, 'index'])->middleware('auth')->name('history');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

Route::get('/gass', [GassController::class, 'overview'])->name('gass');
Route::prefix('admin/gass_physical')->name('admin.gass_physical.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [GassController::class, 'index'])->name('physical');
    Route::post('/pap', [GassController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [GassController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [GassController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [GassController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [GassController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [GassController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [GassController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/sto')->name('admin.sto.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [StoController::class, 'index'])->name('physical');
    Route::post('/pap', [StoController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [StoController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [StoController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [StoController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [StoController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [StoController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [StoController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/enf')->name('admin.enf.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [EnfController::class, 'index'])->name('physical');
    Route::post('/pap', [EnfController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [EnfController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [EnfController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [EnfController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [EnfController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [EnfController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [EnfController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/pa')->name('admin.pa.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [PaController::class, 'index'])->name('physical');
    Route::post('/pap', [PaController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [PaController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PaController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [PaController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [PaController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [PaController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [PaController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/engp')->name('admin.engp.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [EngpController::class, 'index'])->name('physical');
    Route::post('/pap', [EngpController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [EngpController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [EngpController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [EngpController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [EngpController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [EngpController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [EngpController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/lands')->name('admin.lands.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [LandsController::class, 'index'])->name('physical');
    Route::post('/pap', [LandsController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [LandsController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [LandsController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [LandsController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [LandsController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [LandsController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [LandsController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/soilcon')->name('admin.soilcon.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [SoilconController::class, 'index'])->name('physical');
    Route::post('/pap', [SoilconController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [SoilconController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [SoilconController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [SoilconController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [SoilconController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [SoilconController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [SoilconController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/nra')->name('admin.nra.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [NraController::class, 'index'])->name('physical');
    Route::post('/pap', [NraController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [NraController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [NraController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [NraController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [NraController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [NraController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [NraController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/paria')->name('admin.paria.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [PariaController::class, 'index'])->name('physical');
    Route::post('/pap', [PariaController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [PariaController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PariaController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [PariaController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [PariaController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [PariaController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [PariaController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/cobb')->name('admin.cobb.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [CobbController::class, 'index'])->name('physical');
    Route::post('/pap', [CobbController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [CobbController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [CobbController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [CobbController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [CobbController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [CobbController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [CobbController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/continuing')->name('admin.continuing.')->middleware(['auth', 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [ContinuingController::class, 'index'])->name('physical');
    Route::post('/pap', [ContinuingController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [ContinuingController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [ContinuingController::class, 'storeTargets'])->name('targets.store');
    Route::post('/accomplishments/store', [ContinuingController::class, 'storeAccomplishments'])->name('accomplishments.store');
    Route::post('/indicators', [ContinuingController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [ContinuingController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [ContinuingController::class, 'destroyIndicator'])->name('indicators.destroy');
});





Route::post('/logout', function (Request $request) {
    auth()->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');
