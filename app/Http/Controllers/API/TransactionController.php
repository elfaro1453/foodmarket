<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Get all transactions in paginated json format
     *
     * @param Request $request
     *
     * @return App\Helpers\ResponseFormatter
     */
    public function all(Request $request)
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

        $query = Transaction::with(['food', 'user'])->where('user_id', $id);
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
     * @param integer $id
     *
     * @return App\Helpers\ResponseFormatter
     */
    public function update(Request $request, $id)
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

    public function checkOut(Request $request)
    {
        
    }
}
