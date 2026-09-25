@extends('layouts.app')
@section('title', 'History Revisi Plan')

@section('content')
<div class="page-header d-flex align-items-center gap-3">
    <a href="{{ route('budget.plans.index') }}" class="btn-outline-dark text-decoration-none" style="font-size: 13px; padding: 7px 14px; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary);">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <div>
        <h1 class="page-title" style="font-size: 18px;"><i class="bi bi-clock-history me-2 text-accent-blue"></i>History Revisi Plan</h1>
        <p class="page-subtitle">{{ $plan->item->item_name }} — {{ $plan->department->dept_name }} — Periode {{ $plan->period }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card-dark">
            <div class="card-title">Info Plan Saat Ini</div>
            <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-content-between" style="font-size: 13px;">
                    <span class="text-muted-custom">Item</span>
                    <span>{{ $plan->item->item_name }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size: 13px;">
                    <span class="text-muted-custom">Departemen</span>
                    <span>{{ $plan->department->dept_name }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size: 13px;">
                    <span class="text-muted-custom">Periode</span>
                    <span>{{ $plan->period }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size: 13px;">
                    <span class="text-muted-custom">Plan Qty (Saat Ini)</span>
                    <strong class="text-accent-blue">{{ number_format($plan->plan_qty, 2, ',', '.') }} {{ $plan->item->unit }}</strong>
                </div>
                <div class="d-flex justify-content-between" style="font-size: 13px;">
                    <span class="text-muted-custom">Versi</span>
                    <span class="badge-ok">v{{ $plan->current_version }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size: 13px;">
                    <span class="text-muted-custom">Total Revisi</span>
                    <span>{{ $revisions->count() }} kali</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-dark">
            <div class="card-title">Log History Revisi</div>
            @if($revisions->isEmpty())
            <div class="text-center py-4 text-muted-custom">
                <i class="bi bi-journal-x" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                Belum ada revisi untuk plan ini.
            </div>
            @else
            <div class="timeline">
                @foreach($revisions as $rev)
                <div class="mb-4 pb-4 border-bottom border-dark-custom">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span style="background: rgba(88,166,255,.15); color: #58a6ff; border: 1px solid rgba(88,166,255,.3); padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                v{{ $rev->version_from }} → v{{ $rev->version_to }}
                            </span>
                            <span class="text-muted-custom" style="font-size: 11px;">{{ $rev->revision_date->format('d/m/Y H:i') }}</span>
                        </div>
                        <span class="text-muted-custom" style="font-size: 11px;">Oleh: {{ $rev->reviser->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex gap-4 mb-2" style="font-size: 13px;">
                        <div>
                            <span class="text-muted-custom" style="font-size: 11px; display: block;">Qty Sebelum</span>
                            <span class="text-accent-orange">{{ number_format($rev->old_plan_qty, 2, ',', '.') }}</span>
                        </div>
                        <div class="align-self-end text-muted-custom"><i class="bi bi-arrow-right"></i></div>
                        <div>
                            <span class="text-muted-custom" style="font-size: 11px; display: block;">Qty Sesudah</span>
                            <span class="text-accent-green">{{ number_format($rev->new_plan_qty, 2, ',', '.') }}</span>
                        </div>
                    </div>
                    <div style="font-size: 12px; color: var(--text-secondary);">
                        <strong style="color: var(--text-primary);">{{ $rev->reason_category }}</strong> — {{ $rev->reason_detail }}
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
