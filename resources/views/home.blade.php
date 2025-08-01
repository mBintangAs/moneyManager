@extends('layouts.app')

@section('content')
<div class="container mt-3 px-2">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
        }
        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #2c3e50;
        }
        .card-balance {
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(67,206,162,0.15);
        }
        .card-income {
            background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(78,84,200,0.15);
        }
        .card-expense {
            background: linear-gradient(135deg, #ff5858 0%, #f09819 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(255,88,88,0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
            border: none;
        }
        .table thead th {
            background: #f8fafc;
            color: #185a9d;
            font-weight: 600;
        }
    </style>
    <div class="row">
        <div class="col-12 px-4">
           
            <form method="GET" class="mb-3 row gx-2 gy-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="filter_type" class="form-label">Tampilkan</label>
                    <select name="filter_type" id="filter_type" class="form-select" onchange="toggleDateInputs()">
                        <option value="daily" {{ request('filter_type', 'daily') == 'daily' ? 'selected' : '' }}>Hari ini</option>
                        <option value="monthly" {{ request('filter_type') == 'monthly' ? 'selected' : '' }}>Bulan ini</option>
                        <option value="range" {{ request('filter_type') == 'range' ? 'selected' : '' }}>Rentang tanggal</option>
                    </select>
                </div>
                <div id="range-inputs" class="col-12 col-md-5" style="display: {{ request('filter_type') == 'range' ? 'block' : 'none' }};">
                    <label for="start_date" class="form-label">Dari</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                    <label for="end_date" class="form-label">Sampai</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
            <script>
            function toggleDateInputs() {
                var filterType = document.getElementById('filter_type').value;
                document.getElementById('range-inputs').style.display = filterType === 'range' ? 'block' : 'none';
            }
            </script>
            <div class="mb-3">
                <a href="{{ route('transactions.create') }}" class="btn w-100 btn-lg btn-primary shadow">+ Tambah Transaksi</a>
            </div>
            <div class="row g-2">
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <div class="card card-balance">
                        <div class="card-body text-center">
                            <h5 class="card-title">Sisa Saldo</h5>
                            <p class="card-text display-6 fw-bold">Rp {{ number_format($totalSaldo ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <div class="card card-income">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                @if(request('filter_type', 'daily') == 'daily')
                                    Pemasukan Hari Ini
                                @elseif(request('filter_type') == 'monthly')
                                    Pemasukan Bulan Ini
                                @elseif(request('filter_type') == 'custom')
                                    Pemasukan (Custom)
                                @endif
                            </h5>
                            <p class="card-text display-6 fw-bold">Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <div class="card card-expense">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                @if(request('filter_type', 'daily') == 'daily')
                                    Pengeluaran Hari Ini
                                @elseif(request('filter_type') == 'monthly')
                                    Pengeluaran Bulan Ini
                                @elseif(request('filter_type') == 'custom')
                                    Pengeluaran (Custom)
                                @endif
                            </h5>
                            <p class="card-text display-6 fw-bold">Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}</p>
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
                            <div class="rounded-4 shadow-sm px-3 py-2 d-flex flex-column h-100 gap-1" style="background: linear-gradient(135deg, #f8fafc 60%, #e0eafc 100%); border-left: 6px solid {{ $transaction->type == 'pengeluaran' ? '#ff5858' : '#43cea2' }};">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill" style="background:{{ $transaction->type == 'pengeluaran' ? '#ff5858' : '#43cea2' }};color:#fff;min-width:80px;">{{ $transaction->date }}</span>
                                            <span class="fw-bold text-dark">{{ $transaction->category->name ?? '-' }}</span>
                                        </div>
                                        <span class="fs-5 fw-bold {{ $transaction->type == 'pengeluaran' ? 'text-danger' : 'text-success' }}">
                                            {{ $transaction->type == 'pengeluaran' ? '-' : '+' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </span>
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
