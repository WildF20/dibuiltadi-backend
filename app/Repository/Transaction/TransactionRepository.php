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
}