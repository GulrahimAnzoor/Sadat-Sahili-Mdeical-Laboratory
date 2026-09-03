<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Doctor;
use App\Models\InventoryCatalogItem;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Test;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'doctorCount' => Doctor::query()->count(),
            'testCount' => Test::query()->count(),
            'summaryCount' => Test::query()
                ->whereNotNull('interpretation')
                ->where('interpretation', '!=', '')
                ->count(),
            'templateCount' => Test::query()->whereNotNull('template_path')->count(),
            'roleCount' => Role::query()->count(),
            'staffCount' => Staff::query()->count(),
            'accountCount' => Account::query()->count(),
            'goodsCount' => InventoryCatalogItem::query()->count(),
        ]);
    }
}
