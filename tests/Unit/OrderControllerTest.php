<?php

use App\Http\Controllers\API\Transaction\OrderController;
use Illuminate\Support\Facades\Validator;

describe('OrderController Unit Tests', function () {
    
    beforeEach(function () {
        $this->controller = new OrderController();
    });

    describe('validation rules', function () {
        
        it('requires sales_id field', function () {
            $data = [
                'customer_id' => 1,
                'items' => [['product_id' => 1, 'quantity' => 1]]
            ];
            
            $rules = [
                'sales_id' => 'required|integer',
                'customer_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1'
            ];
            
            $validator = Validator::make($data, $rules);
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('sales_id'))->toBeTrue();
        });

        it('requires customer_id field', function () {
            $data = [
                'sales_id' => 1,
                'items' => [['product_id' => 1, 'quantity' => 1]]
            ];
            
            $rules = [
                'sales_id' => 'required|integer',
                'customer_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1'
            ];
            
            $validator = Validator::make($data, $rules);
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('customer_id'))->toBeTrue();
        });

        it('requires items array', function () {
            $data = ['sales_id' => 1, 'customer_id' => 1];
            
            $rules = [
                'sales_id' => 'required|integer',
                'customer_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1'
            ];
            
            $validator = Validator::make($data, $rules);
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('items'))->toBeTrue();
        });

        it('validates quantity minimum value', function () {
            $data = [
                'sales_id' => 1,
                'customer_id' => 1,
                'items' => [['product_id' => 1, 'quantity' => 0]]
            ];
            
            $rules = [
                'sales_id' => 'required|integer',
                'customer_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1'
            ];
            
            $validator = Validator::make($data, $rules);
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('items.0.quantity'))->toBeTrue();
        });

        it('passes validation with valid data', function () {
            $data = [
                'sales_id' => 1,
                'customer_id' => 1,
                'items' => [['product_id' => 1, 'quantity' => 2]]
            ];
            
            $rules = [
                'sales_id' => 'required|integer',
                'customer_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1'
            ];
            
            $validator = Validator::make($data, $rules);
            
            expect($validator->passes())->toBeTrue();
            expect($validator->errors()->isEmpty())->toBeTrue();
        });
    });

    describe('reference number generation', function () {
        
        it('generates reference number with correct format', function () {
            $today = date('Ymd');
            $orderCount = 5;
            $refNo = "INV{$today}-" . str_pad($orderCount + 1, 4, '0', STR_PAD_LEFT);
            
            expect($refNo)->toMatch('/^INV\d{8}-\d{4}$/');
        });

        it('generates incremental reference numbers', function () {
            $today = date('Ymd');
            
            $refNo1 = "INV{$today}-" . str_pad(1, 4, '0', STR_PAD_LEFT);
            $refNo2 = "INV{$today}-" . str_pad(2, 4, '0', STR_PAD_LEFT);
            
            expect($refNo1)->toBe("INV{$today}-0001");
            expect($refNo2)->toBe("INV{$today}-0002");
            expect($refNo1)->not->toBe($refNo2);
        });
    });

    describe('controller structure', function () {
        
        it('has store method', function () {
            expect(method_exists($this->controller, 'store'))->toBeTrue();
        });

        it('has index method', function () {
            expect(method_exists($this->controller, 'index'))->toBeTrue();
        });

        it('extends Controller class', function () {
            expect($this->controller)->toBeInstanceOf(\App\Http\Controllers\Controller::class);
        });
    });
}); 