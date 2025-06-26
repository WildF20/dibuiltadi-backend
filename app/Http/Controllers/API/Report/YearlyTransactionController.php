<?php

namespace App\Http\Controllers\API\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use App\Models\Sales;
use App\Models\Customer;

use App\Repository\Transaction\TransactionRepository;

class YearlyTransactionController extends Controller
{
    protected $transRepo;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transRepo = $transactionRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [];
        $filter['sales'] = $request->sales_id ?? '';
        $filter['customer'] = $request->customer_id ?? '';

        $sales = Sales::find($filter['sales'])->user->name ?? '';
        $customer = Customer::find($filter['customer'])->name ?? '';

        try {
            $results = $this->transRepo->getYearlySummary($filter);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            $results = [];
        }

        return response()->json([
            'sales' => $sales,
            'customer' => $customer,
            'items' => $results
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
