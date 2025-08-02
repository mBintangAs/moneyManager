<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $categories = Category::where('user_id', $user->id)->get();
        $accounts = Account::where('user_id', $user->id)->get();
        return view('transactions.create', compact('categories', 'accounts'));
    }

    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:pemasukan,pengeluaran',
            'category_id' => 'required',
            'account_id' => 'required',
        ]);

        // Handle kategori baru
        if (!Category::find($request->category_id)) {
            $category = Category::create([
            'name' => $request->category_id,
            'user_id'=>Auth::user()->id
            ]);
            $request->category_id = $category->id;
        }

        // Handle akun baru
        if (!Account::find($request->account_id)) {
          $account = Account::create([
            'name' => $request->account_id,
            'user_id'=>Auth::user()->id
          ]);
          $request->account_id= $account->id;
        }

        // Simpan transaksi
        Transaction::create([
            'user_id' => Auth::id(),
            'account_id' => $request->account_id,
            'date' => $request->date,
            'category_id' => $request->category_id,
            'name' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
        ]);

        return redirect()->route('home')->with('success', 'Transaksi berhasil ditambahkan!');
    }
        public function edit(Transaction $transaction)
    {
        $user = Auth::user();
        $categories = Category::where('user_id', $user->id)->get();
        $accounts = Account::where('user_id', $user->id)->get();
        return view('transactions.edit', compact('transaction', 'categories', 'accounts'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:pemasukan,pengeluaran',
            'category_id' => 'required',
            'account_id' => 'required',
        ]);

        // Handle kategori baru
        if (!Category::find($request->category_id)) {
            $category = Category::create([
                'name' => $request->category_id,
                'user_id'=>Auth::user()->id
            ]);
            $request->category_id = $category->id;
        }

        // Handle akun baru
        if (!Account::find($request->account_id)) {
            $account = Account::create([
                'name' => $request->account_id,
                'user_id'=>Auth::user()->id
            ]);
            $request->account_id= $account->id;
        }

        $transaction->update([
            'account_id' => $request->account_id,
            'date' => $request->date,
            'category_id' => $request->category_id,
            'name' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
        ]);

        return redirect()->route('home')->with('success', 'Transaksi berhasil diupdate!');
    }
}
