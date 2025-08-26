@extends('layouts.app')

@section('styles')
    <style>
        .summary-card { background:#fff; border:1px solid #eef2f7; padding:1rem; border-radius:8px; }
        .summary-value { font-size:1.5rem; font-weight:700; }
        .muted-small { color:#6b7280; font-size:0.9rem; }
    /* Chart container fixed height to avoid resize loops on mobile */
    .chart-card { height:320px; }
    .chart-card canvas { width:100% !important; height:100% !important; display:block; }
    </style>
@endsection

@section('content')
    <div class="container mt-4 px-2">
       

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
                <div class="summary-card">
                    <div class="muted-small">Total Pemasukan (Bulan ini)</div>
                    <div class="summary-value">Rp {{ number_format($incomeThisMonth,0,',','.') }}</div>
                    <div class="muted-small">Bulan lalu: Rp {{ number_format($incomeLastMonth,0,',','.') }}</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="summary-card">
                    <div class="muted-small">Total Pengeluaran (Bulan ini)</div>
                    <div class="summary-value">Rp {{ number_format($expenseThisMonth,0,',','.') }}</div>
                    <div class="muted-small">Bulan lalu: Rp {{ number_format($expenseLastMonth,0,',','.') }}</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="summary-card">
                    <div class="muted-small">Saldo Bersih (Bulan ini)</div>
                    <div class="summary-value">Rp {{ number_format($netBalance,0,',','.') }}</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="summary-card">
                    <div class="muted-small">Insight(Bulan ini)</div>
                    @if($topCategoryName)
                        <p class="mb-0 summary-value">Kategori terbesar pengeluaran bulan ini adalah <strong>{{ $topCategoryName }}</strong> sebesar <strong>{{ $topCategoryPercent }}%</strong> dari total pengeluaran.</p>
                    @else
                        <p class="mb-0 text-muted">Belum ada data pengeluaran untuk bulan ini.</p>
                    @endif
                </div>
            </div>
            
              
        </div>

        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card p-3">
                    <div class="card-body">
                        <h6>Ringkasan Cepat</h6>
                        <p class="mb-1">
                            @if(isset($expenseChangePercent))
                                @if($expenseChangePercent > 0)
                                    Pengeluaran bulan ini naik <strong>{{ $expenseChangePercent }}%</strong> dibanding bulan lalu, terutama pada kategori <strong>{{ $topCategoryName ?? '—' }}</strong>.
                                @elseif($expenseChangePercent < 0)
                                    Pengeluaran bulan ini turun <strong>{{ abs($expenseChangePercent) }}%</strong> dibanding bulan lalu.
                                @else
                                    Pengeluaran bulan ini tidak berubah dibanding bulan lalu.
                                @endif
                            @else
                                Tidak cukup data untuk membandingkan dengan bulan lalu.
                            @endif
                        </p>
                        <p class="mb-1">
                            @if(isset($incomeChangePercent) && abs($incomeChangePercent) <= 5)
                                Pemasukan stabil, tetapi pengeluaran meningkat di akhir pekan sebesar <strong>{{ $weekendExpensePercent }}%</strong> dari total pengeluaran bulan ini.
                            @elseif(isset($incomeChangePercent))
                                Pemasukan berubah <strong>{{ $incomeChangePercent }}%</strong> dibanding bulan lalu.
                            @else
                                Tidak cukup data pemasukan untuk analisis.
                            @endif
                        </p>
                        <p class="mb-1">
                            @if(isset($topCategoryName) && $topCategoryName)
                                Pengeluaran terbesar ada di kategori <strong>{{ $topCategoryName }}</strong> ({{ $topCategoryPercent }}% dari total). Coba kurangi 10% agar bisa menghemat <strong>Rp {{ number_format($potentialSavingIf10pct,0,',','.') }}</strong> per bulan.
                            @endif
                        </p>
                        @if(isset($netBalanceNegative) && $netBalanceNegative)
                            <div class="alert alert-danger mt-2 p-2" style="background:#fff1f2;border:1px solid #fee2e2;color:#7f1d1d;">Saldo bersih bulan ini negatif. Pertimbangkan menambah pemasukan atau mengurangi pengeluaran pada kategori hiburan atau makanan luar.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($budgetAlerts))
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card p-3">
                        <div class="card-body">
                            <h6>Pengingat Anggaran</h6>
                            <div class="d-flex flex-column gap-2">
                                @foreach($budgetAlerts as $a)
                                    <div class="p-2" style="background:#fff;border:1px solid #f1f3f5;border-radius:6px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $a['category_name'] }}</strong>
                                                <div class="small text-muted">Penggunaan: {{ $a['percent'] }}% dari Rp {{ number_format($a['budget'],0,',','.') }} (terpakai Rp {{ number_format($a['spent'],0,',','.') }})</div>
                                            </div>
                                            <div>
                                                @if($a['percent'] >= 100)
                                                    <span class="badge bg-danger">Terlampaui</span>
                                                @elseif($a['percent'] >= 80)
                                                    <span class="badge bg-warning text-dark">80%+</span>
                                                @else
                                                    <span class="badge bg-secondary">OK</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

     
        <div class="row g-3 mt-3">
            <div class="col-12 col-md-6">
                <div class="card p-3 chart-card">
                    <div class="card-body">
                        <h6>Distribusi Pengeluaran (Bulan ini)</h6>
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card p-3 chart-card">
                    <div class="card-body">
                        <h6>Distribusi Pemasukan (Bulan ini)</h6>
                        <canvas id="incomeDonutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
      
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const months = @json($months);
        const incomeData = @json($incomeSeries);
        const expenseData = @json($expenseSeries);

        // const ctx = document.getElementById('trendChart').getContext('2d');
        // new Chart(ctx, {
        //     type: 'line',
        //     data: {
        //         labels: months,
        //         datasets: [
        //             {
        //                 label: 'Pemasukan',
        //                 data: incomeData,
        //                 borderColor: '#111827',
        //                 backgroundColor: 'rgba(17,24,39,0.05)',
        //                 tension: 0.2,
        //             },
        //             {
        //                 label: 'Pengeluaran',
        //                 data: expenseData,
        //                 borderColor: '#ef4444',
        //                 backgroundColor: 'rgba(239,68,68,0.05)',
        //                 tension: 0.2,
        //             }
        //         ]
        //     },
        //     options: {
        //         responsive: true,
        //         maintainAspectRatio: false,
        //         animation: { duration: 0 },
        //         responsiveAnimationDuration: 0,
        //         scales: {
        //             y: { beginAtZero: true }
        //         }
        //     }
        // });

        // Donut chart for expense by category
        const donutCtx = document.getElementById('donutChart').getContext('2d');
        const expenseByCategory = @json($expenseByCategory->map(function($r){ return ['id' => $r->category_id, 'label' => $r->category?->name ?? 'Lainnya', 'value' => (float)$r->total]; }));
        const donutLabels = expenseByCategory.map(i => i.label);
        const donutData = expenseByCategory.map(i => i.value);
        const donutIds = expenseByCategory.map(i => i.id);

        const expenseDonut = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{ data: donutData, backgroundColor: ['#111827','#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6'] }]
            },
            options: {
                // disable Chart.js animation and responsive resize to avoid continuous rAF work
                responsive: false,
                maintainAspectRatio: false,
                animations: false,
                // small resize delay if responsive is enabled elsewhere
                resizeDelay: 200
            }
        });

        // Income donut
        const incomeCtx = document.getElementById('incomeDonutChart').getContext('2d');
        const incomeByCategory = @json($incomeByCategory->map(function($r){ return ['id' => $r->category_id, 'label' => $r->category?->name ?? 'Lainnya', 'value' => (float)$r->total]; }));
        const incomeLabels = incomeByCategory.map(i => i.label);
        const incomeDataDonut = incomeByCategory.map(i => i.value);
        const incomeIds = incomeByCategory.map(i => i.id);

        const incomeDonut = new Chart(incomeCtx, {
            type: 'doughnut',
            data: {
                labels: incomeLabels,
                datasets: [{ data: incomeDataDonut, backgroundColor: ['#10b981','#3b82f6','#8b5cf6','#f59e0b','#ef4444','#111827'] }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                animations: false,
                resizeDelay: 200
            }
        });

        // Click handlers for drill-down. Redirect to home with query params: monthly, month=currentMonthParam, category_id, type
        const currentMonth = @json($currentMonthParam);
        document.getElementById('donutChart').onclick = function(evt) {
            const active = expenseDonut.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
            if (active.length) {
                const idx = active[0].index;
                const catId = donutIds[idx];
                if (catId) {
                    window.location = `/home?filter_type=monthly&month=${currentMonth}&category_id=${catId}&type=pengeluaran`;
                }
            }
        };

        document.getElementById('incomeDonutChart').onclick = function(evt) {
            const active = incomeDonut.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
            if (active.length) {
                const idx = active[0].index;
                const catId = incomeIds[idx];
                if (catId) {
                    window.location = `/home?filter_type=monthly&month=${currentMonth}&category_id=${catId}&type=pemasukan`;
                }
            }
        };
    </script>
@endsection
