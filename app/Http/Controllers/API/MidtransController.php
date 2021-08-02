<?php

namespace App\Http\Controllers\API;

use Midtrans\Config;
use Midtrans\Notification;
use App\Http\Controllers\Controller;
use App\Models\Transaction;

class MidtransController extends Controller
{
    public function callback()
    {
        // Konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Buat instance midtrans notification
        $notification = new Notification();

        // Assign ke variable untuk memudahkan ngoding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // Get order id
        $order = explode('-', $order_id);

        // Cari transaksi berdasarkan ID
        $transaction = Transaction::findOrFail($order[1]);

        // Handle notification status midtrans
        if($status == "capture") {
            if($type == "credit_card") {
                if($fraud == "challenge") {
                    $transaction->status = "PENDING";
                } else {
                    $transaction->status = "SUCCESS";
                }
            }
        } elseif($status == "settlement") {
            $transaction->status = "SUCCESS";
        } elseif($status == "pending") {
            $transaction->status = "PENDING";
        } elseif($status == "deny") {
            $transaction->status = "PENDING";
        } elseif($status == "expire") {
            $transaction->status = "CANCELLED";
        } elseif($status == "cancel") {
            $transaction->status = "CANCELLED";
        }

        // Simpan transaksi
        $transaction->save();

        // Return response untuk midtrans
        return response()->json([
            'meta' => [
                'code' => 200,
                'message' => 'Midtrans notification success!'
            ],
        ]);

    }
}
