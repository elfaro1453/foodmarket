<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use App\Models\Food;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FoodController extends Controller
{

    /**
     * Get all foods in paginated json format
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(Request $request) : JsonResponse
    {
        $id = $request->input('id');

        if ($id) {
            $food = Food::find($id);
            if ($food) {
                return ResponseFormatter::success(
                    $food,
                    "Data makanan id $id berhasil ditemukan"
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    "Data makanan id $id tidak ditemukan",
                    404
                );
            }
        }

        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $types = $request->input('types');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        $rate_from = $request->input('rate_from');
        $rate_to = $request->input('rate_to');

        $query = Food::query();
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        if ($types) {
            $query->where('name', 'like', '%' . $types . '%');
        }

        if ($price_from) {
            $query->where('name', 'like', '%' . $price_from . '%');
        }

        if ($price_to) {
            $query->where('name', 'like', '%' . $price_to . '%');
        }

        if ($rate_from) {
            $query->where('name', 'like', '%' . $rate_from . '%');
        }

        if ($rate_to) {
            $query->where('name', 'like', '%' . $rate_to . '%');
        }

        $queryResult = $query->simplePaginate($limit);

        return ResponseFormatter::success(
            $queryResult,
            'Data list produk berhasil diambil'
        );
    }
}
