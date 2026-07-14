<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialInputController;
use App\Http\Controllers\PhysicalInputController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\GassController;
use App\Http\Controllers\GassExcelUploadController;
use App\Http\Controllers\StoController;
use App\Http\Controllers\StoExcelUploadController;
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

$adminRoles = 'role:admin';
$viewRoles = 'role:super-admin,admin,user,penro,cenro,ro-office,ro office';
$userRole = 'role:user,penro,cenro';

// admin pages
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', $viewRoles])->name('dashboard');


Route::prefix('programs')->name('programs.')->middleware(['auth', $adminRoles])->group(function () {
    Route::post('/',            [ProgramController::class, 'store']) ->name('store');
    Route::delete('/{program}', [ProgramController::class, 'destroy'])->name('destroy');
});

//sidebar

Route::get('/gass_physical/{program?}', [GassController::class, 'index'])->middleware(['auth', $viewRoles])->name('gass_physical');
Route::get('/sto', [StoController::class, 'index'])->middleware(['auth', $viewRoles])->name('sto');
Route::get('/sto_physical/{program?}', [StoController::class, 'index'])->middleware(['auth', $viewRoles])->name('sto_physical');
Route::get('/enf', [EnfController::class, 'index'])->middleware(['auth', $viewRoles])->name('enf');
Route::get('/enf_physical/{program?}', [EnfController::class, 'index'])->middleware(['auth', $viewRoles])->name('enf_physical');
Route::get('/pa', [PaController::class, 'index'])->middleware(['auth', $viewRoles])->name('pa');
Route::get('/pa_physical/{program?}', [PaController::class, 'index'])->middleware(['auth', $viewRoles])->name('pa_physical');
Route::get('/engp', [EngpController::class, 'index'])->middleware(['auth', $viewRoles])->name('engp');
Route::get('/engp_physical/{program?}', [EngpController::class, 'index'])->middleware(['auth', $viewRoles])->name('engp_physical');
Route::get('/lands', [LandsController::class, 'index'])->middleware(['auth', $viewRoles])->name('lands');
Route::get('/lands_physical/{program?}', [LandsController::class, 'index'])->middleware(['auth', $viewRoles])->name('lands_physical');
Route::get('/soilcon', [SoilconController::class, 'index'])->middleware(['auth', $viewRoles])->name('soilcon');
Route::get('/soilcon_physical/{program?}', [SoilconController::class, 'index'])->middleware(['auth', $viewRoles])->name('soilcon_physical');
Route::get('/nra', [NraController::class, 'index'])->middleware(['auth', $viewRoles])->name('nra');
Route::get('/nra_physical/{program?}', [NraController::class, 'index'])->middleware(['auth', $viewRoles])->name('nra_physical');
Route::get('/paria', [PariaController::class, 'index'])->middleware(['auth', $viewRoles])->name('paria');
Route::get('/paria_physical/{program?}', [PariaController::class, 'index'])->middleware(['auth', $viewRoles])->name('paria_physical');
Route::get('/cobb', [CobbController::class, 'index'])->middleware(['auth', $viewRoles])->name('cobb');
Route::get('/cobb_physical/{program?}', [CobbController::class, 'index'])->middleware(['auth', $viewRoles])->name('cobb_physical');
Route::get('/continuing', [ContinuingController::class, 'index'])->middleware(['auth', $viewRoles])->name('continuing');
Route::get('/continuing_physical/{program?}', [ContinuingController::class, 'index'])->middleware(['auth', $viewRoles])->name('continuing_physical');
Route::get('/user', [UserController::class, 'index'])->middleware(['auth', $adminRoles])->name('user');
Route::get('/history', [HistoryController::class, 'index'])->middleware(['auth', $adminRoles])->name('history');
Route::post('/financial-inputs/{sector}/store', [FinancialInputController::class, 'store'])
    ->middleware(['auth', $viewRoles, 'field.history'])
    ->name('financial_inputs.store');
Route::post('/users', [UserController::class, 'store'])->middleware(['auth', $adminRoles, 'field.history'])->name('users.store');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware(['auth', $adminRoles])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->middleware(['auth', $adminRoles, 'field.history'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware(['auth', $adminRoles, 'field.history'])->name('users.destroy');

Route::get('/gass', [GassController::class, 'overview'])->middleware(['auth', $userRole])->name('gass');

$userPhysicalSaveControllers = [
    'gass' => GassController::class,
    'sto' => StoController::class,
    'enf' => EnfController::class,
    'pa' => PaController::class,
    'engp' => EngpController::class,
    'lands' => LandsController::class,
    'soilcon' => SoilconController::class,
    'nra' => NraController::class,
    'paria' => PariaController::class,
    'cobb' => CobbController::class,
    'continuing' => ContinuingController::class,
];

foreach ($userPhysicalSaveControllers as $physicalKey => $controllerClass) {
    Route::prefix($physicalKey . '_physical')
        ->name('users.' . $physicalKey . '_physical.')
        ->middleware(['auth', $userRole, 'field.history'])
        ->group(function () use ($physicalKey) {
            Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])
                ->defaults('sector', $physicalKey)
                ->name('targets.store');
            Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])
                ->defaults('sector', $physicalKey)
                ->name('accomplishments.store');
        });
}

