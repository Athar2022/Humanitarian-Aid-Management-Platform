<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DistributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $distributions = Distribution::with(['volunteer', 'beneficiary', 'donation'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($distributions);
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
        $validated = $request->validate([
            'volunteer_id' => 'required|exists:users,id',
            'beneficiary_id' => 'required|exists:users,id',
            'donation_id' => 'required|exists:donations,id',
            'delivery_status' => 'sometimes|in:assigned,in_progress,delivered',
            'proof_file' => 'nullable|file|max:10240',
        ]);

        // التأكد من أن المتطوع له دور volunteer
        $volunteer = User::findOrFail($validated['volunteer_id']);
        if ($volunteer->role !== 'volunteer') {
            return response()->json(['error' => 'User must be a volunteer'], 422);
        }

        // التأكد من أن المستفيد له دور beneficiary
        $beneficiary = User::findOrFail($validated['beneficiary_id']);
        if ($beneficiary->role !== 'beneficiary') {
            return response()->json(['error' => 'User must be a beneficiary'], 422);
        }

        // التأكد من أن التبرع معتمد
        $donation = Donation::findOrFail($validated['donation_id']);
        if ($donation->status !== 'approved') {
            return response()->json(['error' => 'Donation must be approved'], 422);
        }

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('distribution-proofs', 'public');
        }

        $distribution = Distribution::create([
            'volunteer_id' => $validated['volunteer_id'],
            'beneficiary_id' => $validated['beneficiary_id'],
            'donation_id' => $validated['donation_id'],
            'delivery_status' => $validated['delivery_status'] ?? 'assigned',
            'proof_file' => $proofPath,
        ]);

        // تحديث حالة التبرع إلى distributed
        $donation->update(['status' => 'distributed']);

        return response()->json($distribution->load(['volunteer', 'beneficiary', 'donation']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Distribution $distribution)
    {
        return response()->json($distribution->load(['volunteer', 'beneficiary', 'donation']));

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
    public function update(Request $request, Distribution $distribution)
    {
        $validated = $request->validate([
            'delivery_status' => 'sometimes|in:assigned,in_progress,delivered',
            'proof_file' => 'nullable|file|max:10240',
        ]);

        $proofPath = $distribution->proof_file;
        if ($request->hasFile('proof_file')) {
            // حذف الملف القديم إذا موجود
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }
            $proofPath = $request->file('proof_file')->store('distribution-proofs', 'public');
        }

        $distribution->update([
            'delivery_status' => $validated['delivery_status'] ?? $distribution->delivery_status,
            'proof_file' => $proofPath,
        ]);

        return response()->json($distribution->load(['volunteer', 'beneficiary', 'donation']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Distribution $distribution)
    {
        if ($distribution->proof_file) {
            Storage::disk('public')->delete($distribution->proof_file);
        }

        $distribution->delete();

        return response()->json(null, 204);
    }

    public function updateStatus(Distribution $distribution, $status)
    {
        $validStatuses = ['assigned', 'in_progress', 'delivered'];
        
        if (!in_array($status, $validStatuses)) {
            return response()->json(['error' => 'Invalid status'], 422);
        }

        $distribution->update(['delivery_status' => $status]);

        return response()->json($distribution->load(['volunteer', 'beneficiary', 'donation']));
    }

    public function volunteerDistributions()
    {
        $distributions = Distribution::with(['beneficiary', 'donation'])
            ->where('volunteer_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($distributions);
    }
}
