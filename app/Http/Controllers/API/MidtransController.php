<?php

namespace App\Http\Controllers\API;

use Midtrans\Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Midtrans\Notification;

class MidtransController extends Controller
{
    
    /**
     * Controller for Midtrans callback, Midtrans will send notification via POST
     * 
     * @param Request $request
     * 
     * @return void
     */
    public function callback(Request $request)
    {
        // configure midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        /** 
         * handle midtrans notification
         * @see https://github.com/Midtrans/midtrans-php#23-handle-http-notification
         * */
        $midtransNotif = new Notification();

        $transactionStatus = $midtransNotif->transaction_status;
        $fraudStatus = $midtransNotif->fraud_status;
        $orderId = $midtransNotif->order_id;
        $paymentType = $midtransNotif->payment_type;

        // find the transaction order
        $order = Transaction::findOrFail($orderId);

        /**
         * We don't accept Credit card and refund
         * more information about transaction status
         * @see https://api-docs.midtrans.com/#transaction-status
         */
        switch ($transactionStatus) {
            case 'pending':
                // payment is pending in midtrans
                $order->status = 'PENDING';
                break;
            case 'settlement':
                // payment paid
                $order->status = 'SUCCESS';
                break;
            default:
                // Payment denied, expired, cancelled, failed and credit card authorized & captured
                $order->status = 'CANCELLED';
        }
        $order->save();
    }
}
