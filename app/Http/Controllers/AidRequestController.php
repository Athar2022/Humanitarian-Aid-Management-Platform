<?php

namespace App\Http\Controllers;

use App\Models\AidRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AidRequestController extends Controller
{
    public function index()
    {
        $requests = AidRequest::with('beneficiary')->get();
        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'description' => 'required|string',
            'document' => 'nullable|file|max:10240', // 10MB max
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('documents', 'public');
        }

        $aidRequest = AidRequest::create([
            'beneficiary_id' => auth()->id(),
            'type' => $validated['type'],
            'description' => $validated['description'],
            'document_url' => $documentPath,
        ]);

        return response()->json($aidRequest, 201);
    }

    public function show(AidRequest $aidRequest)
    {
        return response()->json($aidRequest->load('beneficiary'));
    }

    public function update(Request $request, AidRequest $aidRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,denied',
        ]);

        $aidRequest->update(['status' => $validated['status']]);

        return response()->json($aidRequest);
    }

    public function destroy(AidRequest $aidRequest)
    {
        if ($aidRequest->document_url) {
            Storage::disk('public')->delete($aidRequest->document_url);
        }

        $aidRequest->delete();

        return response()->json(null, 204);
    }
}