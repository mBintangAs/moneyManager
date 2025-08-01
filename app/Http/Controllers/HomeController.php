<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $filterType = request()->has('filter_type') ? request('filter_type') : 'daily';
        $query = Transaction::with('category')->where('user_id', $user ? $user->id : null);

        if ($filterType === 'daily') {
            $date = request('date', now()->toDateString());
            $query->whereDate('date', $date);
        } elseif ($filterType === 'monthly') {
            $month = request('month', now()->format('Y-m'));
            $query->whereYear('date', substr($month, 0, 4))
                  ->whereMonth('date', substr($month, 5, 2));
        } elseif ($filterType === 'custom') {
            $start = request('start_date');
            $end = request('end_date');
            if ($start && $end) {
                $query->whereBetween('date', [$start, $end]);
            }
        }

        $transactions = $query->orderBy('date', 'desc')->limit(10)->get();

        $totalSaldo = Transaction::where('user_id', $user?->id)
            ->where('type', 'pemasukan')
            ->sum('amount');
        if ($filterType === 'daily') {
            $date = request('date', now()->toDateString());
            $totalIncome = Transaction::where('user_id', $user ? $user->id : null)
                ->where('type', 'pemasukan')
                ->whereDate('date', $date)
                ->sum('amount');
            $totalExpense = Transaction::where('user_id', $user ? $user->id : null)
                ->where('type', 'pengeluaran')
                ->whereDate('date', $date)
                ->sum('amount');
        } elseif ($filterType === 'monthly') {
            $month = request('month', now()->format('Y-m'));
            $totalIncome = Transaction::where('user_id', $user ? $user->id : null)
                ->where('type', 'pemasukan')
                ->whereYear('date', substr($month, 0, 4))
                ->whereMonth('date', substr($month, 5, 2))
                ->sum('amount');
            $totalExpense = Transaction::where('user_id', $user ? $user->id : null)
                ->where('type', 'pengeluaran')
                ->whereYear('date', substr($month, 0, 4))
                ->whereMonth('date', substr($month, 5, 2))
                ->sum('amount');
        } elseif ($filterType === 'custom') {
            $start = request('start_date');
            $end = request('end_date');
            if ($start && $end) {
                $totalIncome = Transaction::where('user_id', $user ? $user->id : null)
                    ->where('type', 'pemasukan')
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount');
                $totalExpense = Transaction::where('user_id', $user ? $user->id : null)
                    ->where('type', 'pengeluaran')
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount');
            } else {
                $totalIncome = 0;
                $totalExpense = 0;
            }
        }

        return view('home', [
            'transactions' => $transactions,
            'totalSaldo' => $totalSaldo,
            'totalIncome' => $totalIncome,
            'totalExpense' => abs($totalExpense),
        ]);
    }
}
