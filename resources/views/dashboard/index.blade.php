@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard / Ringkasan Budget')

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="page-title"><i class="bi bi-speedometer2 me-2 text-accent-blue"></i>Dashboard Monitoring Budget</h1>
        <p class="page-subtitle">Ringkasan serapan anggaran operasional per periode</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
            <select name="period" class="form-select form-select-sm form-select-dark" onchange="this.form.submit()" style="width: auto;">
                @foreach($periods as $p)
                    <option value="{{ $p }}" {{ $p === $period ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
        </form>
        <span class="badge" style="background: rgba(88,166,255,.15); color: #58a6ff; border: 1px solid rgba(88,166,255,.3); padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;">
            <i class="bi bi-calendar3 me-1"></i>{{ $period }}
        </span>
    </div>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="--card-accent: #58a6ff; --icon-bg: rgba(88,166,255,.1); --icon-color: #58a6ff;">
            <div class="stat-icon"><i class="bi bi-clipboard-data"></i></div>
            <div>
                <div class="stat-label">Total Budget Plan</div>
                <div class="stat-value" style="font-size:17px;">{{ number_format($totalPlanNominal/1000000, 1) }}Jt</div>
                <div class="stat-sub">{{ number_format($totalPlanQty, 0, ',', '.') }} unit total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="--card-accent: #3fb950; --icon-bg: rgba(63,185,80,.1); --icon-color: #3fb950;">
            <div class="stat-icon"><i class="bi bi-cart-check"></i></div>
            <div>
                <div class="stat-label">Total Actual</div>
                <div class="stat-value" style="font-size:17px;">{{ number_format($totalActualNominal/1000000, 1) }}Jt</div>
                <div class="stat-sub">{{ number_format($totalActualQty, 0, ',', '.') }} unit keluar</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="--card-accent: {{ $absorptionPercent > 100 ? '#f85149' : ($absorptionPercent > 80 ? '#f0883e' : '#bc8cff') }}; --icon-bg: rgba(188,140,255,.1); --icon-color: #bc8cff;">
            <div class="stat-icon"><i class="bi bi-pie-chart"></i></div>
            <div>
                <div class="stat-label">% Serapan Anggaran</div>
                <div class="stat-value {{ $absorptionPercent > 100 ? 'pct-over' : ($absorptionPercent > 80 ? 'pct-warn' : '') }}">
                    {{ $absorptionPercent }}%
                </div>
                <div class="stat-sub">dari total plan periode ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="--card-accent: {{ $overBudgetCount > 0 ? '#f85149' : '#3fb950' }}; --icon-bg: {{ $overBudgetCount > 0 ? 'rgba(248,81,73,.1)' : 'rgba(63,185,80,.1)' }}; --icon-color: {{ $overBudgetCount > 0 ? '#f85149' : '#3fb950' }};">
            <div class="stat-icon"><i class="bi bi-{{ $overBudgetCount > 0 ? 'exclamation-triangle' : 'check-circle' }}"></i></div>
            <div>
                <div class="stat-label">Over Budget</div>
                <div class="stat-value {{ $overBudgetCount > 0 ? 'pct-over' : 'pct-high' }}">{{ $overBudgetCount }}</div>
                <div class="stat-sub">item melampaui plan</div>
            </div>
        </div>
    </div>
</div>

<!-- MAIN GRID: Chart + Over Budget Table -->
<div class="row g-4 mb-4">
    <!-- Chart -->
    <div class="col-12 col-xl-7">
        <div class="card-dark h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2 text-accent-blue"></i>Plan vs Actual per Departemen
                </div>
                <small class="text-muted-custom">Juta Rupiah (Rp)</small>
            </div>
            <canvas id="deptChart" height="220"></canvas>
        </div>
    </div>

    <!-- Over Budget Alert -->
    <div class="col-12 col-xl-5">
        <div class="card-dark h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-accent-orange"></i>Item Over Budget
                </div>
                @if($overBudgetCount > 0)
                <span class="badge-over">{{ $overBudgetCount }} Item</span>
                @else
                <span class="badge-ok">Semua Normal</span>
                @endif
            </div>

            @if($overBudgetPlans->isEmpty())
            <div class="text-center py-4">
                <i class="bi bi-check-circle-fill text-accent-green" style="font-size: 40px;"></i>
                <p class="text-muted-custom mt-2 mb-0">Tidak ada item over budget</p>
            </div>
            @else
            <div style="max-height: 340px; overflow-y: auto;">
                @foreach($overBudgetPlans as $plan)
                <div class="mb-3 pb-3 border-bottom border-dark-custom">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <div style="font-size: 13px; font-weight: 600;">{{ $plan->item->item_name }}</div>
                            <div style="font-size: 11px; color: var(--text-secondary);">{{ $plan->department->dept_name }}</div>
                        </div>
                        <span class="badge-over">{{ $plan->pct }}%</span>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size: 11px; color: var(--text-secondary);">
                        <span>Plan: <b class="text-primary" style="color: var(--text-primary) !important;">{{ number_format($plan->plan_qty, 0, ',', '.') }}</b></span>
                        <span>Actual: <b class="pct-over">{{ number_format($plan->actual_qty, 0, ',', '.') }}</b></span>
                    </div>
                    <div class="budget-bar mt-2">
                        <div class="budget-bar-fill over" style="width: min({{ $plan->pct }}%, 100%);"></div>
                    </div>
                    @if(!$plan->has_reason)
                    <button class="btn btn-sm mt-2 badge-warn border-0"
                        onclick="openOverReasonModal({{ $plan->id }}, '{{ addslashes($plan->item->item_name) }}', '{{ addslashes($plan->department->dept_name) }}')"
                        style="font-size: 11px; cursor: pointer;">
                        <i class="bi bi-pencil me-1"></i>Input Alasan
                    </button>
                    @else
                    <small class="badge-ok mt-1 d-inline-block" style="font-size: 10px;"><i class="bi bi-check me-1"></i>Alasan sudah disubmit</small>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Recent Actuals Table -->
<div class="card-dark">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="card-title mb-0"><i class="bi bi-clock-history me-2 text-accent-blue"></i>Transaksi Actual Terbaru</div>
        <a href="{{ route('budget.actuals.index', ['period' => $period]) }}" class="btn-outline-dark text-decoration-none" style="font-size: 12px; padding: 6px 14px; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary);">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table-dark-custom w-100" id="recentTable">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Departemen</th>
                    <th>Item</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Nominal</th>
                    <th>Input Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActuals as $actual)
                <tr>
                    <td>{{ $actual->transaction_date->format('d/m/Y') }}</td>
                    <td><span style="background: rgba(88,166,255,.1); color: #58a6ff; padding: 2px 8px; border-radius: 4px; font-size: 11px;">{{ $actual->department->dept_code }}</span></td>
                    <td>
                        <div style="font-size: 13px; font-weight: 500;">{{ $actual->item->item_name }}</div>
                        <div style="font-size: 11px; color: var(--text-secondary);">{{ $actual->item->item_code }}</div>
                    </td>
                    <td class="text-end">{{ number_format($actual->actual_qty, 0, ',', '.') }} {{ $actual->item->unit }}</td>
                    <td class="text-end text-accent-green">{{ 'Rp ' . number_format($actual->actual_qty * $actual->unit_price_snapshot, 0, ',', '.') }}</td>
                    <td style="font-size: 12px; color: var(--text-secondary);">{{ $actual->creator->name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted-custom py-4">Belum ada transaksi periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: Over Budget Reason -->
<div class="modal fade modal-dark" id="overReasonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 15px; font-weight: 700;">
                    <i class="bi bi-exclamation-triangle-fill text-accent-orange me-2"></i>Form Alasan Over Budget
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="overReasonForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3 p-3" style="background: rgba(248,81,73,.1); border: 1px solid rgba(248,81,73,.2); border-radius: 10px;">
                        <div id="overModalItemName" style="font-weight: 600; font-size: 14px;"></div>
                        <div id="overModalDeptName" style="font-size: 12px; color: var(--text-secondary);"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-dark">Kategori Alasan</label>
                        <select name="reason_category" class="form-select form-select-dark" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Kebutuhan Mendadak">Kebutuhan Mendadak / Urgensi Operasional</option>
                            <option value="Kesalahan Estimasi">Kesalahan Estimasi Plan Awal</option>
                            <option value="Penambahan Order">Penambahan Order / Kapasitas Produksi</option>
                            <option value="Kondisi Darurat">Kondisi Darurat / Force Majeure</option>
                            <option value="Revisi Plan Terlambat">Revisi Plan Terlambat</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label-dark">Detail Uraian</label>
                        <textarea name="reason_detail" class="form-control form-control-dark" rows="4"
                            placeholder="Jelaskan detail penyebab over-budget dan tindak lanjutnya..." required minlength="20"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-dark">
                        <i class="bi bi-send me-1"></i>Submit Alasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Department Chart
const ctx = document.getElementById('deptChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [
            {
                label: 'Plan Budget',
                data: {!! json_encode($chartPlanData) !!},
                backgroundColor: 'rgba(31,111,235,.6)',
                borderColor: '#1f6feb',
                borderWidth: 2,
                borderRadius: 6,
            },
            {
                label: 'Actual',
                data: {!! json_encode($chartActualData) !!},
                backgroundColor: 'rgba(63,185,80,.6)',
                borderColor: '#3fb950',
                borderWidth: 2,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { labels: { color: '#8b949e', font: { family: 'Inter', size: 12 }, boxWidth: 12, padding: 16 } },
            tooltip: {
                backgroundColor: '#161b22', borderColor: '#30363d', borderWidth: 1,
                titleColor: '#e6edf3', bodyColor: '#8b949e',
                callbacks: {
                    label: ctx => ` ${ctx.dataset.label}: Rp ${(ctx.raw * 1000000).toLocaleString('id-ID')}`
                }
            }
        },
        scales: {
            x: { grid: { color: 'rgba(48,54,61,.5)' }, ticks: { color: '#8b949e', font: { family: 'Inter', size: 11 } } },
            y: { grid: { color: 'rgba(48,54,61,.5)' }, ticks: { color: '#8b949e', callback: v => 'Rp ' + v + 'Jt', font: { family: 'Inter', size: 11 } } },
        }
    }
});

// Over Budget Modal
function openOverReasonModal(planId, itemName, deptName) {
    document.getElementById('overReasonForm').action = '/budget/over-reason/' + planId;
    document.getElementById('overModalItemName').textContent = itemName;
    document.getElementById('overModalDeptName').textContent = deptName;
    new bootstrap.Modal(document.getElementById('overReasonModal')).show();
}
</script>
@endpush