Route::prefix('admin/gass_physical')->name('admin.gass_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [GassController::class, 'index'])->name('physical');
    Route::post('/pap', [GassController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [GassController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'gass')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'gass')->name('accomplishments.store');
    Route::delete('/rows', [GassController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/import-excel/preview', [GassExcelUploadController::class, 'previewExcelImport'])->name('import_excel.preview');
    Route::post('/import-excel', [GassExcelUploadController::class, 'importExcel'])->name('import_excel');
    Route::post('/indicators', [GassController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [GassController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [GassController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/sto_physical')->name('admin.sto_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [StoController::class, 'index'])->name('physical');
    Route::post('/pap', [StoController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [StoController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'sto')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'sto')->name('accomplishments.store');
    Route::delete('/rows', [StoController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/import-excel/preview', [StoExcelUploadController::class, 'previewExcelImport'])->name('import_excel.preview');
    Route::post('/import-excel', [StoExcelUploadController::class, 'importExcel'])->name('import_excel');
    Route::post('/indicators', [StoController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [StoController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [StoController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/enf_physical')->name('admin.enf_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [EnfController::class, 'index'])->name('physical');
    Route::post('/pap', [EnfController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [EnfController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'enf')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'enf')->name('accomplishments.store');
    Route::delete('/rows', [EnfController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/indicators', [EnfController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [EnfController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [EnfController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/pa_physical')->name('admin.pa_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [PaController::class, 'index'])->name('physical');
    Route::post('/pap', [PaController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [PaController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'pa')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'pa')->name('accomplishments.store');
    Route::delete('/rows', [PaController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/indicators', [PaController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [PaController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [PaController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/engp_physical')->name('admin.engp_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [EngpController::class, 'index'])->name('physical');
    Route::post('/pap', [EngpController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [EngpController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'engp')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'engp')->name('accomplishments.store');
    Route::delete('/rows', [EngpController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/indicators', [EngpController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [EngpController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [EngpController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/lands_physical')->name('admin.lands_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [LandsController::class, 'index'])->name('physical');
    Route::post('/pap', [LandsController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [LandsController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'lands')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'lands')->name('accomplishments.store');
    Route::delete('/rows', [LandsController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/indicators', [LandsController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [LandsController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [LandsController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/soilcon_physical')->name('admin.soilcon_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [SoilconController::class, 'index'])->name('physical');
    Route::post('/pap', [SoilconController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [SoilconController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'soilcon')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'soilcon')->name('accomplishments.store');
    Route::delete('/rows', [SoilconController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/indicators', [SoilconController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [SoilconController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [SoilconController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/nra_physical')->name('admin.nra_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [NraController::class, 'index'])->name('physical');
    Route::post('/pap', [NraController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [NraController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'nra')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'nra')->name('accomplishments.store');
    Route::delete('/rows', [NraController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/indicators', [NraController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [NraController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [NraController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/paria_physical')->name('admin.paria_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [PariaController::class, 'index'])->name('physical');
    Route::post('/pap', [PariaController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [PariaController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'paria')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'paria')->name('accomplishments.store');
    Route::delete('/rows', [PariaController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/indicators', [PariaController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [PariaController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [PariaController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/cobb_physical')->name('admin.cobb_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [CobbController::class, 'index'])->name('physical');
    Route::post('/pap', [CobbController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [CobbController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'cobb')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'cobb')->name('accomplishments.store');
    Route::delete('/rows', [CobbController::class, 'destroyPhysicalRow'])->name('rows.destroy');
    Route::post('/indicators', [CobbController::class, 'storeIndicator'])->name('indicators.store');
    Route::patch('/indicators/{indicator}', [CobbController::class, 'update'])->name('indicators.update');
    Route::delete('/indicators/{indicator}', [CobbController::class, 'destroyIndicator'])->name('indicators.destroy');
});

Route::prefix('admin/continuing_physical')->name('admin.continuing_physical.')->middleware(['auth', $adminRoles, 'field.history'])->group(function () {
    Route::get('/physical/{program?}', [ContinuingController::class, 'index'])->name('physical');
    Route::post('/pap', [ContinuingController::class, 'storePap'])->name('pap.store');
    Route::delete('/pap/{program}', [ContinuingController::class, 'destroyPap'])->name('pap.destroy');
    Route::post('/targets/store', [PhysicalInputController::class, 'storeTargets'])->defaults('sector', 'continuing')->name('targets.store');
    Route::post('/accomplishments/store', [PhysicalInputController::class, 'storeAccomplishments'])->defaults('sector', 'continuing')->name('accomplishments.store');
    Route::delete('/rows', [ContinuingController::class, 'destroyPhysicalRow'])->name('rows.destroy');
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
