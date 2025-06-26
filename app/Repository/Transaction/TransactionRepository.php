<?php

namespace App\Repository\Transaction;

use App\Models\SalesOrder;
use Carbon\Carbon;

class TransactionRepository
{
    public function getYearlySummary($filter)
    {
        try {
            $order = SalesOrder::when($filter['sales'] != '', function($query) use ($filter){
                $query->where('sales_id', $filter['sales']);
            })->when($filter['customer'] != '', function($query) use ($filter){
                $query->where('customer_id', $filter['customer']);
            })
            ->where('created_at', '>=', Carbon::now()->subYears(2)->startOfYear()->format('Y-m-d'))
            ->with('items')
            ->get()
            ->map(function($item){
                return [
                    'name' => Carbon::parse($item->created_at)->format('Y'),
                    'x' => Carbon::parse($item->created_at)->format('M'),
                    'y' => $item->items->sum(function($i){
                        return $i->quantity * $i->selling_price;
                    })
                ];
            })
            ->groupBy('name') 
            ->map(function($yearItems, $year) {
                $allMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                             'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                
                $monthlyData = $yearItems
                    ->groupBy('x')
                    ->map(function($monthItems) {
                        return $monthItems->sum('y');
                    });
                
                $data = collect($allMonths)->map(function($month) use ($monthlyData) {
                    return [
                        'x' => $month,
                        'y' => number_format($monthlyData->get($month, 0), 2, '.', '')
                    ];
                });
                
                return [
                    'name' => (int) $year,
                    'data' => $data->values()->toArray()
                ];
            })
            ->sortBy('name')
            ->values();

        } catch (\Throwable $th) {
            throw $th;
        }

        return $order;
    }

    public function getTargetRevenue($filter)
    {
        try {
            $order = SalesOrder::when($filter['sales'] != '', function($query) use ($filter){
                $query->where('sales_id', $filter['sales']);
            })->where('created_at', '>=', Carbon::now()->startOfYear()->format('Y-m-d'))
            ->with('items')
            ->get()
            ->map(function($item){
                return [
                    'x' => Carbon::parse($item->created_at)->format('M'),
                    'target' => $item->sales->latestTarget->amount,
                    'revenue' => $item->items->sum(function($i){
                        return $i->quantity * $i->selling_price;
                    }),
                    'income' => $item->items->sum(function($i){
                        return $i->quantity * ($i->selling_price - $i->production_price);
                    })
                ];
            })
            ->groupBy('x') // Group by month
            ->map(function($monthItems) {
                // Sum all values for each month
                return [
                    'target' => $monthItems->sum('target'),
                    'revenue' => $monthItems->sum('revenue'),
                    'income' => $monthItems->sum('income')
                ];
            })
            ->pipe(function($monthlyTotals) {
                // Transform to chart format
                $allMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                              'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                
                return collect(['Target' => 'target', 'Revenue' => 'revenue', 'Income' => 'income'])
                    ->map(function($field, $seriesName) use ($allMonths, $monthlyTotals) {
                        return [
                            'name' => $seriesName,
                            'data' => collect($allMonths)->map(function($month) use ($monthlyTotals, $field) {
                                $amount = $monthlyTotals->get($month)[$field] ?? 0;
                                return [
                                    'x' => $month,
                                    'y' => number_format($amount, 2, '.', '')
                                ];
                            })->toArray()
                        ];
                    })
                    ->values()
                    ->toArray();
            });
        } catch (\Throwable $th) {
            throw $th;
        }

        return $order;
    }
}