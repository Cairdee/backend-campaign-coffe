<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Order;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database']; // bisa tambahin 'mail' juga nanti kalau perlu
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Status Pesanan Diperbarui',
            'body' => 'Status pesanan #' . $this->order->id . ' sekarang: ' . $this->order->status,
            'order_id' => $this->order->id,
        ];
    }
}
