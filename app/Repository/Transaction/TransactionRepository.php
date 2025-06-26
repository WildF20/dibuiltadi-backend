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
            ->groupBy('x')
            ->map(function($monthItems) {
                return [
                    'target' => $monthItems->sum('target'),
                    'revenue' => $monthItems->sum('revenue'),
                    'income' => $monthItems->sum('income')
                ];
            })
            ->pipe(function($monthlyTotals) {
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

    public function getSalesAchievement($filter)
    {
        try {
            $order = SalesOrder::when($filter['period'] != '', function($query) use ($filter){
                $query->whereYear("created_at", Carbon::parse($filter['period'])->year);
                $query->whereMonth("created_at", Carbon::parse($filter['period'])->month);
            })
            ->with('items')
            ->get()
            ->map(function($item){
                return [
                    'sales' => $item->sales->user->name,
                    'target' => $item->sales->latestTarget->amount,
                    'revenue' => $item->items->sum(function($i){
                        return $i->quantity * $i->selling_price;
                    }),
                ];
            })
            ->groupBy('sales')
            ->map(function($group, $sales) {
                $revenue = $group->sum('revenue');
                $target = $group->first()['target'];
                $percentage = $target > 0 ? ($revenue / $target) * 100 : 0;
                
                return [
                    'sales' => $sales,
                    'revenue' => [
                        'amount' => number_format($revenue, 2, '.', ''),
                        'abbreviation' => $this->formatNumberAbbreviation($revenue)
                    ],
                    'target' => [
                        'amount' => number_format($target, 2, '.', ''),
                        'abbreviation' => $this->formatNumberAbbreviation($target)
                    ],
                    'percentage' => $percentage
                ];
            })
            ->values()
            ->when($filter['isUnderperform'] != null, function($query) use ($filter){
                return $query->when($filter['isUnderperform'] == 'true', function($q){
                    return $q->filter(function($value){
                        return $value['percentage'] < 100;
                    });
                }, function($q){
                    return $q->filter(function($value){
                        return $value['percentage'] >= 100;
                    });
                });
            })
            ->toArray();
        } catch (\Throwable $th) {
            throw $th;
        }

        return $order;
    }

    private function formatNumberAbbreviation($number)
    {
        $number = floatval($number);
        
        if ($number >= 1000000000) {
            $formatted = $number / 1000000000;
            $suffix = 'B';
        } elseif ($number >= 1000000) {
            $formatted = $number / 1000000;
            $suffix = 'M';
        } elseif ($number >= 1000) {
            $formatted = $number / 1000;
            $suffix = 'K';
        } else {
            return number_format($number, 2);
        }
        
        if ($formatted == floor($formatted)) {
            return number_format($formatted, 0) . $suffix;
        } else {
            return number_format($formatted, 2) . $suffix;
        }
    }
}