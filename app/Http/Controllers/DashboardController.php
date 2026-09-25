<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BudgetPlan;
use App\Models\BudgetActual;
use App\Models\Department;
use App\Models\Item;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $period = $request->get('period', Carbon::now()->format('Y-m'));

        // Tentukan scope departemen berdasarkan role
        $deptQuery = Department::query();
        if ($user->hasRole(['PIC Departemen', 'Admin Gudang']) && $user->department_id) {
            $deptIds = [$user->department_id];
        } else {
            $deptIds = Department::pluck('id')->toArray();
        }

        // --- Summary Cards ---
        $totalPlanNominal = BudgetPlan::where('period', $period)
            ->whereIn('department_id', $deptIds)
            ->selectRaw('SUM(plan_qty * unit_price_snapshot) as total')
            ->value('total') ?? 0;

        $totalActualNominal = BudgetActual::whereIn('department_id', $deptIds)
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
            ->selectRaw('SUM(actual_qty * unit_price_snapshot) as total')
            ->value('total') ?? 0;

        $totalPlanQty = BudgetPlan::where('period', $period)
            ->whereIn('department_id', $deptIds)
            ->sum('plan_qty');

        $totalActualQty = BudgetActual::whereIn('department_id', $deptIds)
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
            ->sum('actual_qty');

        $absorptionPercent = $totalPlanNominal > 0
            ? round(($totalActualNominal / $totalPlanNominal) * 100, 1)
            : 0;

        // --- Over-Budget count ---
        $overBudgetCount = $this->getOverBudgetPlans($period, $deptIds)->count();

        // --- Chart: Dept Comparison (Plan vs Actual Nominal) ---
        $depts = Department::whereIn('id', $deptIds)->get();
        $chartLabels = [];
        $chartPlanData = [];
        $chartActualData = [];

        foreach ($depts as $dept) {
            $planNom = BudgetPlan::where('period', $period)
                ->where('department_id', $dept->id)
                ->selectRaw('COALESCE(SUM(plan_qty * unit_price_snapshot),0) as total')
                ->value('total');

            $actualNom = BudgetActual::where('department_id', $dept->id)
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
                ->selectRaw('COALESCE(SUM(actual_qty * unit_price_snapshot),0) as total')
                ->value('total');

            $chartLabels[]      = $dept->dept_name;
            $chartPlanData[]    = round($planNom / 1000000, 2); // juta rupiah
            $chartActualData[]  = round($actualNom / 1000000, 2);
        }

        // --- Over-Budget Table ---
        $overBudgetPlans = $this->getOverBudgetPlans($period, $deptIds)
            ->with(['department', 'item', 'item.category', 'overReasons'])
            ->get()
            ->map(function ($plan) use ($period) {
                $actualQty = BudgetActual::where('department_id', $plan->department_id)
                    ->where('item_id', $plan->item_id)
                    ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
                    ->sum('actual_qty');

                $plan->actual_qty   = $actualQty;
                $plan->pct          = $plan->plan_qty > 0 ? round(($actualQty / $plan->plan_qty) * 100, 1) : 0;
                $plan->has_reason   = $plan->overReasons->count() > 0;
                return $plan;
            });

        // --- Recent Actuals ---
        $recentActuals = BudgetActual::with(['department', 'item', 'creator'])
            ->whereIn('department_id', $deptIds)
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // --- Periods list for filter ---
        $periods = [];
        for ($i = 0; $i < 12; $i++) {
            $periods[] = Carbon::now()->subMonths($i)->format('Y-m');
        }

        return view('dashboard.index', compact(
            'period', 'periods',
            'totalPlanNominal', 'totalActualNominal',
            'totalPlanQty', 'totalActualQty',
            'absorptionPercent', 'overBudgetCount',
            'chartLabels', 'chartPlanData', 'chartActualData',
            'overBudgetPlans', 'recentActuals', 'depts'
        ));
    }

    private function getOverBudgetPlans($period, $deptIds)
    {
        return BudgetPlan::where('period', $period)
            ->whereIn('department_id', $deptIds)
            ->whereRaw("plan_qty > 0")
            ->whereExists(function ($query) use ($period) {
                $query->select(DB::raw(1))
                    ->from('budget_actuals as ba')
                    ->whereColumn('ba.department_id', 'budget_plans.department_id')
                    ->whereColumn('ba.item_id', 'budget_plans.item_id')
                    ->whereRaw("DATE_FORMAT(ba.transaction_date, '%Y-%m') = ?", [$period])
                    ->havingRaw('SUM(ba.actual_qty) > budget_plans.plan_qty')
                    ->groupBy('ba.department_id', 'ba.item_id');
            });
    }
}
