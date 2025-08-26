@extends('layouts.app')
@section('styles')
    <link href="/select2.min.css" rel="stylesheet" />
    <style>
        .select2-selection__rendered { line-height: 42px !important; color: #222 !important; }
        .select2-container .select2-selection--single { height: 42px !important; border-radius: 6px !important; border:1px solid #e6e6e6 !important; background:#fff !important; }
        .select2-selection__arrow { height: 42px !important; }
        .select2-dropdown { border-radius:6px !important; box-shadow: 0 4px 10px rgba(0,0,0,0.04); }
        .select2-results__option { padding:8px 12px !important; }
        .select2-results__option--highlighted { background:#f1f3f5 !important; color:#111 !important; }
        body { background:#f7f7f7; }
        .card { border:1px solid #eee; box-shadow:none; }
        .card-header { background:transparent; }
        .btn-primary { background:#111827; border:none; }
        .form-control, .form-select { border-radius:6px; }
    </style>
@endsection

@section('content')
    <div class="container py-5 px-2" style="min-height:100vh;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-6">
                <div class="card rounded-3">
                    <div class="card-header text-center py-3">
                        <span class="fs-5 fw-semibold text-dark">Edit Transaksi</span>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('transactions.update', $transaction->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="date" class="form-label fw-semibold">Tanggal</label>
                                <input type="date" class="form-control rounded-3 shadow-sm" id="date" name="date"
                                    value="{{ old('date', $transaction->date) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="account_id" class="form-label fw-semibold">Akun</label>
                                <select class="form-select form-select-lg rounded-3 shadow-sm" id="account_id" name="account_id" required style="width:100%">
                                    <option value="">Pilih Akun</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ $transaction->account_id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="category_id" class="form-label fw-semibold">Kategori</label>
                                <select class="form-select form-select-lg rounded-3 shadow-sm mb-2" id="category_id" name="category_id" style="width:100%">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ $transaction->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Nama</label>
                                <select id="name" name="name" class="form-select form-select-lg rounded-3 shadow-sm" style="width:100%" required>
                                    <option value="">Pilih atau tambah nama transaksi</option>
                                    @foreach ($names as $name)
                                        <option value="{{ $name }}" @if(old('name', $transaction->name) == $name) selected @endif>{{ $name }}</option>
                                    @endforeach
                                    @if($transaction->name && !$names->contains($transaction->name))
                                        <option value="{{ $transaction->name }}" selected>{{ $transaction->name }}</option>
                                    @endif
                                </select>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label fw-semibold">Deskripsi (opsional)</label>
                                <textarea id="description" name="description" class="form-control rounded-3 shadow-sm" rows="2">{{ old('description', $transaction->description) }}</textarea>
                                @error('description')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="type" class="form-label fw-semibold">Tipe Transaksi</label>
                                <select class="form-select rounded-3 shadow-sm" id="type" name="type" required>
                                    <option value="pengeluaran" {{ $transaction->type == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                                    <option value="pemasukan" {{ $transaction->type == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label fw-semibold">Jumlah</label>
                                <input type="number" class="form-control rounded-3 shadow-sm" id="amount" name="amount"
                                    value="{{ old('amount', $transaction->amount) }}" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 rounded">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="/jquery.min.js"></script>
    <script src="/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#account_id').select2({
                tags: true,
                placeholder: 'Pilih atau tambah akun',
                width: '100%'
            });
            $('#category_id').select2({
                tags: true,
                placeholder: 'Pilih atau tambah kategori',
                width: '100%'
            });
          
            // map #name to select2 behavior
            $('#name').select2({
                tags: true,
                placeholder: 'Pilih atau tambah nama transaksi',
                width: '100%'
            });
        });
    </script>
@endsection
