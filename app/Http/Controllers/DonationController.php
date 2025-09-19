<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DonationController extends Controller
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
        $donations = Donation::orderBy('created_at', 'desc')->get();
        return response()->json($donations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'donor_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'sometimes|in:pending,approved,distributed',
        ]);

        $donation = Donation::create($validated);

        return response()->json($donation, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Donation $donation)
    {
        return response()->json($donation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Donation $donation)
    {
         $validated = $request->validate([
            'donor_name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'quantity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:pending,approved,distributed',
        ]);

        $donation->update($validated);

        return response()->json($donation);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Donation $donation)
    {
        $donation->delete();

        return response()->json(null, 204);
    }


    public function approve(Donation $donation)
    {
        $donation->update(['status' => 'approved']);

        return response()->json($donation);
    }

    public function markDistributed(Donation $donation)
    {
        $donation->update(['status' => 'distributed']);

        return response()->json($donation);
    }

}
