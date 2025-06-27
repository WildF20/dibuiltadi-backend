<?php

namespace App\Http\Controllers\API\Master;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class CustomerController extends Controller
{
    protected $httpClient;
    protected $url;

    public function __construct()
    {
        $this->httpClient   = new Client();
        
        $apiKey             = config('app.abstract_api_key');
        $this->url          = "https://phonevalidation.abstractapi.com/v1/?api_key=$apiKey&phone=";
    }

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
            'name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $validatePhone = $this->httpClient->request('GET', $this->url . $request->phone);

        $validatePhone = json_decode($validatePhone->getBody(), true);

        if ($validatePhone['valid'] == false) {
            return response()->json(['error' => 'Phone number is not valid'], 422);
        }

        $customer = Customer::create($request->only('name', 'address', 'phone'));
        
        return response()->json([
            'message' => 'Customer created successfully',
        ], 201);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $validatePhone = $this->httpClient->request('GET', $this->url . $request->phone);

        $validatePhone = json_decode($validatePhone->getBody(), true);

        if ($validatePhone['valid'] == false) {
            return response()->json(['error' => 'Phone number is not valid'], 422);
        }

        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $customer->update($request->only('name', 'address', 'phone'));
        
        return response()->json(['message' => 'Customer updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
