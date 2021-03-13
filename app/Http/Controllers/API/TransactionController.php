<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
    /**
     * Get all transactions in paginated json format.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $foodId = $request->input('food_id');
        $status = $request->input('status');

        if ($id) {
            /**
             * @see https://laravel.com/docs/8.x/eloquent-relationships#eager-loading-multiple-relationships
             */
            $query = Transaction::with(['food', 'user'])->find($id);

            if ($query) {
                return ResponseFormatter::success(
                    $query,
                    "Data transaksi id $id berhasil ditemukan"
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    "Data transaksi dengan id $id tidak ditemukan"
                );
            }
        }

        $userId = Auth::user()->id;

        $query = Transaction::with(['food', 'user'])->where('user_id', $userId);
        if ($foodId) {
            $query->where('food_id', $foodId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $queryResult = $query->simplePaginate($limit);

        return ResponseFormatter::success(
            $queryResult,
            'Data transaksi berhasil diambil'
        );
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        /**
         * @see https://laravel.com/docs/8.x/eloquent#not-found-exceptions
         */
        $query = Transaction::findOrFail($id);

        // exclude user_id and food_id
        // https://laravel.com/docs/8.x/routing#parameters-and-dependency-injection
        $data = $request->except(['user_id', 'food_id']);

        $query->update($data);

        return ResponseFormatter::success(
            $query,
            "Transaksi id $id berhasil diperbarui"
        );
    }

    /**
     * Checkout controller to.
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function checkout(Request $request) : JsonResponse
    {
        // validate request
        $validator = Validator::make(
            $request->all(),
            [
                'food_id' => 'required|exists:App\Models\Food,id',
                // 'user_id' => 'required|exists:App\Models\User,id',
                'quantity' => 'required',
                'total' => 'required',
                'status' => 'required',
            ]
        );

        // return error if validation fails
        if ($validator->fails()) {
            // get all the error
            $errors = $validator->errors()->all();

            // return json with all error
            return ResponseFormatter::error(
                [
                'message' => 'Invalid Input',
                'error' => $errors,
                ],
                'Invalid input',
                400
            );
        }

        // get current User
        $user = Auth::user();

        // create a transactions
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->food_id = $request->food_id;
        $transaction->quantity = $request->quantity;
        $transaction->total = $request->total;
        $transaction->status = $request->status;
        $transaction->save();

        // configure midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        /**
         * Build Midtrans Request Body.
         * @see https://snap-docs.midtrans.com/#request-body-json-parameter
         */
        $midtrans = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => $transaction->total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'enabled_payments' => [
                'bank_transfer', 'indomaret', 'gopay',
            ],
            'vtweb' => [],
        ];

        try {
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            // get transaction details with user and food description
            $transactionDetails = Transaction::with(['food', 'user'])->find($transaction->id);

            // return transaction details
            return ResponseFormatter::success(
                $transactionDetails,
                'Transaksi Berhasil !'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Transaksi Error',
                500
            );
        }
    }
}
