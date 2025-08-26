@extends('layouts.app')

@section('content')
<div class="container-fluid mt-3 px-2">
    <style>
        body { background: #fafafa; }
        .dashboard-title { font-size:1.6rem; font-weight:600; color:#111827; }
        .card-balance, .card-income, .card-expense { background:#ffffff; color:#111827; border:1px solid #eef2f7; box-shadow:none; }
        .btn-primary { background:#111827; border:none; }
        .table thead th { background:#fff; color:#111827; font-weight:600; }
        .badge-neutral { background:#f1f3f5; color:#111827; }
        .tx-card { background:#ffffff; border:1px solid #eef2f7; border-left:6px solid transparent; }
    </style>
    <div class="row">
        <div class="col-12 px-2 px-md-4">
           
            <div class="mb-3">
                @if(!empty($budgetAlerts))
                    <div class="mb-2">
                        <div class="alert alert-warning p-3" role="alert" style="background:#fff4e6;border:1px solid #ffedd5;color:#7c2d12;">
                            <strong>Perhatian anggaran:</strong>
                            <div class="mt-1">
                                @foreach($budgetAlerts as $alert)
                                    @if($alert['percent'] >= 100)
                                        <div>Anda telah melebihi anggaran untuk <strong>{{ $alert['category_name'] }}</strong> ({{ $alert['percent'] }}% dari anggaran).</div>
                                    @elseif($alert['percent'] >= 80)
                                        <div>Anda sudah menggunakan <strong>{{ $alert['percent'] }}%</strong> dari anggaran <strong>{{ $alert['category_name'] }}</strong> — pertimbangkan untuk mengurangi pengeluaran di kategori ini.</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                <div class="d-flex flex-column flex-md-row gap-3 align-items-start">
                    <form method="GET" class="row gap-2 align-items-end flex-grow-1 p-3" style="border:1px solid #eef2f7;border-radius:8px;" aria-label="Filter transaksi">
                        <div class="w-100 d-flex flex-column">
                            <label for="filter_type" class="form-label small mb-1">Tampilkan</label>
                            <select name="filter_type" id="filter_type" class="form-select form-select-sm" onchange="toggleDateInputs()" aria-label="Tipe filter">
                                <option value="daily" {{ request('filter_type', 'daily') == 'daily' ? 'selected' : '' }}>Hari ini</option>
                                <option value="monthly" {{ request('filter_type') == 'monthly' ? 'selected' : '' }}>Bulan ini</option>
                                <option value="range" {{ request('filter_type') == 'range' ? 'selected' : '' }}>Rentang tanggal</option>
                            </select>
                        </div>
                        <div id="range-inputs" class="w-100 d-flex gap-2 align-items-end" style="display: {{ request('filter_type') == 'range' ? 'flex' : 'none' }};">
                            <div class="d-flex flex-column">
                                <label for="start_date" class="form-label small mb-1">Dari</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" aria-label="Tanggal mulai">
                            </div>
                            <div class="d-flex flex-column">
                                <label for="end_date" class="form-label small mb-1">Sampai</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" aria-label="Tanggal selesai">
                            </div>
                        </div>
                        <div class="d-flex align-items-end">
                            <button type="submit" class="w-100 btn btn-outline-secondary btn-sm" aria-label="Terapkan filter">Filter</button>
                        </div>
                    </form>

                    <div class="d-flex w-100 align-items-center">
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-lg w-100 d-md-inline-block" aria-label="Tambah Transaksi">+ Tambah Transaksi</a>
                    </div>
                </div>
            </div>
            <script>
            function toggleDateInputs() {
                var filterType = document.getElementById('filter_type').value;
                document.getElementById('range-inputs').style.display = filterType === 'range' ? 'flex' : 'none';
            }
            </script>
            <div class="row g-2">
                <div class="col-12 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 rounded-4 fw-bold"><i class="bi bi-pie-chart me-2"></i>Pengeluaran per Kategori</div>
                        <div class="card-body p-2">
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($expenseByCategory as $cat)
                                    <div class="flex-fill min-w-0" style="min-width:0;;">
                                        <div class="rounded-3 px-3 py-2 mb-1 d-flex flex-column align-items-start" style="background:#fff; border:1px solid #f1f3f5;">
                                            <span class="fw-semibold text-dark small mb-1"><i class="bi bi-tag me-1"></i>{{ $cat->category->name ?? '-' }}</span>
                                            <span class="fw-bold text-danger">Rp {{ number_format($cat->total, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">-</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 rounded-4 fw-bold"><i class="bi bi-list-ol me-2"></i>Pengeluaran per Nama Transaksi</div>
                        <div class="card-body p-2">
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($expenseByName as $row)
                                    <div class="flex-fill min-w-0" style="min-width:0;">
                                        <div class="rounded-3 px-3 py-2 mb-1 d-flex flex-column align-items-start" style="background:#fff; border:1px solid #f1f3f5;">
                                            <span class="fw-semibold text-dark small mb-1"><i class="bi bi-receipt me-1"></i>{{ $row->name }}</span>
                                            <span class="fw-bold text-danger">Rp {{ number_format($row->total, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">-</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="card-title">Sisa Saldo</h6>
                            <p class="card-text h4 fw-semibold">Rp {{ number_format($totalSaldo ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="card-title">
                                @if(request('filter_type', 'daily') == 'daily') Pemasukan Hari Ini
                                @elseif(request('filter_type') == 'monthly') Pemasukan Bulan Ini
                                @else Pemasukan (Custom)
                                @endif
                            </h6>
                            <p class="card-text h4 fw-semibold">Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
             
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="card-title">
                                @if(request('filter_type', 'daily') == 'daily') Pengeluaran Hari Ini
                                @elseif(request('filter_type') == 'monthly') Pengeluaran Bulan Ini
                                @else Pengeluaran (Custom)
                                @endif
                            </h6>
                            <p class="card-text h4 fw-semibold">Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3 border-0 bg-transparent">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-0 shadow-sm rounded-3 mb-2" style="background:rgba(255,255,255,0.7);">
                    <span class="fs-5 fw-bold text-dark"><i class="bi bi-clock-history me-2"></i>Transaksi Terbaru</span>
                </div>
                <div class="card-body p-2">
                    <div class="d-flex flex-column gap-3">
                            @forelse($transactions ?? [] as $transaction)
                            <div class="rounded-3 px-3 py-2 d-flex flex-column h-100 gap-1 tx-card" style="border-left:6px solid {{ $transaction->type == 'pengeluaran' ? '#ef4444' : '#111827' }};">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-neutral rounded-pill" style="min-width:80px;">{{ $transaction->date }}</span>
                                            <span class="fw-bold text-dark">{{ $transaction->category->name ?? '-' }}</span>
                                        </div>
                                        <span class="fs-5 fw-bold {{ $transaction->type == 'pengeluaran' ? 'text-danger' : 'text-success' }}">
                                            {{ $transaction->type == 'pengeluaran' ? '-' : '+' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="d-flex  align-items-center">
                                        <span class="text-muted small">{{ $transaction->name }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <i class="bi bi-wallet2 text-secondary"></i>
                                        <span class="fw-semibold text-primary">
                                            {{ $transaction->account->name ?? 'Belum punya akun' }}
                                        </span>
                                    </div>
                                    <div class="text-muted small ms-4">{{ $transaction->description }}</div>
                                </div>
                                <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 align-self-end mt-2"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">Belum ada transaksi.</div>
                        @endforelse
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection
