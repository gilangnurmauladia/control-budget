<?php

namespace App\Http\Controllers;

use App\Models\BudgetPlan;
use App\Models\BudgetOverReason;
use Illuminate\Http\Request;

class OverBudgetController extends Controller
{
    public function submitReason(Request $request, BudgetPlan $plan)
    {
        $validated = $request->validate([
            'reason_category' => 'required|string|max:100',
            'reason_detail'   => 'required|string|min:20',
        ]);

        BudgetOverReason::updateOrCreate(
            ['budget_plan_id' => $plan->id],
            [
                'flag_date'       => now()->toDateString(),
                'reason_category' => $validated['reason_category'],
                'reason_detail'   => $validated['reason_detail'],
                'submitted_by'    => auth()->id(),
            ]
        );

        return redirect()->route('dashboard')
            ->with('success', 'Alasan over-budget berhasil disubmit.');
    }
}
