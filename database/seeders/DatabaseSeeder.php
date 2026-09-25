<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;
use App\Models\Location;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\User;
use App\Models\BudgetPlan;
use App\Models\BudgetPlanRevision;
use App\Models\BudgetActual;
use App\Models\BudgetOverReason;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // 1. PERMISSIONS
        // =============================================
        $permissions = [
            // Dashboard
            'dashboard.view',
            // Master Data
            'master.view', 'master.create', 'master.edit', 'master.delete', 'master.import', 'master.export',
            // Budget Plan
            'budget_plan.view', 'budget_plan.create', 'budget_plan.edit', 'budget_plan.revise', 'budget_plan.import', 'budget_plan.export',
            // Budget Actual
            'budget_actual.view', 'budget_actual.create', 'budget_actual.edit', 'budget_actual.delete', 'budget_actual.import', 'budget_actual.export',
            // Reports
            'report.view', 'report.export',
            // Over Budget
            'over_budget.view', 'over_budget.submit',
            // User Management
            'user.view', 'user.create', 'user.edit', 'user.delete',
            // Roles
            'role.view', 'role.create', 'role.edit', 'role.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // =============================================
        // 2. ROLES & ASSIGN PERMISSIONS
        // =============================================
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        $picDept = Role::firstOrCreate(['name' => 'PIC Departemen']);
        $picDept->syncPermissions([
            'dashboard.view',
            'budget_plan.view', 'budget_plan.create', 'budget_plan.edit', 'budget_plan.revise', 'budget_plan.export', 'budget_plan.import',
            'budget_actual.view',
            'over_budget.view', 'over_budget.submit',
            'report.view', 'report.export',
        ]);

        $adminGudang = Role::firstOrCreate(['name' => 'Admin Gudang']);
        $adminGudang->syncPermissions([
            'dashboard.view',
            'budget_actual.view', 'budget_actual.create', 'budget_actual.edit', 'budget_actual.delete', 'budget_actual.import', 'budget_actual.export',
            'budget_plan.view',
            'master.view',
        ]);

        $manajemen = Role::firstOrCreate(['name' => 'Manajemen']);
        $manajemen->syncPermissions([
            'dashboard.view',
            'budget_plan.view', 'budget_plan.export',
            'budget_actual.view', 'budget_actual.export',
            'report.view', 'report.export',
            'over_budget.view',
            'master.view',
        ]);

        // =============================================
        // 3. DEPARTMENTS
        // =============================================
        $depts = [
            ['dept_code' => 'PRD', 'dept_name' => 'Produksi'],
            ['dept_code' => 'MTC', 'dept_name' => 'Maintenance'],
            ['dept_code' => 'QC', 'dept_name' => 'Quality Control'],
            ['dept_code' => 'WH', 'dept_name' => 'Warehouse'],
            ['dept_code' => 'HR', 'dept_name' => 'HR & GA'],
        ];

        foreach ($depts as $dept) {
            Department::firstOrCreate(['dept_code' => $dept['dept_code']], $dept);
        }

        $deptProd = Department::where('dept_code', 'PRD')->first();
        $deptMtc  = Department::where('dept_code', 'MTC')->first();
        $deptQc   = Department::where('dept_code', 'QC')->first();
        $deptWh   = Department::where('dept_code', 'WH')->first();
        $deptHr   = Department::where('dept_code', 'HR')->first();

        // =============================================
        // 4. LOCATIONS (General per Dept + extras)
        // =============================================
        $locations = [
            ['department_id' => $deptProd->id, 'location_code' => 'PRD-GEN', 'location_name' => 'GENERAL - Produksi', 'is_default' => true],
            ['department_id' => $deptProd->id, 'location_code' => 'PRD-M01', 'location_name' => 'Mesin Injection 001', 'is_default' => false],
            ['department_id' => $deptProd->id, 'location_code' => 'PRD-M02', 'location_name' => 'Mesin Injection 002', 'is_default' => false],
            ['department_id' => $deptMtc->id, 'location_code' => 'MTC-GEN', 'location_name' => 'GENERAL - Maintenance', 'is_default' => true],
            ['department_id' => $deptMtc->id, 'location_code' => 'MTC-WS1', 'location_name' => 'Workshop Area A', 'is_default' => false],
            ['department_id' => $deptQc->id, 'location_code' => 'QC-GEN', 'location_name' => 'GENERAL - QC', 'is_default' => true],
            ['department_id' => $deptQc->id, 'location_code' => 'QC-LAB', 'location_name' => 'Laboratorium QC', 'is_default' => false],
            ['department_id' => $deptWh->id, 'location_code' => 'WH-GEN', 'location_name' => 'GENERAL - Warehouse', 'is_default' => true],
            ['department_id' => $deptHr->id, 'location_code' => 'HR-GEN', 'location_name' => 'GENERAL - HR & GA', 'is_default' => true],
        ];

        foreach ($locations as $loc) {
            Location::firstOrCreate(['location_code' => $loc['location_code']], $loc);
        }

        // =============================================
        // 5. ITEM CATEGORIES
        // =============================================
        $categories = ['Consumable', 'Toolings', 'Material', 'APD'];
        foreach ($categories as $cat) {
            ItemCategory::firstOrCreate(['category_name' => $cat]);
        }

        $catConsumable = ItemCategory::where('category_name', 'Consumable')->first();
        $catToolings   = ItemCategory::where('category_name', 'Toolings')->first();
        $catMaterial   = ItemCategory::where('category_name', 'Material')->first();
        $catAPD        = ItemCategory::where('category_name', 'APD')->first();

        // =============================================
        // 6. MASTER ITEMS (10 items)
        // =============================================
        $items = [
            ['category_id' => $catAPD->id,        'item_code' => 'APD-001', 'item_name' => 'Sarung Tangan Vinyl (Box)',      'unit' => 'Box',    'unit_price' => 75000],
            ['category_id' => $catAPD->id,        'item_code' => 'APD-002', 'item_name' => 'Masker Medis 3 Ply (Box)',       'unit' => 'Box',    'unit_price' => 85000],
            ['category_id' => $catAPD->id,        'item_code' => 'APD-003', 'item_name' => 'Sepatu Safety (Pcs)',            'unit' => 'Pcs',    'unit_price' => 450000],
            ['category_id' => $catConsumable->id, 'item_code' => 'CSM-001', 'item_name' => 'Majun / Kain Lab (Kg)',          'unit' => 'Kg',     'unit_price' => 25000],
            ['category_id' => $catConsumable->id, 'item_code' => 'CSM-002', 'item_name' => 'Oli Pelumas Mesin (Liter)',      'unit' => 'Liter',  'unit_price' => 65000],
            ['category_id' => $catConsumable->id, 'item_code' => 'CSM-003', 'item_name' => 'Greases / Gemuk Mesin (Kg)',    'unit' => 'Kg',     'unit_price' => 55000],
            ['category_id' => $catToolings->id,   'item_code' => 'TLG-001', 'item_name' => 'Apex Tool Set (Set)',           'unit' => 'Set',    'unit_price' => 1250000],
            ['category_id' => $catToolings->id,   'item_code' => 'TLG-002', 'item_name' => 'Kunci Pas Set 8-24mm (Set)',    'unit' => 'Set',    'unit_price' => 550000],
            ['category_id' => $catMaterial->id,   'item_code' => 'MTR-001', 'item_name' => 'Drum Wire / Kawat Baja (Roll)', 'unit' => 'Roll',   'unit_price' => 320000],
            ['category_id' => $catMaterial->id,   'item_code' => 'MTR-002', 'item_name' => 'Amplas Halus No.400 (Lbr)',    'unit' => 'Lembar', 'unit_price' => 8500],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(['item_code' => $item['item_code']], $item);
        }

        // =============================================
        // 7. USERS (1 per role)
        // =============================================
        $userSuperAdmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@budgetcontrol.com',
                'username' => 'superadmin',
                'password' => Hash::make('password123'),
                'department_id' => null,
                'is_active' => true,
            ]
        );
        $userSuperAdmin->syncRoles(['Super Admin']);

        $userPicProd = User::firstOrCreate(
            ['username' => 'pic_produksi'],
            [
                'name' => 'Budi Santoso (Spv Produksi)',
                'email' => 'pic.produksi@budgetcontrol.com',
                'username' => 'pic_produksi',
                'password' => Hash::make('password123'),
                'department_id' => $deptProd->id,
                'is_active' => true,
            ]
        );
        $userPicProd->syncRoles(['PIC Departemen']);

        $userPicMtc = User::firstOrCreate(
            ['username' => 'pic_maintenance'],
            [
                'name' => 'Ahmad Fauzi (Spv Maintenance)',
                'email' => 'pic.mtc@budgetcontrol.com',
                'username' => 'pic_maintenance',
                'password' => Hash::make('password123'),
                'department_id' => $deptMtc->id,
                'is_active' => true,
            ]
        );
        $userPicMtc->syncRoles(['PIC Departemen']);

        $userAdminGudang = User::firstOrCreate(
            ['username' => 'admin_gudang'],
            [
                'name' => 'Siti Rahayu (Admin Gudang)',
                'email' => 'admin.gudang@budgetcontrol.com',
                'username' => 'admin_gudang',
                'password' => Hash::make('password123'),
                'department_id' => $deptWh->id,
                'is_active' => true,
            ]
        );
        $userAdminGudang->syncRoles(['Admin Gudang']);

        $userManajemen = User::firstOrCreate(
            ['username' => 'manager'],
            [
                'name' => 'Ir. Dharma Kusuma (Manajer)',
                'email' => 'manager@budgetcontrol.com',
                'username' => 'manager',
                'password' => Hash::make('password123'),
                'department_id' => null,
                'is_active' => true,
            ]
        );
        $userManajemen->syncRoles(['Manajemen']);

        // =============================================
        // 8. SAMPLE BUDGET PLANS & ACTUALS (Demo Data)
        // =============================================
        $period = Carbon::now()->format('Y-m'); // Current month
        $prevPeriod = Carbon::now()->subMonth()->format('Y-m');

        $allItems = Item::all();
        $adminGudangUser = User::where('username', 'admin_gudang')->first();

        // Budget Plans untuk Produksi - periode ini
        $planItems = [
            ['item_code' => 'APD-001', 'plan_qty' => 20, 'dept' => $deptProd],
            ['item_code' => 'APD-002', 'plan_qty' => 15, 'dept' => $deptProd],
            ['item_code' => 'CSM-001', 'plan_qty' => 50, 'dept' => $deptProd],
            ['item_code' => 'CSM-002', 'plan_qty' => 100, 'dept' => $deptProd],
            ['item_code' => 'MTR-001', 'plan_qty' => 10, 'dept' => $deptProd],
            // Maintenance
            ['item_code' => 'TLG-001', 'plan_qty' => 5, 'dept' => $deptMtc],
            ['item_code' => 'TLG-002', 'plan_qty' => 8, 'dept' => $deptMtc],
            ['item_code' => 'CSM-002', 'plan_qty' => 80, 'dept' => $deptMtc],
            ['item_code' => 'CSM-003', 'plan_qty' => 30, 'dept' => $deptMtc],
            // QC
            ['item_code' => 'APD-001', 'plan_qty' => 10, 'dept' => $deptQc],
            ['item_code' => 'MTR-002', 'plan_qty' => 200, 'dept' => $deptQc],
        ];

        foreach ($planItems as $pi) {
            $item = Item::where('item_code', $pi['item_code'])->first();
            if (!$item) continue;

            $plan = BudgetPlan::firstOrCreate(
                [
                    'period' => $period,
                    'department_id' => $pi['dept']->id,
                    'item_id' => $item->id,
                ],
                [
                    'location_id' => null,
                    'plan_qty' => $pi['plan_qty'],
                    'unit_price_snapshot' => $item->unit_price,
                    'current_version' => 1.0,
                    'created_by' => $userSuperAdmin->id,
                ]
            );
        }

        // Budget Actuals (beberapa transaksi demo)
        $actualData = [
            // Produksi - normal
            ['dept' => $deptProd, 'item_code' => 'APD-001', 'qty' => 8,   'day' => 2],
            ['dept' => $deptProd, 'item_code' => 'APD-001', 'qty' => 5,   'day' => 8],
            ['dept' => $deptProd, 'item_code' => 'APD-002', 'qty' => 6,   'day' => 3],
            ['dept' => $deptProd, 'item_code' => 'CSM-001', 'qty' => 35,  'day' => 5],
            ['dept' => $deptProd, 'item_code' => 'CSM-002', 'qty' => 95,  'day' => 6], // mendekati limit
            ['dept' => $deptProd, 'item_code' => 'MTR-001', 'qty' => 12,  'day' => 9], // OVER BUDGET (>10)
            // Maintenance - normal
            ['dept' => $deptMtc, 'item_code' => 'TLG-001', 'qty' => 2,   'day' => 4],
            ['dept' => $deptMtc, 'item_code' => 'TLG-002', 'qty' => 10,  'day' => 7], // OVER BUDGET (>8)
            ['dept' => $deptMtc, 'item_code' => 'CSM-002', 'qty' => 45,  'day' => 5],
            ['dept' => $deptMtc, 'item_code' => 'CSM-003', 'qty' => 22,  'day' => 10],
            // QC - normal
            ['dept' => $deptQc, 'item_code' => 'APD-001', 'qty' => 4,    'day' => 3],
            ['dept' => $deptQc, 'item_code' => 'MTR-002', 'qty' => 80,   'day' => 6],
        ];

        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        foreach ($actualData as $ad) {
            $item = Item::where('item_code', $ad['item_code'])->first();
            if (!$item) continue;

            $txDate = Carbon::createFromDate($currentYear, $currentMonth, $ad['day']);
            if ($txDate->isFuture()) {
                $txDate = Carbon::now()->subDays(rand(1, 5));
            }

            BudgetActual::create([
                'transaction_date' => $txDate->toDateString(),
                'department_id' => $ad['dept']->id,
                'location_id' => null,
                'item_id' => $item->id,
                'actual_qty' => $ad['qty'],
                'unit_price_snapshot' => $item->unit_price,
                'notes' => 'Data demo - ' . $item->item_name,
                'created_by' => $adminGudangUser->id,
            ]);
        }

        // Over-budget reasons untuk item yang sudah over
        $overPlanMtrProd = BudgetPlan::where('period', $period)
            ->where('department_id', $deptProd->id)
            ->whereHas('item', fn($q) => $q->where('item_code', 'MTR-001'))
            ->first();

        if ($overPlanMtrProd) {
            BudgetOverReason::firstOrCreate(
                ['budget_plan_id' => $overPlanMtrProd->id],
                [
                    'flag_date' => Carbon::now()->subDays(1)->toDateString(),
                    'reason_category' => 'Kebutuhan Mendadak',
                    'reason_detail' => 'Terjadi penambahan order mendadak dari customer XYZ yang membutuhkan material tambahan di luar rencana awal bulan. Pengajuan sudah dikonfirmasi oleh Manager Produksi.',
                    'submitted_by' => $userPicProd->id,
                ]
            );
        }

        // Revisi Plan - satu contoh
        $planToRevise = BudgetPlan::where('period', $period)
            ->where('department_id', $deptMtc->id)
            ->whereHas('item', fn($q) => $q->where('item_code', 'TLG-002'))
            ->first();

        if ($planToRevise && $planToRevise->current_version == 1.0) {
            BudgetPlanRevision::create([
                'budget_plan_id' => $planToRevise->id,
                'revision_date' => Carbon::now()->subDays(3),
                'old_plan_qty' => $planToRevise->plan_qty,
                'new_plan_qty' => 12,
                'version_from' => 1.0,
                'version_to' => 1.1,
                'reason_category' => 'Penambahan Kebutuhan',
                'reason_detail' => 'Penambahan jadwal preventive maintenance mesin tambahan dari schedule October yang dimajukan ke bulan ini.',
                'revised_by' => $userPicMtc->id,
            ]);

            $planToRevise->update([
                'plan_qty' => 12,
                'current_version' => 1.1,
            ]);
        }

        $this->command->info('✅ Seeder selesai! Akun default:');
        $this->command->info('   superadmin / password123 (Super Admin)');
        $this->command->info('   pic_produksi / password123 (PIC Departemen - Produksi)');
        $this->command->info('   pic_maintenance / password123 (PIC Departemen - Maintenance)');
        $this->command->info('   admin_gudang / password123 (Admin Gudang)');
        $this->command->info('   manager / password123 (Manajemen)');
    }
}
