<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $filterType = request()->has('filter_type') ? request('filter_type') : 'daily';
        $query = Transaction::with('category')->where('user_id', $user ? $user->id : null);

        // optional filters from querystring (category, type)
        if (request()->has('category_id') && request('category_id') != '') {
            $query->where('category_id', request('category_id'));
        }
        if (request()->has('type') && in_array(request('type'), ['pemasukan','pengeluaran'])) {
            $query->where('type', request('type'));
        }

        if ($filterType === 'daily') {
            $date = request('date', now()->toDateString());
            $query->whereDate('date', $date);
        } elseif ($filterType === 'monthly') {
            $month = request('month', now()->format('Y-m'));
            $query->whereYear('date', substr($month, 0, 4))
                ->whereMonth('date', substr($month, 5, 2));
        } elseif ($filterType === 'range') {
            $start = request('start_date');
            $end = request('end_date') ?: $start;

            if ($start && $end) {
                $query->whereBetween('date', [$start, $end]);
            }
        }

        $transactions = $query->orderBy('date', 'desc')->get();
        $totalSaldo = Transaction::where('user_id', $user?->id)
            ->where('type', 'pemasukan')
            ->sum('amount')
            - Transaction::where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
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
        } elseif ($filterType === 'range') {
            $start = request('start_date');
            $end = request('end_date') ?: $start;

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
        if ($filterType == 'daily') {
            $expenseByCategory = Transaction::with('category')
                ->where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
                ->whereDate('date', now()->toDateString())
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')
                ->get();
            $expenseByName = Transaction::where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
                ->whereDate('date', now()->toDateString())
                ->selectRaw('name, SUM(amount) as total')
                ->groupBy('name')
                ->get();
        } elseif ($filterType == 'monthly') {
            $expenseByCategory = Transaction::with('category')
                ->where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')
                ->get();
            $expenseByName = Transaction::where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
                ->whereMonth('date', [request('start_date'), request('end_date')])
                ->selectRaw('name, SUM(amount) as total')
                ->groupBy('name')
                ->get();
        } elseif ($filterType == 'range') {
            $expenseByCategory = Transaction::with('category')
                ->where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')
                ->get();

            $expenseByName = Transaction::where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
                ->whereBetween('date', [request('start_date'), request('end_date')])
                ->selectRaw('name, SUM(amount) as total')
                ->groupBy('name')
                ->get();
        }
        // Analisis pengeluaran group by kategori
        // Compute budget alerts for the selected period (monthly only for now)
        $budgetAlerts = [];
        if ($filterType === 'monthly') {
            $month = request('month', now()->format('Y-m'));
            $year = substr($month, 0, 4);
            $mon = substr($month, 5, 2);

            $categoryTotals = Transaction::with('category')
                ->where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
                ->whereYear('date', $year)
                ->whereMonth('date', $mon)
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')
                ->get();

            foreach ($categoryTotals as $c) {
                $budget = $c->category?->budget;
                if ($budget && $budget > 0) {
                    $pct = round(($c->total / $budget) * 100, 1);
                    $budgetAlerts[] = [
                        'category_id' => $c->category_id,
                        'category_name' => $c->category?->name ?? 'Lainnya',
                        'spent' => (float)$c->total,
                        'budget' => (float)$budget,
                        'percent' => $pct,
                    ];
                }
            }
        }

        // Analisis pengeluaran group by nama transaksi
        // Average daily expense for the selected period
        $avgDailyExpense = 0;
        if ($filterType === 'daily') {
            $avgDailyExpense = $totalExpense;
        } elseif ($filterType === 'monthly') {
            $month = request('month', now()->format('Y-m'));
            $year = substr($month, 0, 4);
            $mon = substr($month, 5, 2);
            $daysInMonth = Carbon::createFromDate($year, $mon, 1)->daysInMonth;
            $avgDailyExpense = $daysInMonth > 0 ? ($totalExpense / $daysInMonth) : 0;
        } elseif ($filterType === 'range') {
            $start = request('start_date');
            $end = request('end_date') ?: $start;
            if ($start && $end) {
                $days = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
                $avgDailyExpense = $days > 0 ? ($totalExpense / $days) : 0;
            }
        }

        return view('home', [
            'transactions' => $transactions,
            'totalSaldo' => $totalSaldo,
            'totalIncome' => $totalIncome,
            'totalExpense' => abs($totalExpense),
            'expenseByCategory' => $expenseByCategory,
            'expenseByName' => $expenseByName,
            'budgetAlerts' => $budgetAlerts,
            'avgDailyExpense' => $avgDailyExpense,
        ]);
    }

    public function analytics()
    {
        $user = Auth::user();
        $filterType = request()->has('filter_type') ? request('filter_type') : 'monthly';

        // Determine period based on filter
        if ($filterType === 'daily') {
            $date = request('date', now()->toDateString());
            $currentPeriod = Carbon::parse($date);
            $previousPeriod = $currentPeriod->copy()->subDay();
        } elseif ($filterType === 'monthly') {
            $month = request('month', now()->format('Y-m'));
            $currentPeriod = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $previousPeriod = $currentPeriod->copy()->subMonth();
        } else { // range
            $start = request('start_date');
            $end = request('end_date') ?: $start;
            if ($start && $end) {
                $currentPeriod = ['start' => $start, 'end' => $end];
                $days = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
                $previousStart = Carbon::parse($start)->subDays($days)->toDateString();
                $previousEnd = Carbon::parse($start)->subDay()->toDateString();
                $previousPeriod = ['start' => $previousStart, 'end' => $previousEnd];
            } else {
                $currentPeriod = Carbon::now()->startOfMonth();
                $previousPeriod = $currentPeriod->copy()->subMonth();
            }
        }

        // Current period income/expense
        $incomeQuery = Transaction::where('user_id', $user?->id)->where('type', 'pemasukan');
        $expenseQuery = Transaction::where('user_id', $user?->id)->where('type', 'pengeluaran');

        if ($filterType === 'daily') {
            $incomeThisMonth = $incomeQuery->whereDate('date', $currentPeriod->toDateString())->sum('amount');
            $expenseThisMonth = $expenseQuery->whereDate('date', $currentPeriod->toDateString())->sum('amount');
        } elseif ($filterType === 'monthly') {
            $incomeThisMonth = $incomeQuery->whereYear('date', $currentPeriod->year)
                ->whereMonth('date', $currentPeriod->month)->sum('amount');
            $expenseThisMonth = $expenseQuery->whereYear('date', $currentPeriod->year)
                ->whereMonth('date', $currentPeriod->month)->sum('amount');
        } else {
            $incomeThisMonth = is_array($currentPeriod) ? 
                $incomeQuery->whereBetween('date', [$currentPeriod['start'], $currentPeriod['end']])->sum('amount') : 0;
            $expenseThisMonth = is_array($currentPeriod) ? 
                $expenseQuery->whereBetween('date', [$currentPeriod['start'], $currentPeriod['end']])->sum('amount') : 0;
        }

        // Previous period for comparison
        $incomeLastQuery = Transaction::where('user_id', $user?->id)->where('type', 'pemasukan');
        $expenseLastQuery = Transaction::where('user_id', $user?->id)->where('type', 'pengeluaran');

        if ($filterType === 'daily') {
            $incomeLastMonth = $incomeLastQuery->whereDate('date', $previousPeriod->toDateString())->sum('amount');
            $expenseLastMonth = $expenseLastQuery->whereDate('date', $previousPeriod->toDateString())->sum('amount');
        } elseif ($filterType === 'monthly') {
            $incomeLastMonth = $incomeLastQuery->whereYear('date', $previousPeriod->year)
                ->whereMonth('date', $previousPeriod->month)->sum('amount');
            $expenseLastMonth = $expenseLastQuery->whereYear('date', $previousPeriod->year)
                ->whereMonth('date', $previousPeriod->month)->sum('amount');
        } else {
            $incomeLastMonth = is_array($previousPeriod) ? 
                $incomeLastQuery->whereBetween('date', [$previousPeriod['start'], $previousPeriod['end']])->sum('amount') : 0;
            $expenseLastMonth = is_array($previousPeriod) ? 
                $expenseLastQuery->whereBetween('date', [$previousPeriod['start'], $previousPeriod['end']])->sum('amount') : 0;
        }

        $netBalance = $incomeThisMonth - $expenseThisMonth;

        // Monthly trend for last 12 months (keep this unchanged for consistency)
        $months = [];
        $incomeSeries = [];
        $expenseSeries = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $label = $m->format('Y-m');
            $months[] = $m->format('M Y');

            $incomeSeries[] = Transaction::where('user_id', $user?->id)
                ->where('type', 'pemasukan')
                ->whereYear('date', $m->year)
                ->whereMonth('date', $m->month)
                ->sum('amount');

            $expenseSeries[] = Transaction::where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
                ->whereYear('date', $m->year)
                ->whereMonth('date', $m->month)
                ->sum('amount');
        }

        // Expense/Income by category for current period
        $expenseCategoryQuery = Transaction::with('category')
            ->where('user_id', $user?->id)
            ->where('type', 'pengeluaran');
        
        $incomeCategoryQuery = Transaction::with('category')
            ->where('user_id', $user?->id)
            ->where('type', 'pemasukan');

        if ($filterType === 'daily') {
            $expenseByCategory = $expenseCategoryQuery->whereDate('date', $currentPeriod->toDateString())
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')->get();
            $incomeByCategory = $incomeCategoryQuery->whereDate('date', $currentPeriod->toDateString())
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')->get();
        } elseif ($filterType === 'monthly') {
            $expenseByCategory = $expenseCategoryQuery->whereYear('date', $currentPeriod->year)
                ->whereMonth('date', $currentPeriod->month)
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')->get();
            $incomeByCategory = $incomeCategoryQuery->whereYear('date', $currentPeriod->year)
                ->whereMonth('date', $currentPeriod->month)
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')->get();
        } else {
            if (is_array($currentPeriod)) {
                $expenseByCategory = $expenseCategoryQuery->whereBetween('date', [$currentPeriod['start'], $currentPeriod['end']])
                    ->selectRaw('category_id, SUM(amount) as total')
                    ->groupBy('category_id')->get();
                $incomeByCategory = $incomeCategoryQuery->whereBetween('date', [$currentPeriod['start'], $currentPeriod['end']])
                    ->selectRaw('category_id, SUM(amount) as total')
                    ->groupBy('category_id')->get();
            } else {
                $expenseByCategory = collect();
                $incomeByCategory = collect();
            }
        }

    $totalExpenseForCategories = $expenseByCategory->sum('total');
        $topCategory = $expenseByCategory->sortByDesc('total')->first();
        $topCategoryName = $topCategory?->category?->name ?? null;
        $topCategoryPercent = $totalExpenseForCategories > 0 && $topCategory ? round(($topCategory->total / $totalExpenseForCategories) * 100, 1) : 0;

        // percent change vs last month
        $expenseChangePercent = $expenseLastMonth > 0 ? round((($expenseThisMonth - $expenseLastMonth) / max(1, $expenseLastMonth)) * 100, 1) : null;
        $incomeChangePercent = $incomeLastMonth > 0 ? round((($incomeThisMonth - $incomeLastMonth) / max(1, $incomeLastMonth)) * 100, 1) : null;

        // weekend expense share for current period
        if ($filterType === 'monthly' && !is_array($currentPeriod)) {
            $monthStart = $currentPeriod->copy();
            $monthEnd = $currentPeriod->copy()->endOfMonth();
            $monthExpenses = Transaction::where('user_id', $user?->id)
                ->where('type', 'pengeluaran')
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->get();
            $weekendExpense = $monthExpenses->filter(function($t){
                return Carbon::parse($t->date)->isWeekend();
            })->sum('amount');
            $weekendExpensePercent = $expenseThisMonth > 0 ? round(($weekendExpense / $expenseThisMonth) * 100, 1) : 0;
        } else {
            $weekendExpensePercent = 0;
        }

        $topCategorySpent = $topCategory?->total ? (float)$topCategory->total : 0;
        $potentialSavingIf10pct = round($topCategorySpent * 0.10);
        $netBalanceNegative = $netBalance < 0;

        // average daily expense since start of this month (up to today)
        if ($filterType === 'monthly' && !is_array($currentPeriod)) {
            $daysElapsed = now()->day;
            $avgDailySinceMonth = $daysElapsed > 0 ? ($expenseThisMonth / $daysElapsed) : 0;
        } elseif ($filterType === 'daily') {
            $avgDailySinceMonth = $expenseThisMonth;
        } else {
            $days = is_array($currentPeriod) ? Carbon::parse($currentPeriod['start'])->diffInDays(Carbon::parse($currentPeriod['end'])) + 1 : 1;
            $avgDailySinceMonth = $days > 0 ? ($expenseThisMonth / $days) : 0;
        }

        // Current month param for drill-down links
        if ($filterType === 'monthly' && !is_array($currentPeriod)) {
            $currentMonthParam = $currentPeriod->format('Y-m');
        } else {
            $currentMonthParam = now()->format('Y-m');
        }

        return view('analytics.index', [
            'incomeThisMonth' => $incomeThisMonth,
            'expenseThisMonth' => $expenseThisMonth,
            'incomeLastMonth' => $incomeLastMonth,
            'expenseLastMonth' => $expenseLastMonth,
            'netBalance' => $netBalance,
            'months' => $months,
            'incomeSeries' => $incomeSeries,
            'expenseSeries' => $expenseSeries,
            'expenseByCategory' => $expenseByCategory,
            'incomeByCategory' => $incomeByCategory,
            // compute simple budget alerts for this month as well
            'budgetAlerts' => (function() use ($expenseByCategory) {
                $alerts = [];
                foreach ($expenseByCategory as $c) {
                    $budget = $c->category?->budget;
                    if ($budget && $budget > 0) {
                        $pct = round(($c->total / $budget) * 100, 1);
                        $alerts[] = [
                            'category_id' => $c->category_id,
                            'category_name' => $c->category?->name ?? 'Lainnya',
                            'spent' => (float)$c->total,
                            'budget' => (float)$budget,
                            'percent' => $pct,
                        ];
                    }
                }
                return $alerts;
            })(),
            'currentMonthParam' => $currentMonthParam,
            'topCategoryName' => $topCategoryName,
            'topCategoryPercent' => $topCategoryPercent,
            'expenseChangePercent' => $expenseChangePercent,
            'incomeChangePercent' => $incomeChangePercent,
            'weekendExpensePercent' => $weekendExpensePercent,
            'topCategorySpent' => $topCategorySpent,
            'potentialSavingIf10pct' => $potentialSavingIf10pct,
            'netBalanceNegative' => $netBalanceNegative,
            'avgDailySinceMonth' => $avgDailySinceMonth,
        ]);
    }
}
