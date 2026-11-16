<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BudgetPlanController;
use App\Http\Controllers\BudgetActualController;
use App\Http\Controllers\OverBudgetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data - Departments
    Route::prefix('master/departments')->name('master.departments.')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
        Route::get('/export/excel', [DepartmentController::class, 'export'])->name('export');
        Route::post('/import', [DepartmentController::class, 'import'])->name('import');
    });

    // Master Data - Items
    Route::prefix('master/items')->name('master.items.')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::put('/{item}', [ItemController::class, 'update'])->name('update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
        Route::get('/export/excel', [ItemController::class, 'export'])->name('export');
        Route::post('/import', [ItemController::class, 'import'])->name('import');
    });

    // Master Data - Item Categories
    Route::prefix('master/categories')->name('master.categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ItemCategoryController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\ItemCategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [\App\Http\Controllers\ItemCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [\App\Http\Controllers\ItemCategoryController::class, 'destroy'])->name('destroy');
    });

    // Budget Plans
    Route::prefix('budget/plans')->name('budget.plans.')->group(function () {
        Route::get('/', [BudgetPlanController::class, 'index'])->name('index');
        Route::post('/', [BudgetPlanController::class, 'store'])->name('store');
        Route::post('/{plan}/revise', [BudgetPlanController::class, 'revise'])->name('revise');
        Route::get('/{plan}/revisions', [BudgetPlanController::class, 'revisionHistory'])->name('revisions');
        Route::get('/export/excel', [BudgetPlanController::class, 'export'])->name('export');
        Route::get('/export/pdf', [BudgetPlanController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/import', [BudgetPlanController::class, 'import'])->name('import');
    });

    // Budget Actuals
    Route::prefix('budget/actuals')->name('budget.actuals.')->group(function () {
        Route::get('/', [BudgetActualController::class, 'index'])->name('index');
        Route::post('/', [BudgetActualController::class, 'store'])->name('store');
        Route::get('/{actual}/edit', [BudgetActualController::class, 'edit'])->name('edit');
        Route::put('/{actual}', [BudgetActualController::class, 'update'])->name('update');
        Route::delete('/{actual}', [BudgetActualController::class, 'destroy'])->name('destroy');
        Route::get('/export/excel', [BudgetActualController::class, 'export'])->name('export');
        Route::get('/export/pdf', [BudgetActualController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/import', [BudgetActualController::class, 'import'])->name('import');
    });

    // Over-Budget Justification
    Route::post('/budget/over-reason/{plan}', [OverBudgetController::class, 'submitReason'])->name('over_budget.submit');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
    });

    // Admin - User Management
    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Admin - Roles & Permissions
    Route::prefix('admin/roles')->name('admin.roles.')->group(function () {
        Route::get('/', [App\Http\Controllers\RoleController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\RoleController::class, 'store'])->name('store');
        Route::put('/{role}', [App\Http\Controllers\RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [App\Http\Controllers\RoleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/permissions')->name('admin.permissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\PermissionController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\PermissionController::class, 'store'])->name('store');
        Route::delete('/{permission}', [App\Http\Controllers\PermissionController::class, 'destroy'])->name('destroy');
    });

    // API - Get locations by department (for dynamic form)
    Route::get('/api/locations/{department}', function ($departmentId) {
        $locations = \App\Models\Location::where('department_id', $departmentId)
            ->where('status', 'active')
            ->orderBy('is_default', 'desc')
            ->orderBy('location_name')
            ->get(['id', 'location_name', 'is_default']);
        return response()->json($locations);
    })->name('api.locations');

    // API - Get items by department (for dynamic form)
    Route::get('/api/departments/{department}/items', function (\Illuminate\Http\Request $request, $departmentId) {
        $period = $request->get('period');
        $dept = \App\Models\Department::findOrFail($departmentId);
        
        $items = $dept->items()->where('status', 'active')->orderBy('item_name')->get(['master_items.id', 'item_code', 'item_name', 'unit']);
        
        // Check planned items for this period
        $plannedItemIds = \App\Models\BudgetPlan::where('period', $period)
            ->where('department_id', $departmentId)
            ->pluck('item_id')
            ->toArray();

        $items->transform(function ($item) use ($plannedItemIds) {
            $item->is_planned = in_array($item->id, $plannedItemIds);
            return $item;
        });

        return response()->json($items);
    })->name('api.department.items');
});
