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
                        <p class="mb-0 small text-muted mt-1">Rata-rata pengeluaran harian sejak awal bulan: <strong>Rp {{ number_format($avgDailySinceMonth ?? 0,0,',','.') }}</strong></p>
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
                <div class="card p-3">
                    <div class="card-body">
                        <h6>Peringkat Pengeluaran (Bulan ini)</h6>
                        <div class="list-group mt-2">
                            @php $rank = 0; @endphp
                            @forelse($expenseByCategory->sortByDesc('total') as $cat)
                                @php $rank++; @endphp
                                <a href="/home?filter_type=monthly&month={{ $currentMonthParam }}&category_id={{ $cat->category_id }}&type=pengeluaran" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="me-2">#{{ $rank }}</strong>
                                        <span>{{ $cat->category?->name ?? 'Lainnya' }}</span>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold">Rp {{ number_format($cat->total,0,',','.') }}</div>
                                        @if($expenseThisMonth > 0)
                                            <div class="small text-muted">{{ round(($cat->total / $expenseThisMonth) * 100,1) }}%</div>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="text-muted">Belum ada pengeluaran untuk bulan ini.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card p-3">
                    <div class="card-body">
                        <h6>Peringkat Pemasukan (Bulan ini)</h6>
                        <div class="list-group mt-2">
                            @php $rankInc = 0; @endphp
                            @forelse($incomeByCategory->sortByDesc('total') as $cat)
                                @php $rankInc++; @endphp
                                <a href="/home?filter_type=monthly&month={{ $currentMonthParam }}&category_id={{ $cat->category_id }}&type=pemasukan" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="me-2">#{{ $rankInc }}</strong>
                                        <span>{{ $cat->category?->name ?? 'Lainnya' }}</span>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold">Rp {{ number_format($cat->total,0,',','.') }}</div>
                                        @if($incomeThisMonth > 0)
                                            <div class="small text-muted">{{ round(($cat->total / $incomeThisMonth) * 100,1) }}%</div>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="text-muted">Belum ada pemasukan untuk bulan ini.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
      
    </div>
@endsection
