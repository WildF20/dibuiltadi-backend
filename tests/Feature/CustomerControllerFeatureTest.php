<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Customer API Feature Tests', function () {

    describe('POST /api/mst-customer', function () {
        
        it('creates a customer with valid data', function () {
            // Mock external phone validation API
            Http::fake([
                'phonevalidation.abstractapi.com/*' => Http::response(['valid' => true], 200)
            ]);
            
            $customerData = [
                'name' => 'John Doe',
                'address' => '123 Main Street, New York, NY 10001',
                'phone' => '+1234567890'
            ];
            
            $response = $this->postJson('/api/mst-customer', $customerData);
            
            $response->assertStatus(201)
                    ->assertJsonStructure([
                        'id',
                        'name',
                        'address',
                        'phone',
                        'created_at',
                        'updated_at'
                    ])
                    ->assertJson([
                        'name' => 'John Doe',
                        'address' => '123 Main Street, New York, NY 10001',
                        'phone' => '+1234567890'
                    ]);
            
            // Verify the customer was actually created in the database
            $this->assertDatabaseHas('customers', [
                'name' => 'John Doe',
                'address' => '123 Main Street, New York, NY 10001',
                'phone' => '+1234567890'
            ]);
        });
        
        it('returns validation errors for missing fields', function () {
            $response = $this->postJson('/api/mst-customer', []);
            
            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['name', 'address', 'phone']);
        });
        
        it('returns validation error for invalid phone number', function () {
            // Mock the external phone validation API to return invalid
            Http::fake([
                'phonevalidation.abstractapi.com/*' => Http::response(['valid' => false], 200)
            ]);
            
            $customerData = [
                'name' => 'John Doe',
                'address' => '123 Main Street, New York, NY 10001',
                'phone' => 'invalid-phone'
            ];
            
            $response = $this->postJson('/api/mst-customer', $customerData);
            
            $response->assertStatus(422)
                    ->assertJson([
                        'error' => 'Phone number is not valid'
                    ]);
            
            // Verify no customer was created
            $this->assertDatabaseMissing('customers', [
                'name' => 'John Doe'
            ]);
        });
        
        it('validates field length constraints', function () {
            $customerData = [
                'name' => str_repeat('a', 101), // Too long
                'address' => str_repeat('b', 256), // Too long
                'phone' => str_repeat('1', 21) // Too long
            ];
            
            $response = $this->postJson('/api/mst-customer', $customerData);
            
            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['name', 'address', 'phone']);
        });

        it('handles external API failure gracefully', function () {
            // Mock API to throw an exception
            Http::fake([
                'phonevalidation.abstractapi.com/*' => function () {
                    throw new \Exception('API is down');
                }
            ]);
            
            $customerData = [
                'name' => 'John Doe',
                'address' => '123 Main Street, New York, NY 10001',
                'phone' => '+1234567890'
            ];
            
            // Depending on how your controller handles API failures,
            // you might expect a 500 error or fallback behavior
            $response = $this->postJson('/api/mst-customer', $customerData);
            
            // Adjust this expectation based on your error handling strategy
            expect($response->getStatusCode())->toBeIn([422, 500]);
        });
    });

    describe('GET /api/mst-customer', function () {
        
        it('returns empty list when no customers exist', function () {
            $response = $this->getJson('/api/mst-customer');
            
            $response->assertStatus(200)
                    ->assertJson([]);
        });
        
        it('returns list of customers', function () {
            // Create some test customers
            Customer::factory()->create([
                'name' => 'John Doe',
                'address' => '123 Main St',
                'phone' => '+1234567890'
            ]);
            
            Customer::factory()->create([
                'name' => 'Jane Smith',
                'address' => '456 Oak Ave',
                'phone' => '+0987654321'
            ]);
            
            $response = $this->getJson('/api/mst-customer');
            
            $response->assertStatus(200)
                    ->assertJsonCount(2)
                    ->assertJsonStructure([
                        '*' => [
                            'id',
                            'name',
                            'address',
                            'phone',
                            'created_at',
                            'updated_at'
                        ]
                    ]);
        });
    });

    describe('GET /api/mst-customer/{id}', function () {
        
        it('returns specific customer', function () {
            $customer = Customer::factory()->create([
                'name' => 'John Doe',
                'address' => '123 Main St',
                'phone' => '+1234567890'
            ]);
            
            $response = $this->getJson("/api/mst-customer/{$customer->id}");
            
            $response->assertStatus(200)
                    ->assertJson([
                        'id' => $customer->id,
                        'name' => 'John Doe',
                        'address' => '123 Main St',
                        'phone' => '+1234567890'
                    ]);
        });
        
        it('returns 404 for non-existent customer', function () {
            $response = $this->getJson('/api/mst-customer/999');
            
            $response->assertStatus(404);
        });
    });
}); 