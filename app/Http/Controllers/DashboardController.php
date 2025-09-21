<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Donation;
use App\Models\AidRequest;
use App\Models\Distribution;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'beneficiaries' => User::where('role', 'beneficiary')->count(),
            'volunteers' => User::where('role', 'volunteer')->count(),
            'donations' => Donation::count(),
            'aid_requests' => AidRequest::count(),
            'distributions' => Distribution::count(),
            'pending_requests' => AidRequest::where('status', 'pending')->count(),
            'approved_requests' => AidRequest::where('status', 'approved')->count(),
            'completed_distributions' => Distribution::where('delivery_status', 'delivered')->count(),
        ];

        return response()->json($stats);
    }

    public function charts(Request $request)
    {
        $aidRequestsByType = AidRequest::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        $aidRequestsByStatus = AidRequest::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $currentMonthDistributions = Distribution::whereMonth('created_at', Carbon::now()->month)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        $currentMonthDonations = Donation::whereMonth('created_at', Carbon::now()->month)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        return response()->json([
            'aid_requests_by_type' => $aidRequestsByType,
            'aid_requests_by_status' => $aidRequestsByStatus,
            'monthly_distributions' => $currentMonthDistributions,
            'monthly_donations' => $currentMonthDonations,
        ]);
    }

    public function activity(Request $request)
    {
        $aidRequestsActivity = AidRequest::with('beneficiary')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'type' => 'aid_request',
                    'description' => "New aid request by {$request->beneficiary->name}",
                    'created_at' => $request->created_at,
                    'user_name' => $request->beneficiary->name,
                    'user_initials' => strtoupper(substr($request->beneficiary->name, 0, 2))
                ];
            });

        $donationsActivity = Donation::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($donation) {
                return [
                    'id' => $donation->id,
                    'type' => 'donation',
                    'description' => "New donation from {$donation->donor_name}",
                    'created_at' => $donation->created_at,
                    'user_name' => $donation->donor_name,
                    'user_initials' => strtoupper(substr($donation->donor_name, 0, 2))
                ];
            });

        $distributionsActivity = Distribution::with(['volunteer', 'beneficiary'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($distribution) {
                return [
                    'id' => $distribution->id,
                    'type' => 'distribution',
                    'description' => "Distribution assigned to {$distribution->volunteer->name} for {$distribution->beneficiary->name}",
                    'created_at' => $distribution->created_at,
                    'user_name' => $distribution->volunteer->name,
                    'user_initials' => strtoupper(substr($distribution->volunteer->name, 0, 2))
                ];
            });

        $allActivity = collect()
            ->merge($aidRequestsActivity)
            ->merge($donationsActivity)
            ->merge($distributionsActivity)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        return response()->json($allActivity);
    }

    public function userStats(Request $request)
    {
        $user = $request->user();
        
        $stats = [];
        
        if ($user->role === 'beneficiary') {
            $stats = [
                'my_requests' => AidRequest::where('beneficiary_id', $user->id)->count(),
                'pending_requests' => AidRequest::where('beneficiary_id', $user->id)
                    ->where('status', 'pending')->count(),
                'approved_requests' => AidRequest::where('beneficiary_id', $user->id)
                    ->where('status', 'approved')->count(),
                'received_aid' => Distribution::where('beneficiary_id', $user->id)
                    ->where('delivery_status', 'delivered')->count(),
            ];
        } elseif ($user->role === 'volunteer') {
            $stats = [
                'assigned_distributions' => Distribution::where('volunteer_id', $user->id)->count(),
                'completed_distributions' => Distribution::where('volunteer_id', $user->id)
                    ->where('delivery_status', 'delivered')->count(),
                'in_progress_distributions' => Distribution::where('volunteer_id', $user->id)
                    ->where('delivery_status', 'in_progress')->count(),
            ];
        } elseif ($user->role === 'admin') {
            $stats = $this->stats($request)->getData();
        }

        return response()->json($stats);
    }
}