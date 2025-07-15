<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ResponseApi;

class NotificationController extends Controller
{
    use ResponseApi;
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();

        return $this->success([
            'notifications' => $notifications
        ]);
    }

     public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'nullable|string'  // Bisa kirim ID atau kosong (untuk semua)
        ]);

        $user = $request->user();

        if ($request->notification_id) {
            // Tandai notifikasi tertentu sebagai sudah dibaca
            $notification = $user->notifications()->find($request->notification_id);
            if ($notification) {
                $notification->markAsRead();
                return $this->success(['message' => 'Notifikasi ditandai sebagai sudah dibaca']);
            } else {
                return $this->success(['message' => 'Notifikasi tidak ditemukan'], 404);
            }
        } else {
            // Tandai semua notifikasi sebagai sudah dibaca
            $user->unreadNotifications->markAsRead();
            return $this->success(['message' => 'Semua notifikasi ditandai sebagai sudah dibaca']);
        }

    }
}

