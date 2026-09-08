<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Doctor;
use App\Models\InventoryCatalogItem;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Test;
use App\Support\DatabaseBackup;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(DatabaseBackup $backups): View
    {
        return view('settings.index', [
            'backupCount' => count($backups->list()),
            'doctorCount' => Doctor::query()->count(),
            'testCount' => Test::query()->count(),
            'summaryCount' => Test::query()
                ->whereNotNull('interpretation')
                ->where('interpretation', '!=', '')
                ->count(),
            'roleCount' => Role::query()->count(),
            'staffCount' => Staff::query()->count(),
            'accountCount' => Account::query()->count(),
            'goodsCount' => InventoryCatalogItem::query()->count(),
        ]);
    }
}
