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

    public function __construct()
    {
        $this->httpClient = new Client();
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

        $apiKey = config('app.abstract_api_key');
        $url    = "https://phonevalidation.abstractapi.com/v1/?api_key=$apiKey&phone=$request->phone";

        $validatePhone = $this->httpClient->request('GET', $url);

        $validatePhone = json_decode($validatePhone->getBody(), true);

        if ($validatePhone['valid'] == false) {
            return response()->json(['error' => 'Phone number is not valid'], 422);
        }

        $customer = Customer::create($request->only('name', 'address', 'phone'));
        
        return response()->json($customer, 201);
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
