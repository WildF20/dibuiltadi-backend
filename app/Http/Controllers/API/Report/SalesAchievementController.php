<?php

namespace App\Http\Controllers\API\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\Sales;

use App\Repository\Transaction\TransactionRepository;

class SalesAchievementController extends Controller
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
        $filter['isUnderperform'] = $request->has('isUnderperform') ? ($request->isUnderperform == 'true' ? 'true' : 'false') : null;
        $filter['period'] = $request->period ?? Carbon::now()->format('Y-m');

        try {
            $results = $this->transRepo->getSalesAchievement($filter);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            $results = [];
        }

        return response()->json([
            'is_underperform' => $filter['isUnderperform'],
            'month' => Carbon::parse($filter['period'])->format('F Y'),
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
