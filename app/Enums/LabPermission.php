<?php

namespace App\Enums;

enum LabPermission: string
{
    case Dashboard = 'dashboard.view';
    case Reception = 'reception.manage';
    case Patients = 'patients.manage';
    case Doctors = 'doctors.manage';
    case Tests = 'tests.manage';
    case Lab = 'lab.manage';
    case Reports = 'reports.view';
    case Finance = 'finance.manage';
    case Suppliers = 'suppliers.manage';
    case Purchases = 'purchases.manage';
    case Inventory = 'inventory.manage';
    case Expenses = 'expenses.manage';
    case Settings = 'settings.view';
    case Staff = 'staff.manage';
    case Accounts = 'accounts.manage';
    case Edit = 'records.edit';
    case Delete = 'records.delete';

    public function label(): string
    {
        return match ($this) {
            self::Dashboard => __('Dashboard'),
            self::Reception => __('Reception and visits'),
            self::Patients => __('Patients'),
            self::Doctors => __('Doctors'),
            self::Tests => __('Tests'),
            self::Lab => __('Lab results and worklist'),
            self::Reports => __('Reports'),
            self::Finance => __('Finance'),
            self::Suppliers => __('Suppliers'),
            self::Purchases => __('Purchases'),
            self::Inventory => __('Inventory'),
            self::Expenses => __('Expenses'),
            self::Settings => __('Settings'),
            self::Staff => __('Staff and roles'),
            self::Accounts => __('Cash accounts'),
            self::Edit => __('Edit records'),
            self::Delete => __('Delete records'),
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::Dashboard, self::Reception, self::Patients => __('Front desk'),
            self::Lab, self::Reports => __('Laboratory'),
            self::Finance, self::Accounts => __('Finance'),
            self::Suppliers, self::Purchases, self::Inventory, self::Expenses => __('Store'),
            self::Doctors, self::Tests, self::Settings, self::Staff, self::Edit, self::Delete => __('Administration'),
        };
    }

    /**
     * @return array<string, list<self>>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::cases() as $permission) {
            $groups[$permission->group()][] = $permission;
        }

        return $groups;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function accepts(string $ability): bool
    {
        return in_array($ability, self::values(), true);
    }
}
