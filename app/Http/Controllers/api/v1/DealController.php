<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\FlashDeal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function get_featured_deal()
    {
        $featured_deal = FlashDeal::with(['products.product.reviews'])
            ->where(['status' => 1])
            ->where(['deal_type' => 'feature_deal'])->first();

        $featured_deal = $featured_deal->products->map( function ($data) {
            $data->product = Helpers::product_data_formatting($data->product,false);
            return $data;
        });

        return response()->json($featured_deal, 200);
    }
}
