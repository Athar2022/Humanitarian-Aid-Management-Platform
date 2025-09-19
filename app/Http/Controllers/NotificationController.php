<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = Notification::with('user')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
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
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'type' => 'required|string',
        ]);

        $notification = Notification::create([
            'user_id' => $validated['user_id'],
            'message' => $validated['message'],
            'type' => $validated['type'],
        ]);

        return response()->json($notification->load('user'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        // التأكد من أن المستخدم يمكنه الوصول إلى الإشعار
        if ($notification->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($notification->load('user'));
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
    public function update(Request $request, Notification $notification)
    {
         if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:unread,read',
        ]);

        $notification->update(['status' => $validated['status']]);

        return response()->json($notification->load('user'));
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        // التأكد من أن المستخدم يمكنه حذف الإشعار
        if ($notification->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(null, 204);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('status', 'unread')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function notifyAidRequestStatus($userId, $requestId, $status)
    {
        $user = User::findOrFail($userId);
        
        $message = "Your aid request #{$requestId} has been {$status}";
        $type = 'aid_request_status';

        $notification = Notification::create([
            'user_id' => $user->id,
            'message' => $message,
            'type' => $type,
        ]);

        return $notification;
    }
}
