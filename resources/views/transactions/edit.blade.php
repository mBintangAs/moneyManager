@extends('layouts.app')
@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-selection__rendered {
            line-height: 50px !important;
            font-size: 1.1rem !important;
            color: #2c3e50 !important;
        }
        .select2-container .select2-selection--single {
            height: 50px !important;
            border-radius: 1rem !important;
            box-shadow: 0 2px 8px rgba(67,206,162,0.10);
            background: rgba(255,255,255,0.85) !important;
            border: 1px solid #cfdef3 !important;
            font-weight: 500;
        }
        .select2-selection__arrow {
            height: 50px !important;
        }
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default .select2-selection--single:hover {
            border-color: #43cea2 !important;
            box-shadow: 0 0 0 2px #43cea233;
        }
        .select2-dropdown {
            border-radius: 1rem !important;
            box-shadow: 0 4px 16px rgba(67,206,162,0.10);
            background: #f8fafc !important;
        }
        .select2-results__option {
            padding: 12px 18px !important;
            font-size: 1rem !important;
        }
        .select2-results__option--highlighted {
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%) !important;
            color: #fff !important;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5 px-2" style="min-height:100vh; background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4" style="background:rgba(255,255,255,0.85);">
                    <div class="card-header border-0 bg-transparent text-center py-4">
                        <span class="fs-4 fw-bold text-dark"><i class="bi bi-pencil-square me-2"></i>Edit Transaksi</span>
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
                                <label for="description" class="form-label fw-semibold">Nama</label>
                                <select id="description" name="description" class="form-select form-select-lg rounded-3 shadow-sm" style="width:100%" required>
                                    <option value="">Pilih atau tambah nama transaksi</option>
                                    @foreach ($names as $name)
                                        <option value="{{ $name }}" @if(old('description', $transaction->name) == $name) selected @endif>{{ $name }}</option>
                                    @endforeach
                                    @if($transaction->name && !$names->contains($transaction->name))
                                        <option value="{{ $transaction->name }}" selected>{{ $transaction->name }}</option>
                                    @endif
                                </select>
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
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow" style="font-size:1.2rem; background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); border:none;">Simpan Perubahan</button>
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
            $('#description').select2({
                tags: true,
                placeholder: 'Pilih atau tambah nama transaksi',
                
            });
        });
    </script>
@endsection
