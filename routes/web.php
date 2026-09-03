<?php

use App\Enums\LabPermission;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InventoryCatalogItemController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientTestController;
use App\Http\Controllers\PatientTestPaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TestImportController;
use App\Http\Controllers\TestParameterController;
use App\Http\Controllers\TestReportContentController;
use App\Http\Controllers\TestResultController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\VisitDeliveryController;
use App\Http\Controllers\VisitPaymentController;
use App\Http\Controllers\VisitResultController;
use App\Http\Controllers\VisitShareController;
use App\Http\Controllers\VisitTokenController;
use App\Http\Controllers\WorklistController;
use Illuminate\Support\Facades\Route;

Route::get('locale/{locale}', LocaleController::class)->name('locale.switch');
Route::get('theme/{theme}', ThemeController::class)->name('theme.switch');

Route::permanentRedirect('/welcome', '/');
Route::permanentRedirect('/wellcome', '/');
Route::permanentRedirect('/doctor', '/doctors');
Route::permanentRedirect('/doctore', '/doctors');
Route::permanentRedirect('/Doctor', '/doctors');
Route::permanentRedirect('/test', '/tests');
Route::permanentRedirect('/patient', '/patients');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:register')->name('register.store');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open');

    Route::get('/', DashboardController::class)->middleware('can:'.LabPermission::Dashboard->value)->name('dashboard');

    Route::middleware('can:'.LabPermission::Reception->value)->group(function () {
        Route::get('reception', [ReceptionController::class, 'index'])->name('reception.index');
        Route::get('reception/patients/{patient}/visit', [ReceptionController::class, 'visit'])->name('reception.visit');
        Route::post('reception/patients/{patient}/visits', [ReceptionController::class, 'storeVisit'])->name('reception.visits.store');
        Route::post('reception/patients/{patient}/tests', [ReceptionController::class, 'attachTest'])->name('reception.tests.store');
        Route::put('reception/patients/{patient}/tests', [ReceptionController::class, 'syncTests'])->name('reception.tests.sync');
        Route::delete('reception/patients/{patient}/tests/{test}', [ReceptionController::class, 'detachTest'])->name('reception.tests.destroy');
        Route::patch('visits/{visit}/billing', [ReceptionController::class, 'updateBilling'])->name('visits.billing.update');
        Route::patch('visits/{visit}/payment', [VisitPaymentController::class, 'update'])->name('visits.payment');
        Route::post('visits/{visit}/print', [ReceptionController::class, 'printVisit'])->name('visits.print');
        Route::get('visits/{visit}/token', [VisitTokenController::class, 'show'])->name('visits.token');
    });

    Route::middleware('can:'.LabPermission::Patients->value)->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('patients/{patient}/token', [TokenController::class, 'show'])->name('tokens.show');
    });

    Route::middleware('can:'.LabPermission::Doctors->value)->group(function () {
        Route::resource('doctors', DoctorController::class);
    });

    Route::middleware('can:'.LabPermission::Tests->value)->group(function () {
        Route::resource('tests', TestController::class);
        Route::get('tests-import/template', [TestImportController::class, 'template'])->name('tests.import.template');
        Route::post('tests-import', [TestImportController::class, 'store'])->name('tests.import');
        Route::post('tests-templates', [TestImportController::class, 'storeTemplates'])->name('tests.templates.store');
        Route::post('tests/{test}/template', [TestImportController::class, 'storeTemplates'])->name('tests.template.store');
        Route::get('tests/{test}/template', [TestImportController::class, 'downloadTemplate'])->name('tests.template.download');
        Route::post('tests/{test}/parameters', [TestParameterController::class, 'store'])->name('tests.parameters.store');
        Route::delete('tests/{test}/parameters/{parameter}', [TestParameterController::class, 'destroy'])->name('tests.parameters.destroy');
        Route::get('settings/test-reports/{test?}', [TestReportContentController::class, 'edit'])->name('settings.test-reports.edit');
        Route::put('settings/test-reports/{test}', [TestReportContentController::class, 'update'])->name('settings.test-reports.update');
    });

    Route::middleware('can:'.LabPermission::Lab->value)->group(function () {
        Route::resource('patient-tests', PatientTestController::class);
        Route::patch('patient-tests/{patient_test}/payment', [PatientTestPaymentController::class, 'update'])
            ->name('patient-tests.payment');
        Route::resource('test-results', TestResultController::class);
        Route::get('worklist', WorklistController::class)->name('worklist');
        Route::get('visits/{visit}/results', [VisitResultController::class, 'edit'])->name('visits.results.edit');
        Route::post('visits/{visit}/results', [VisitResultController::class, 'store'])->name('visits.results.store');
        Route::get('visits/{visit}/report', [VisitResultController::class, 'report'])->name('visits.report');
        Route::post('visits/{visit}/email', [VisitShareController::class, 'email'])->name('visits.email');
        Route::post('visits/{visit}/deliver', [VisitDeliveryController::class, 'store'])->name('visits.deliver');
        Route::get('visits/{visit}/handover', [VisitDeliveryController::class, 'show'])->name('visits.handover');
    });

    Route::get('visits/{visit}/whatsapp', [VisitShareController::class, 'whatsapp'])
        ->middleware('can:'.LabPermission::Lab->value)
        ->name('visits.whatsapp');

    Route::middleware('can:'.LabPermission::Reports->value)->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('patients/{patient}/report', [ReportController::class, 'show'])->name('reports.show');
    });

    Route::middleware('can:'.LabPermission::Finance->value)->group(function () {
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('finance/export', [FinanceController::class, 'export'])->name('finance.export');
        Route::post('cash-transactions', [CashTransactionController::class, 'store'])->name('cash-transactions.store');
    });

    Route::middleware('can:'.LabPermission::Settings->value)->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('settings/goods', [InventoryCatalogItemController::class, 'index'])->name('settings.goods.index');
        Route::post('settings/goods', [InventoryCatalogItemController::class, 'store'])->name('settings.goods.store');
        Route::delete('settings/goods/{inventoryCatalogItem}', [InventoryCatalogItemController::class, 'destroy'])->name('settings.goods.destroy');
    });

    Route::middleware('can:'.LabPermission::Staff->value)->group(function () {
        Route::get('settings/staff', [StaffController::class, 'index'])->name('settings.staff.index');
        Route::post('settings/staff', [StaffController::class, 'store'])->name('settings.staff.store');
        Route::put('settings/staff/{staff}', [StaffController::class, 'update'])->name('settings.staff.update');
        Route::delete('settings/staff/{staff}', [StaffController::class, 'destroy'])->name('settings.staff.destroy');
        Route::post('settings/roles', [RoleController::class, 'store'])->name('settings.roles.store');
        Route::put('settings/roles/{role}', [RoleController::class, 'update'])->name('settings.roles.update');
        Route::delete('settings/roles/{role}', [RoleController::class, 'destroy'])->name('settings.roles.destroy');
    });

    Route::middleware('can:'.LabPermission::Accounts->value)->group(function () {
        Route::get('settings/accounts', [AccountController::class, 'index'])->name('settings.accounts.index');
        Route::put('accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
        Route::delete('accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
        Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
    });

    Route::middleware('can:'.LabPermission::Suppliers->value)->group(function () {
        Route::resource('suppliers', SupplierController::class);
    });

    Route::middleware('can:'.LabPermission::Purchases->value)->group(function () {
        Route::resource('purchases', PurchaseController::class);
    });

    Route::middleware('can:'.LabPermission::Inventory->value)->group(function () {
        Route::resource('inventory-items', InventoryItemController::class);
    });

    Route::middleware('can:'.LabPermission::Expenses->value)->group(function () {
        Route::resource('expenses', ExpenseController::class);
    });
});
