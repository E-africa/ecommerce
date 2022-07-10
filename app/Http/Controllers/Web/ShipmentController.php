<?php

namespace App\Http\Controllers\Web;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Model\ShippingAddress;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\Order;
use App\Model\Seller;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ShipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $order = OrderDetail::where('order_id', $request->order_id)->first();
        $product = Product::where('id', $order->product_id)->first();
        $order_with_customer = Order::where('id', $request->order_id)->first();
        $customer_id = $order_with_customer->customer_id;
//        $shipping_address = ShippingAddress::where('customer_id',$customer_id)->first();

//                    Customer_details

        $customer_cityname = \App\CPU\CartManager::get_customer_cityname($customer_id);
        $customer_countrycode = \App\CPU\CartManager::get_customer_country_code($customer_id);
        $customer_postalCode = \App\CPU\CartManager::get_customer_postalCode($customer_id);
        $customer_countryname = \App\CPU\CartManager::get_customer_cityname($customer_id);
        $customer_mobile = \App\CPU\CartManager::get_customer_mobileNumber($customer_id);
        $customer_phone = \App\CPU\CartManager::get_customer_mobileNumber($customer_id);
        $customer_fullname = \App\CPU\CartManager::get_customer_name($customer_id);
        $customer_email = $request->customer_email;


//                 Seller Details

        $seller = Seller::where('id', $order->seller_id)->first();

        $seller_city = \App\CPU\CartManager::get_seller_city($order->seller_id);

        $sellerCountry = $seller->country;
        $sellerCity = \App\CPU\CartManager::get_seller_city($order->seller->id);
        $sellerPhone = $seller->phone;
        $sellerName = $seller->f_name;
        $sellerEmail = $seller->email;

        $weight = $product->weight;
        $length = $product->length;
        $height = $product->height;
        $width = $product->width;
        $description = $product->meta_description;



        $dhlAccount = \App\CPU\CartManager::get_dhl_account($sellerCountry);

        $DateShipping = gmdate("Y-m-d",strtotime(' +1 day'))."T".gmdate('H:m:s')." GMT+01:00";

        $DatePickUp = gmdate("Y-m-d",strtotime(' +1 day'))."T".gmdate('H:m:s',strtotime('+5 hours'))." GMT+01:00";


        $results = \App\CPU\CartManager::createShipment($height, $width, $length, $weight, $customer_email, $customer_fullname, $customer_mobile, $DateShipping, $sellerCity, $sellerCountry, $customer_cityname, $sellerPhone, $sellerName, $sellerEmail, $customer_countrycode, $customer_postalCode, $dhlAccount,$description);

        $trackingNumber = $results->shipmentTrackingNumber;

