<?php

namespace App\Http\Controllers\API\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Product;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $validator = Validator::make($request->all(), [
            'sales_id' => 'required|exists:sales,id',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        $transaction = true;

        // Generate reference number from any resource here
        $reference_no = 'INV' . date('Ymd') . '-' . str_pad(SalesOrder::count() + 1, 4, '0', STR_PAD_LEFT);

        try {
            $salesOrder = SalesOrder::create([
                'reference_no' => $reference_no,
                'sales_id' => $request->sales_id,
                'customer_id' => $request->customer_id
            ]);
        } catch (\Exception $e) {
            $transaction = false;
            Log::error('Failed to create sales order: ' . $e->getMessage());
        }

        $order_id = $salesOrder->id;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                $transaction = false;
                return response()->json(['error' => 'Product not found'], 404);
            }

            try {
                $salesOrderItem = SalesOrderItem::create([
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'production_price' => $product->production_price,
                    'selling_price' => $product->selling_price,
                ]);
            } catch (\Exception $e) {
                $transaction = false;
                Log::error('Failed to create sales order item: ' . $e->getMessage());
            }
        }

        if ($transaction) {
            DB::commit();
            return response()->json(['message' => 'Sales order created successfully'], 201);
        } else {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create sales order'], 500);
        }
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
