<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Models\UserRole;
use App\Models\SalesArea;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Order API Feature Tests', function () {

    beforeEach(function () {
        // Create test data
        $this->role = UserRole::create(['name' => 'Sales']);
        $this->user = User::create([
            'name' => 'Sales Person',
            'email' => 'sales@example.com',
            'phone' => '+1234567890',
            'is_active' => true,
            'role_id' => $this->role->id,
            'password' => bcrypt('password')
        ]);
        
        $this->area = SalesArea::create(['name' => 'North Area']);
        
        $this->sales = Sales::create([
            'user_id' => $this->user->id,
            'area_id' => $this->area->id
        ]);
        
        $this->customer = Customer::create([
            'name' => 'Test Customer',
            'address' => '123 Test Street',
            'phone' => '+1234567890'
        ]);
        
        $this->product1 = Product::create([
            'name' => 'Product 1',
            'production_price' => 100.00,
            'selling_price' => 150.00
        ]);
        
        $this->product2 = Product::create([
            'name' => 'Product 2',
            'production_price' => 200.00,
            'selling_price' => 250.00
        ]);
    });

    describe('POST /api/trx-order', function () {
        
        it('creates order with single item successfully', function () {
            $orderData = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 2
                    ]
                ]
            ];
            
            $response = $this->postJson('/api/trx-order', $orderData);
            
            $response->assertStatus(201)
                    ->assertJsonStructure([
                        'message',
                        'data' => [
                            'id',
                            'reference_no',
                            'sales_id',
                            'customer_id',
                            'created_at',
                            'updated_at'
                        ]
                    ])
                    ->assertJson([
                        'message' => 'Sales order created successfully',
                        'data' => [
                            'sales_id' => $this->sales->id,
                            'customer_id' => $this->customer->id
                        ]
                    ]);
            
            // Verify order was created in database
            $this->assertDatabaseHas('sales_orders', [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id
            ]);
            
            // Verify order item was created
            $order = SalesOrder::latest()->first();
            $this->assertDatabaseHas('sales_order_items', [
                'sales_order_id' => $order->id,
                'product_id' => $this->product1->id,
                'quantity' => 2,
                'production_price' => 100.00,
                'selling_price' => 150.00
            ]);
        });
        
        it('creates order with multiple items successfully', function () {
            $orderData = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 3
                    ],
                    [
                        'product_id' => $this->product2->id,
                        'quantity' => 1
                    ]
                ]
            ];
            
            $response = $this->postJson('/api/trx-order', $orderData);
            
            $response->assertStatus(201);
            
            // Verify order was created
            $order = SalesOrder::latest()->first();
            expect($order->sales_id)->toBe($this->sales->id);
            expect($order->customer_id)->toBe($this->customer->id);
            
            // Verify both items were created
            expect($order->items()->count())->toBe(2);
            
            $items = $order->items;
            expect($items->where('product_id', $this->product1->id)->first()->quantity)->toBe(3);
            expect($items->where('product_id', $this->product2->id)->first()->quantity)->toBe(1);
        });
        
        it('generates unique reference numbers', function () {
            $orderData1 = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => [['product_id' => $this->product1->id, 'quantity' => 1]]
            ];
            
            $orderData2 = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => [['product_id' => $this->product2->id, 'quantity' => 1]]
            ];
            
            // Create first order
            $response1 = $this->postJson('/api/trx-order', $orderData1);
            $response1->assertStatus(201);
            
            // Create second order
            $response2 = $this->postJson('/api/trx-order', $orderData2);
            $response2->assertStatus(201);
            
            $order1RefNo = $response1->json('data.reference_no');
            $order2RefNo = $response2->json('data.reference_no');
            
            expect($order1RefNo)->not->toBe($order2RefNo);
            expect($order1RefNo)->toMatch('/^INV\d{8}-\d{4}$/');
            expect($order2RefNo)->toMatch('/^INV\d{8}-\d{4}$/');
        });
        
        it('returns validation errors for missing fields', function () {
            $response = $this->postJson('/api/trx-order', []);
            
            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['sales_id', 'customer_id', 'items']);
        });
        
        it('returns validation errors for empty items array', function () {
            $orderData = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => []
            ];
            
            $response = $this->postJson('/api/trx-order', $orderData);
            
            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['items']);
        });
        
        it('returns validation errors for invalid foreign keys', function () {
            $orderData = [
                'sales_id' => 999, // Non-existent
                'customer_id' => 999, // Non-existent
                'items' => [
                    [
                        'product_id' => 999, // Non-existent
                        'quantity' => 1
                    ]
                ]
            ];
            
            $response = $this->postJson('/api/trx-order', $orderData);
            
            $response->assertStatus(422)
                    ->assertJsonValidationErrors([
                        'sales_id',
                        'customer_id',
                        'items.0.product_id'
                    ]);
        });
        
        it('returns validation errors for invalid quantity', function () {
            $orderData = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 0 // Invalid - must be >= 1
                    ]
                ]
            ];
            
            $response = $this->postJson('/api/trx-order', $orderData);
            
            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['items.0.quantity']);
        });
        
        it('handles product not found during processing', function () {
            $orderData = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 1
                    ]
                ]
            ];
            
            // Delete the product after validation but before processing
            $this->product1->delete();
            
            $response = $this->postJson('/api/trx-order', $orderData);
            
            $response->assertStatus(404)
                    ->assertJson([
                        'error' => 'Product not found'
                    ]);
            
            // Verify no order was created due to rollback
            $this->assertDatabaseMissing('sales_orders', [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id
            ]);
        });
        
        it('maintains data integrity on partial failure', function () {
            $orderData = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 1
                    ],
                    [
                        'product_id' => 999, // This will fail validation
                        'quantity' => 1
                    ]
                ]
            ];
            
            $response = $this->postJson('/api/trx-order', $orderData);
            
            $response->assertStatus(422);
            
            // Verify no order or items were created
            $this->assertDatabaseMissing('sales_orders', [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id
            ]);
            
            expect(SalesOrderItem::count())->toBe(0);
        });
    });

    describe('order pricing', function () {
        
        it('uses correct product prices from database', function () {
            $orderData = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 2
                    ]
                ]
            ];
            
            $response = $this->postJson('/api/trx-order', $orderData);
            $response->assertStatus(201);
            
            // Verify prices are taken from product model
            $order = SalesOrder::latest()->first();
            $item = $order->items()->first();
            
            expect($item->production_price)->toBe(100.00);
            expect($item->selling_price)->toBe(150.00);
        });
        
        it('handles different products with different prices', function () {
            $orderData = [
                'sales_id' => $this->sales->id,
                'customer_id' => $this->customer->id,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 1
                    ],
                    [
                        'product_id' => $this->product2->id,
                        'quantity' => 1
                    ]
                ]
            ];
            
            $response = $this->postJson('/api/trx-order', $orderData);
            $response->assertStatus(201);
            
            $order = SalesOrder::latest()->first();
            $items = $order->items;
            
            $item1 = $items->where('product_id', $this->product1->id)->first();
            $item2 = $items->where('product_id', $this->product2->id)->first();
            
            expect($item1->production_price)->toBe(100.00);
            expect($item1->selling_price)->toBe(150.00);
            expect($item2->production_price)->toBe(200.00);
            expect($item2->selling_price)->toBe(250.00);
        });
    });
}); 