//        dd($trackingNumber);

        foreach ($results->documents as $doc) {
            $file = $doc->content;
            DB::update('update order_details set file = ? where order_id = ?',
                [$file, $request->order_id]);
        }
        $update = "created";
        DB::update('update orders set shipment = ? where id = ?', [$update, $order->id]);

        $file_name = $order->id . '.pdf';
        Storage::disk('public')->put($file_name, base64_decode($file));

        DB::update('update order_details set ImageName = ? where order_id = ? ', [$file_name, $request->order_id]);

        $pickUp = \App\CPU\CartManager::OrderPickUp($height, $width, $length, $weight, $DatePickUp, $sellerCity, $sellerCountry, $sellerPhone, $sellerName, $sellerEmail,$dhlAccount);

        $pickUpNumber = $pickUp->dispatchConfirmationNumbers[0];

        DB::update('update order_details set orderPickUp = ? where order_id = ?', [$pickUpNumber, $request->order_id]);

        DB::update('update order_details set trackingNumber = ? where order_id = ?', [$trackingNumber, $request->order_id]);

        Toastr::success('Shipment Created successfully!');
        Toastr::success('Pickup ordered successfully check files!');
        return redirect()->back();

    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function download(Request $request)
    {

        $order = OrderDetail::where('order_id', $request->order_id)->first();

        $imageName = $order->id;

        $headers = [
            'Conte'
        ];

        $path = Storage::disk('public')->path("$imageName.pdf");

        return response()->download($path);

        return redirect()->back();


    }


    public function exchangeRates()
    {

        $APIKEY = "NeiWeh9O8qCey2a5PumNVwIoYZKPth8J";
        $Kenya = "KES";
        $Morroco = "MAD";
        $Congo = "CDF";
        $Nigeria = "NGN";
        $Namibia = "NAD";
        $Mozambique = "MZN";
        $Burkinafaso = "EUR";
        $URL = 'https://api.apilayer.com/fixer/latest?base=USD&symbols='.$Kenya.','.$Morroco.','.$Congo.','.$Nigeria.','.$Namibia.','.$Mozambique.','.$Burkinafaso;

        $KenyanProducts = DB::select('select * from products where Currency = ? ', [$Kenya]);
        $BurkinafasoProducts = DB::select('select * from products where Currency = ? ', [$Burkinafaso]);
        $MorroccoProducts = DB::select('select * from products where Currency = ? ', [$Morroco]);
        $NigeriaProducts = DB::select('select * from products where Currency = ? ', [$Nigeria]);
        $MozambiqueProducts = DB::select('select * from products where Currency = ? ', [$Mozambique]);
        $NamibiaProducts = DB::select('select * from products where Currency = ? ', [$Namibia]);
        $CongoProducts = DB::select('select * from products where Currency = ? ', [$Congo]);

        $client = new \GuzzleHttp\Client([
            'headers'=>array('apikey'=>$APIKEY),
        ]);
        $res = $client->get($URL);

        $Content = json_decode($res->getBody()->getContents());

        $rates = $Content->rates;


        $KenyanConst = $rates->KES;
        $CongoConst = $rates->CDF;
        $NamibiaConst = $rates->NAD;
        $MozambiqueConst = $rates->MZN;
        $BurkinafasoConst = $rates->EUR;
        $MorrocoConst = $rates->MAD;
        $NigeriaConst = $rates->NGN;



//Update Kenyan currency

        foreach ($KenyanProducts as $product ){
            $id = $product->id;
            $price = round($product->interprice/$KenyanConst);
            DB::update('update products set unit_price = ? where id = ?',
                [$price, $id]);
        }

        //Morroco
        foreach ($MorroccoProducts as $product ){
            $id = $product->id;
            $price = round($product->interprice/$MorrocoConst);
            DB::update('update products set unit_price = ? where id = ?',
                [$price, $id]);
        }


        //Update CongoProducts
        foreach ($CongoProducts as $product ){
            $id = $product->id;
            $price = round($product->interprice/$CongoConst);
            DB::update('update products set unit_price = ? where id = ?',
                [$price, $id]);
        }

        //Update NigeriaProducts
        foreach ($NigeriaProducts as $product ){
            $id = $product->id;
            $price = round($product->interprice/$NigeriaConst);
            DB::update('update products set unit_price = ? where id = ?',
                [$price, $id]);
        }

        //Update NamibiaProducts
        foreach ($NamibiaProducts as $product ){
            $id = $product->id;
            $price = round($product->interprice/$NamibiaConst);
            DB::update('update products set unit_price = ? where id = ?',
                [$price, $id]);
        }

        //Update MozambiqueProducts
        foreach ($MozambiqueProducts as $product ){
            $id = $product->id;
            $price = round($product->interprice/$MozambiqueConst);
            DB::update('update products set unit_price = ? where id = ?',
                [$price, $id]);
        }

        //Update BurkinafasoProducts
        foreach ($BurkinafasoProducts as $product ){
            $id = $product->id;
            $price = round($product->interprice/$BurkinafasoConst);
            DB::update('update products set unit_price = ? where id = ?',
                [$price, $id]);
        }

        Toastr::success('Rates Converted successfully!');
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
