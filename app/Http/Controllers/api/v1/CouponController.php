<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Model\Coupon;
use App\Model\Order;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {

        try {
             $couponLimit = Order::where('customer_id',$request->user()->id)
                         ->where('coupon_code',$request['code'])->count();

             $coupon = Coupon::where(['code' => $request['code']])
                             ->where('limit','>',$couponLimit)
                             ->where('status','=',1)
                             ->where('start_date', '<=', now())
                             ->where('expire_date', '>', now())->first();
            //$coupon = Coupon::where(['code' => $request['code']])->first();
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }

        return response()->json($coupon,200);
    }
}
