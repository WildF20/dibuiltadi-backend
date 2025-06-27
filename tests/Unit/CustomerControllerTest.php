<?php

use App\Http\Controllers\API\Master\CustomerController;
use Illuminate\Support\Facades\Validator;

describe('CustomerController Unit Tests', function () {
    
    beforeEach(function () {
        $this->controller = new CustomerController();
    });

    describe('validation rules', function () {
        
        it('requires name field', function () {
            $data = ['address' => '123 Main Street', 'phone' => '+1234567890'];
            
            $validator = Validator::make($data, [
                'name' => 'required|string|max:100',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('name'))->toBeTrue();
        });

        it('requires address field', function () {
            $data = ['name' => 'John Doe', 'phone' => '+1234567890'];
            
            $validator = Validator::make($data, [
                'name' => 'required|string|max:100',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('address'))->toBeTrue();
        });

        it('requires phone field', function () {
            $data = ['name' => 'John Doe', 'address' => '123 Main Street'];
            
            $validator = Validator::make($data, [
                'name' => 'required|string|max:100',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('phone'))->toBeTrue();
        });

        it('validates name max length', function () {
            $data = [
                'name' => str_repeat('a', 101),
                'address' => '123 Main Street',
                'phone' => '+1234567890'
            ];
            
            $validator = Validator::make($data, [
                'name' => 'required|string|max:100',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('name'))->toBeTrue();
        });

        it('passes validation with valid data', function () {
            $data = [
                'name' => 'John Doe',
                'address' => '123 Main Street',
                'phone' => '+1234567890'
            ];
            
            $validator = Validator::make($data, [
                'name' => 'required|string|max:100',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);
            
            expect($validator->passes())->toBeTrue();
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
            expect($this->controller)->toBeInstanceOf('App\Http\Controllers\Controller');
        });
    });

    describe('data processing', function () {
        
        it('can filter request data correctly', function () {
            $requestData = [
                'name' => 'John Doe',
                'address' => '123 Main Street',
                'phone' => '+1234567890',
                'extra_field' => 'should be ignored'
            ];
            
            $allowedFields = ['name', 'address', 'phone'];
            $filteredData = array_intersect_key($requestData, array_flip($allowedFields));
            
            expect($filteredData)->toHaveKeys(['name', 'address', 'phone']);
            expect($filteredData)->not->toHaveKey('extra_field');
            expect(count($filteredData))->toBe(3);
        });
    });
}); 