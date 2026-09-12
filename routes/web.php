<?php

use App\Enums\LabPermission;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FinancePeriodReportController;
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
use App\Http\Controllers\StockUsageController;
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
        Route::delete('reception/patients/{patient}/tests/{test}', [ReceptionController::class, 'detachTest'])
            ->middleware('can:'.LabPermission::Delete->value)
            ->name('reception.tests.destroy');
        Route::patch('visits/{visit}/billing', [ReceptionController::class, 'updateBilling'])->name('visits.billing.update');
        Route::patch('visits/{visit}/payment', [VisitPaymentController::class, 'update'])->name('visits.payment');
        Route::post('visits/{visit}/print', [ReceptionController::class, 'printVisit'])->name('visits.print');
        Route::get('visits/{visit}/token', [VisitTokenController::class, 'show'])->name('visits.token');
    });

    Route::middleware('can:'.LabPermission::Patients->value)->group(function () {
        Route::resource('patients', PatientController::class)->except(['edit', 'update', 'destroy']);
        Route::get('patients/{patient}/token', [TokenController::class, 'show'])->name('tokens.show');
    });
    Route::middleware(['can:'.LabPermission::Patients->value, 'can:'.LabPermission::Edit->value])->group(function () {
        Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
        Route::match(['put', 'patch'], 'patients/{patient}', [PatientController::class, 'update'])->name('patients.update');
    });
    Route::delete('patients/{patient}', [PatientController::class, 'destroy'])
        ->middleware(['can:'.LabPermission::Patients->value, 'can:'.LabPermission::Delete->value])
        ->name('patients.destroy');

    Route::middleware('can:'.LabPermission::Doctors->value)->group(function () {
        Route::resource('doctors', DoctorController::class)->except(['edit', 'update', 'destroy']);
    });
    Route::middleware(['can:'.LabPermission::Doctors->value, 'can:'.LabPermission::Edit->value])->group(function () {
        Route::get('doctors/{doctor}/edit', [DoctorController::class, 'edit'])->name('doctors.edit');
        Route::match(['put', 'patch'], 'doctors/{doctor}', [DoctorController::class, 'update'])->name('doctors.update');
    });
    Route::delete('doctors/{doctor}', [DoctorController::class, 'destroy'])
        ->middleware(['can:'.LabPermission::Doctors->value, 'can:'.LabPermission::Delete->value])
        ->name('doctors.destroy');

    Route::middleware('can:'.LabPermission::Tests->value)->group(function () {
        Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::resource('tests', TestController::class)->except(['edit', 'update', 'destroy']);
        Route::get('tests-import/template', [TestImportController::class, 'template'])->name('tests.import.template');
        Route::post('tests-import', [TestImportController::class, 'store'])->name('tests.import');
        Route::post('tests/{test}/parameters', [TestParameterController::class, 'store'])->name('tests.parameters.store');
    });
    Route::middleware(['can:'.LabPermission::Tests->value, 'can:'.LabPermission::Edit->value])->group(function () {
        Route::get('tests/{test}/edit', [TestController::class, 'edit'])->name('tests.edit');
        Route::match(['put', 'patch'], 'tests/{test}', [TestController::class, 'update'])->name('tests.update');
        Route::get('settings/test-reports/{test?}', [TestReportContentController::class, 'edit'])->name('settings.test-reports.edit');
        Route::put('settings/test-reports/{test}', [TestReportContentController::class, 'update'])->name('settings.test-reports.update');
    });
    Route::middleware(['can:'.LabPermission::Tests->value, 'can:'.LabPermission::Delete->value])->group(function () {
        Route::delete('tests/{test}', [TestController::class, 'destroy'])->name('tests.destroy');
        Route::delete('tests/{test}/parameters/{parameter}', [TestParameterController::class, 'destroy'])->name('tests.parameters.destroy');
    });

    Route::middleware('can:'.LabPermission::Lab->value)->group(function () {
        Route::resource('patient-tests', PatientTestController::class)->except(['edit', 'update', 'destroy']);
        Route::patch('patient-tests/{patient_test}/payment', [PatientTestPaymentController::class, 'update'])
            ->name('patient-tests.payment');
        Route::resource('test-results', TestResultController::class)->except(['edit', 'update', 'destroy']);
        Route::get('worklist', WorklistController::class)->name('worklist');
        Route::get('visits/{visit}/results', [VisitResultController::class, 'edit'])->name('visits.results.edit');
        Route::post('visits/{visit}/results', [VisitResultController::class, 'store'])->name('visits.results.store');
        Route::get('visits/{visit}/report', [VisitResultController::class, 'report'])->name('visits.report');
        Route::post('visits/{visit}/email', [VisitShareController::class, 'email'])->name('visits.email');
        Route::post('visits/{visit}/deliver', [VisitDeliveryController::class, 'store'])->name('visits.deliver');
        Route::get('visits/{visit}/handover', [VisitDeliveryController::class, 'show'])->name('visits.handover');
    });

    Route::middleware(['can:'.LabPermission::Lab->value, 'can:'.LabPermission::Edit->value])->group(function () {
        Route::get('patient-tests/{patient_test}/edit', [PatientTestController::class, 'edit'])->name('patient-tests.edit');
        Route::match(['put', 'patch'], 'patient-tests/{patient_test}', [PatientTestController::class, 'update'])->name('patient-tests.update');
        Route::get('test-results/{test_result}/edit', [TestResultController::class, 'edit'])->name('test-results.edit');
        Route::match(['put', 'patch'], 'test-results/{test_result}', [TestResultController::class, 'update'])->name('test-results.update');
    });
    Route::middleware(['can:'.LabPermission::Lab->value, 'can:'.LabPermission::Delete->value])->group(function () {
        Route::delete('patient-tests/{patient_test}', [PatientTestController::class, 'destroy'])->name('patient-tests.destroy');
        Route::delete('test-results/{test_result}', [TestResultController::class, 'destroy'])->name('test-results.destroy');
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
        Route::get('finance/period-report', [FinancePeriodReportController::class, 'show'])->name('finance.period-report');
        Route::post('cash-transactions', [CashTransactionController::class, 'store'])->name('cash-transactions.store');
    });

    Route::middleware('can:'.LabPermission::Settings->value)->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('settings/backups', [BackupController::class, 'index'])->name('settings.backups.index');
        Route::post('settings/backups', [BackupController::class, 'store'])->name('settings.backups.store');
        Route::get('settings/backups/{backup}/download', [BackupController::class, 'download'])
            ->where('backup', 'ssml-backup-[A-Za-z0-9._-]+\.(sql|sqlite)')
            ->name('settings.backups.download');
        Route::post('settings/backups/restore', [BackupController::class, 'restore'])->name('settings.backups.restore');
        Route::get('settings/goods', [InventoryCatalogItemController::class, 'index'])->name('settings.goods.index');
        Route::post('settings/goods', [InventoryCatalogItemController::class, 'store'])->name('settings.goods.store');
        Route::delete('settings/goods/{inventoryCatalogItem}', [InventoryCatalogItemController::class, 'destroy'])
            ->middleware('can:'.LabPermission::Delete->value)
            ->name('settings.goods.destroy');
    });

    Route::middleware('can:'.LabPermission::Staff->value)->group(function () {
        Route::get('settings/staff', [StaffController::class, 'index'])->name('settings.staff.index');
        Route::post('settings/staff', [StaffController::class, 'store'])->name('settings.staff.store');
        Route::put('settings/staff/{staff}', [StaffController::class, 'update'])
            ->middleware('can:'.LabPermission::Edit->value)
            ->name('settings.staff.update');
        Route::delete('settings/staff/{staff}', [StaffController::class, 'destroy'])
            ->middleware('can:'.LabPermission::Delete->value)
            ->name('settings.staff.destroy');
        Route::post('settings/roles', [RoleController::class, 'store'])->name('settings.roles.store');
        Route::put('settings/roles/{role}', [RoleController::class, 'update'])
            ->name('settings.roles.update');
        Route::delete('settings/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('can:'.LabPermission::Delete->value)
            ->name('settings.roles.destroy');
    });

    Route::middleware('can:'.LabPermission::Accounts->value)->group(function () {
        Route::get('settings/accounts', [AccountController::class, 'index'])->name('settings.accounts.index');
        Route::put('accounts/{account}', [AccountController::class, 'update'])
            ->middleware('can:'.LabPermission::Edit->value)
            ->name('accounts.update');
        Route::delete('accounts/{account}', [AccountController::class, 'destroy'])
            ->middleware('can:'.LabPermission::Delete->value)
            ->name('accounts.destroy');
        Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
    });

    Route::middleware('can:'.LabPermission::Suppliers->value)->group(function () {
        Route::resource('suppliers', SupplierController::class)->except(['edit', 'update', 'destroy']);
    });

    Route::middleware('can:'.LabPermission::Purchases->value)->group(function () {
        Route::resource('purchases', PurchaseController::class)->except(['edit', 'update', 'destroy']);
    });

    Route::middleware('can:'.LabPermission::Inventory->value)->group(function () {
        Route::resource('inventory-items', InventoryItemController::class)->except(['edit', 'update', 'destroy']);
        Route::get('stock-usages', [StockUsageController::class, 'create'])->name('stock-usages.create');
        Route::post('stock-usages', [StockUsageController::class, 'store'])->name('stock-usages.store');
    });

    Route::middleware('can:'.LabPermission::Expenses->value)->group(function () {
        Route::resource('expenses', ExpenseController::class)->except(['edit', 'update', 'destroy']);
    });

    Route::middleware(['can:'.LabPermission::Suppliers->value, 'can:'.LabPermission::Edit->value])->group(function () {
        Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::match(['put', 'patch'], 'suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    });
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])
        ->middleware(['can:'.LabPermission::Suppliers->value, 'can:'.LabPermission::Delete->value])
        ->name('suppliers.destroy');

    Route::middleware(['can:'.LabPermission::Purchases->value, 'can:'.LabPermission::Edit->value])->group(function () {
        Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::match(['put', 'patch'], 'purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    });
    Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])
        ->middleware(['can:'.LabPermission::Purchases->value, 'can:'.LabPermission::Delete->value])
        ->name('purchases.destroy');

    Route::middleware(['can:'.LabPermission::Inventory->value, 'can:'.LabPermission::Edit->value])->group(function () {
        Route::get('inventory-items/{inventory_item}/edit', [InventoryItemController::class, 'edit'])->name('inventory-items.edit');
        Route::match(['put', 'patch'], 'inventory-items/{inventory_item}', [InventoryItemController::class, 'update'])->name('inventory-items.update');
    });
    Route::delete('inventory-items/{inventory_item}', [InventoryItemController::class, 'destroy'])
        ->middleware(['can:'.LabPermission::Inventory->value, 'can:'.LabPermission::Delete->value])
        ->name('inventory-items.destroy');

    Route::middleware(['can:'.LabPermission::Expenses->value, 'can:'.LabPermission::Edit->value])->group(function () {
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::match(['put', 'patch'], 'expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    });
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->middleware(['can:'.LabPermission::Expenses->value, 'can:'.LabPermission::Delete->value])
        ->name('expenses.destroy');
});
