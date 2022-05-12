<?php

namespace App\CPU;

use App\Model\Cart;
use GuzzleHttp\Client;
use App\Model\CartShipping;
use App\Model\Color;
use App\Model\Product;
use App\Model\Seller;
use App\Model\ShippingAddress;
use App\Model\Shop;
use Barryvdh\Debugbar\Twig\Extension\Debug;
use Cassandra\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class CartManager
{
    public static function cart_to_db()
    {
        $user = Helpers::get_customer();
        if (session()->has('offline_cart')) {
            $cart = session('offline_cart');
            $storage = [];
            foreach ($cart as $item) {
                $db_cart = Cart::where(['customer_id' => $user->id, 'seller_id' => $item['seller_id'], 'seller_is' => $item['seller_is']])->first();
                $seller = Seller::find($item['seller_id']);

                $storage[] = [
                    'customer_id' => $user->id,
                    'cart_group_id' => isset($db_cart) ? $db_cart['cart_group_id'] : str_replace('offline', $user->id, $item['cart_group_id']),
                    'product_id' => $item['product_id'],
                    'color' => $item['color'],
                    'choices' => $item['choices'],
                    'variations' => $item['variations'],
                    'variant' => $item['variant'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'tax' => $item['tax'],
                    'discount' => $item['discount'],
                    'slug' => $item['slug'],
                    'name' => $item['name'],
                    'thumbnail' => $item['thumbnail'],
                    'seller_id' => $item['seller_id'],
                    'seller_is' => $item['seller_is'],
                    'shop_info' => $item['shop_info'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Cart::insert($storage);
            session()->put('offline_cart', collect([]));
        }
    }

    public static function get_cart($group_id = null)
    {
        $user = Helpers::get_customer();
        if (session()->has('offline_cart') && $user == 'offline') {
            $cart = session('offline_cart');
            if ($group_id != null) {
                return $cart->where('cart_group_id', $group_id)->get();
            } else {
                return $cart;
            }
        }

        if ($group_id == null) {
            $cart = Cart::whereIn('cart_group_id', CartManager::get_cart_group_ids())->get();
        } else {
            $cart = Cart::where('cart_group_id', $group_id)->get();
        }

        return $cart;
    }

    public static function get_cart_group_ids($request = null)
    {
        $user = Helpers::get_customer($request);
        if ($user == 'offline') {
            if (session()->has('offline_cart') == false) {
                session()->put('offline_cart', collect([]));
            }
            $cart = session('offline_cart');
            $cart_ids = array_unique($cart->pluck('cart_group_id')->toArray());
        } else {
            $cart_ids = Cart::where(['customer_id' => $user->id])->groupBy('cart_group_id')->pluck('cart_group_id')->toArray();
        }
        return $cart_ids;
    }

    public static function get_shipping_cost($group_id = null)
    {
        if ($group_id == null) {
            $cost = CartShipping::whereIn('cart_group_id', CartManager::get_cart_group_ids())->sum('shipping_cost');
        } else {
            $data = CartShipping::where('cart_group_id', $group_id)->first();
            $cost = isset($data) ? $data->shipping_cost : 0;
        }
        return $cost;
    }

    public static function get_seller_country_code($seller_id){

        $seller = Seller::find($seller_id);
        $seller_country = $seller->country;
        return Str::upper($seller_country);
    }
    public static function get_seller_city($seller_id){

        $seller = Seller::find($seller_id);
        $seller_country = Str::lower($seller->country);

        $City = "";
        switch ($seller_country) {
            case ("ke"):
                $City = "Nairobi";
                break;
            case ("cd"):
                $City = "Kinshasa";
                break;
            case ("mz"):
                $City = "Maputo";
                break;
            case ("na"):
                $City = "Windhock";
                break;
            case ("ma"):
                $City = "Agadir";
               break;
            default:
                $City = "Kinshasa";

        }

        return $City;

    }

    public static function get_product_weight($product_id){

        $product = Product::find($product_id);
        $product_weight = $product->weight;

        if($product_weight == null){
            $product_weight = 5;
        }
        return $product_weight;
    }

    public static function get_product_length($product_id){

        $product = Product::find($product_id);
        $product_length = $product->length;

        if($product_length == null){
            $product_length = 5;
        }
        return $product_length;
    }

    public static function get_product_width($product_id){

        $product = Product::find($product_id);
        $product_width = $product->width;

        if($product_width == null){
            $product_width = 5;
        }
        return $product_width;
    }

    public static function get_shipping_country($customer_id){

        $customer_country = ShippingAddress::find($customer_id);
        $customer_country = $customer_country->country;
        return $customer_country;
    }

    public static function get_customer_cityname($customer_id){
        $customer_cityname = ShippingAddress::find($customer_id);
        $customer_cityname = $customer_cityname->city;
        return $customer_cityname;
    }
    public static function get_customer_country_code($customer_id){
        $customer = ShippingAddress::find($customer_id);
        $customer_country = $customer->country;

        $lowerCountry = strtolower($customer_country);

        $FinalCountry = ucfirst($lowerCountry);

        $country = array(
            "AF" => "Afghanistan",
            "AX"=> "Aland Islands",
            "AL"=> "Albania",
            "DZ"=> "Algeria",
            "AS"=> "American Samoa",
            "AD"=> "Andorra",
            "AO"=> "Angola",
            "AI"=> "Anguilla",
            "AQ"=> "Antarctica",
            "AG"=> "Antigua and Barbuda",
            "AR"=> "Argentina",
            "AM"=> "Armenia",
            "AW"=> "Aruba",
            "AU"=> "Australia",
            "AT"=> "Austria",
            "AZ"=> "Azerbaijan",
            "BS"=> "Bahamas",
            "BH"=> "Bahrain",
            "BD"=> "Bangladesh",
            "BB"=> "Barbados",
            "BY"=> "Belarus",
            "BE"=> "Belgium",
            "BZ"=> "Belize",
            "BJ"=> "Benin",
            "BM"=> "Bermuda",
            "BT"=> "Bhutan",
            "BO"=> "Bolivia",
            "BQ"=> "Bonaire, Sint Eustatius and Saba",
            "BA"=> "Bosnia and Herzegovina",
            "BW"=> "Botswana",
            "BV"=> "Bouvet Island",
            "BR"=> "Brazil",
            "IO"=> "British Indian Ocean Territory",
            "BN"=> "Brunei Darussalam",
            "BG"=> "Bulgaria",
            "BF"=> "Burkina Faso",
            "BI"=> "Burundi",
            "KH"=> "Cambodia",
            "CM"=> "Cameroon",
            "CA"=> "Canada",
            "CV"=> "Cape Verde",
            "KY"=> "Cayman Islands",
            "CF"=> "Central African Republic",
            "TD"=> "Chad",
            "CL"=> "Chile",
            "CN"=> "China",
            "CX"=> "Christmas Island",
            "CC"=> "Cocos (Keeling) Islands",
            "CO"=> "Colombia",
            "KM"=> "Comoros",
            "CG"=> "Congo",
            "CD"=> "Congo, The Democratic Republic of ",
            "CK"=> "Cook Islands",
            "CR"=> "Costa Rica",
            "CI"=> "Cote d'Ivoire",
            "HR"=> "Croatia",
            "CU"=> "Cuba",
            "CW"=> "Curaçao",
            "CY"=> "Cyprus",
            "CZ"=> "Czechia",
            "DK"=> "Denmark",
            "DJ"=> "Djibouti",
            "DM"=> "Dominica",
            "DO"=> "Dominican Republic",
            "EC"=> "Ecuador",
            "EG"=> "Egypt",
            "SV"=> "El Salvador",
            "GQ"=> "Equatorial Guinea",
            "ER"=> "Eritrea",
            "EE"=> "Estonia",
            "ET"=> "Ethiopia",
            "FK"=> "Falkland Islands (Malvinas)",
            "FO"=> "Faroe Islands",
            "FJ"=> "Fiji",
            "FI"=> "Finland",
            "FR"=> "France",
            "GF"=> "French Guiana",
            "PF"=> "French Polynesia",
            "TF"=> "French Southern Territories",
            "GA"=> "Gabon",
            "GM"=> "Gambia",
            "GE"=> "Georgia",
            "DE"=> "Germany",
            "GH"=> "Ghana",
            "GI"=> "Gibraltar",
            "GR"=> "Greece",
            "GL"=> "Greenland",
            "GD"=> "Grenada",
            "GP"=> "Guadeloupe",
            "GU"=> "Guam",
            "GT"=> "Guatemala",
            "GG"=> "Guernsey",
            "GN"=> "Guinea",
            "GW"=> "Guinea-Bissau",
            "GY"=> "Guyana",
            "HT"=> "Haiti",
            "HM"=> "Heard and Mc Donald Islands",
            "VA"=> "Holy See (Vatican City State)",
            "HN"=> "Honduras",
            "HK"=> "Hong Kong",
            "HU"=> "Hungary",
            "IS"=> "Iceland",
            "IN"=> "India",
            "ID"=> "Indonesia",
            "IR"=> "Iran, Islamic Republic of",
            "IQ"=> "Iraq",
            "IE"=> "Ireland",
            "IM"=> "Isle of Man",
            "IL"=> "Israel",
            "IT"=> "Italy",
            "JM"=> "Jamaica",
            "JP"=> "Japan",
            "JE"=> "Jersey",
            "JO"=> "Jordan",
            "KZ"=> "Kazakstan",
            "KE"=> "Kenya",
            "KI"=> "Kiribati",
            "KP"=> "Korea, Democratic People's Republic of",
            "KR"=> "Korea, Republic of",
            "XK"=> "Kosovo (temporary code)",
            "KW"=> "Kuwait",
            "KG"=>"Kyrgyzstan",
            "LA"=> "Lao, People's Democratic Republic",
            "LV"=> "Latvia",
            "LB"=> "Lebanon",
            "LS"=> "Lesotho",
            "LR"=> "Liberia",
            "LY"=> "Libyan Arab Jamahiriya",
            "LI"=> "Liechtenstein",
            "LT"=> "Lithuania",
            "LU"=> "Luxembourg",
            "MO"=> "Macao",
            "MK"=> "Macedonia, The Former Yugoslav Republic Of",
            "MG"=> "Madagascar",
            "MW"=> "Malawi",
            "MY"=> "Malaysia",
            "MV"=> "Maldives",
            "ML"=> "Mali",
            "MT"=> "Malta",
            "MH"=> "Marshall Islands",
            "MQ"=> "Martinique",
            "MR"=> "Mauritania",
            "MU"=> "Mauritius",
            "YT"=> "Mayotte",
            "MX"=> "Mexico",
            "FM"=> "Micronesia, Federated States of",
            "MD"=> "Moldova, Republic of",
            "MC"=> "Monaco",
            "MN"=> "Mongolia",
            "ME"=> "Montenegro",
            "MS"=> "Montserrat",
            "MA"=> "Morocco",
            "MZ"=> "Mozambique",
            "MM"=> "Myanmar",
            "NA"=> "Namibia",
            "NR"=> "Nauru",
            "NP"=> "Nepal",
            "NL"=> "Netherlands",
            "AN"=> "Netherlands Antilles",
            "NC"=> "New Caledonia",
            "NZ"=> "New Zealand",
            "NI"=> "Nicaragua",
            "NE"=> "Niger",
            "NG"=> "Nigeria",
            "NU"=> "Niue",
            "NF"=> "Norfolk Island",
            "MP"=> "Northern Mariana Islands",
            "NO"=> "Norway",
            "OM"=> "Oman",
            "PK"=> "Pakistan",
            "PW"=> "Palau",
            "PS"=> "Palestinian Territory, Occupied",
            "PA"=> "Panama",
            "PG"=> "Papua New Guinea",
            "PY"=> "Paraguay",
            "PE"=> "Peru",
            "PH"=> "Philippines",
            "PN"=> "Pitcairn",
            "PL"=> "Poland",
            "PT"=> "Portugal",
            "PR"=> "Puerto Rico",
            "QA"=> "Qatar",
            "RS"=> "Republic of Serbia",
            "RE"=> "Reunion",
            "RO"=> "Romania",
            "RU"=> "Russia Federation",
            "RW"=> "Rwanda",
            "BL"=> "Saint Barthélemy",
            "SH"=> "Saint Helena",
            "KN"=> "Saint Kitts & Nevis",
            "LC"=> "Saint Lucia",
            "MF"=> "Saint Martin",
            "PM"=> "Saint Pierre and Miquelon",
            "VC"=> "Saint Vincent and the Grenadines",
            "WS"=> "Samoa",
            "SM"=> "San Marino",
            "ST"=> "Sao Tome and Principe",
            "SA"=> "Saudi Arabia",
            "SN"=> "Senegal",
            "CS"=> "Serbia and Montenegro",
            "SC"=> "Seychelles",
            "SL"=> "Sierra Leone",
            "SG"=> "Singapore",
            "SX"=> "Sint Maarten",
            "SK"=> "Slovakia",
            "SI"=> "Slovenia",
            "SB"=> "Solomon Islands",
            "SO"=> "Somalia",
            "ZA"=> "South Africa",
            "GS"=> "South Georgia & The South Sandwich Islands",
            "SS"=> "South Sudan",
            "ES"=> "Spain",
            "LK"=> "Sri Lanka",
            "SD"=> "Sudan",
            "SR"=> "Suriname",
            "SJ"=> "Svalbard and Jan Mayen",
            "SZ"=> "Swaziland",
            "SE"=> "Sweden",
            "CH"=> "Switzerland",
            "SY"=> "Syrian Arab Republic",
            "TW"=> "Taiwan, Province of China",
            "TJ"=> "Tajikistan",
            "TZ"=> "Tanzania, United Republic of",
            "TH"=> "Thailand",
            "TL"=> "Timor-Leste",
            "TG"=> "Togo",
            "TK"=> "Tokelau",
            "TO"=> "Tonga",
            "TT"=> "Trinidad and Tobago",
            "TN"=> "Tunisia",
            "TR"=> "Turkey",
            "XT"=> "Turkish Rep N Cyprus (temporary code)",
            "TM"=> "Turkmenistan",
            "TC"=> "Turks and Caicos Islands",
            "TV"=> "Tuvalu",
            "UG"=> "Uganda",
            "UA"=> "Ukraine",
            "AE"=> "United Arab Emirates",
            "GB"=> "United Kingdom",
            "US"=> "United States",
            "UM"=> "United States Minor Outlying Islands",
            "UY"=> "Uruguay",
            "UZ"=> "Uzbekistan",
            "VU"=> "Vanuatu",
            "VE"=> "Venezuela",
            "VN"=> "Vietnam",
            "VG"=> "Virgin Islands, British",
            "VI"=> "Virgin Islands, U.S.",
            "WF"=> "Wallis and Futuna",
            "EH"=> "Western Sahara",
            "YE"=> "Yemen",
            "ZM"=> "Zambia",
            "ZW"=> "Zimbabwe");

        return array_search($FinalCountry,$country);
    }

    public static function cart_total($cart)
    {
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $product_subtotal = $item['price'] * $item['quantity'];
                $total += $product_subtotal;
            }
        }
        return $total;
    }

    public static function cart_total_applied_discount($cart)
    {
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $product_subtotal = ($item['price'] - $item['discount']) * $item['quantity'];
                $total += $product_subtotal;
            }
        }
        return $total;
    }

    public static function cart_total_with_tax($cart)
    {
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $product_subtotal = ($item['price'] * $item['quantity']) + ($item['tax'] * $item['quantity']);
                $total += $product_subtotal;
            }
        }
        return $total;
    }

    public static function cart_grand_total($cart_group_id = null)
    {
        $cart = CartManager::get_cart($cart_group_id);
        $shipping_cost = CartManager::get_shipping_cost($cart_group_id);
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $product_subtotal = ($item['price'] * $item['quantity'])
                    + ($item['tax'] * $item['quantity'])
                    - $item['discount'] * $item['quantity'];
                $total += $product_subtotal;
            }
            $total += $shipping_cost;
        }
        return $total;
    }

    public static function cart_clean($request = null)
    {
        $cart_ids = CartManager::get_cart_group_ids($request);
        CartShipping::whereIn('cart_group_id', $cart_ids)->delete();
        Cart::whereIn('cart_group_id', $cart_ids)->delete();

        session()->forget('coupon_code');
        session()->forget('coupon_discount');
        session()->forget('payment_method');
        session()->forget('shipping_method_id');
        session()->forget('order_id');
        session()->forget('cart_group_id');
    }

    public static function add_to_cart($request, $from_api = false)
    {
        $str = '';
        $variations = [];
        $price = 0;

        $user = Helpers::get_customer($request);
        $product = Product::find($request->id);

        //check the color enabled or disabled for the product
        if ($request->has('color')) {
            $str = Color::where('code', $request['color'])->first()->name;
            $variations['color'] = $str;
        }

        //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
        $choices = [];
        foreach (json_decode($product->choice_options) as $key => $choice) {
            $choices[$choice->name] = $request[$choice->name];
            $variations[$choice->title] = $request[$choice->name];
            if ($str != null) {
                $str .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $str .= str_replace(' ', '', $request[$choice->name]);
            }
        }

        if ($user == 'offline') {
            if (session()->has('offline_cart')) {
                $cart = session('offline_cart');
                $check = $cart->where('product_id', $request->id)->where('variant', $str)->first();
                if (isset($check) == false) {
                    $cart = collect();
                    $cart['id'] = time();
                } else {
                    return [
                        'status' => 0,
                        'message' => translate('already_added!')
                    ];
                }
            } else {
                $cart = collect();
                session()->put('offline_cart', $cart);
            }
        } else {
            $cart = Cart::where(['product_id' => $request->id, 'customer_id' => $user->id, 'variant' => $str])->first();
            if (isset($cart) == false) {
                $cart = new Cart();
            } else {
                return [
                    'status' => 0,
                    'message' => translate('already_added!')
                ];
            }
        }

        $cart['color'] = $request->has('color') ? $request['color'] : null;
        $cart['product_id'] = $product->id;
        $cart['choices'] = json_encode($choices);

        //chek if out of stock
        if ($product['current_stock'] < $request['quantity']) {
            return [
                'status' => 0,
                'message' => translate('out_of_stock!')
            ];
        }

        $cart['variations'] = json_encode($variations);
        $cart['variant'] = $str;

        //Check the string and decreases quantity for the stock
        if ($str != null) {
            $count = count(json_decode($product->variation));
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variation)[$i]->type == $str) {
                    $price = json_decode($product->variation)[$i]->price;
                    if (json_decode($product->variation)[$i]->qty < $request['quantity']) {
                        return [
                            'status' => 0,
                            'message' => translate('out_of_stock!')
                        ];
                    }
                }
            }
        } else {
            $price = $product->unit_price;
        }

        $tax = Helpers::tax_calculation($price, $product['tax'], 'percent');

        //generate group id
        if ($user == 'offline') {
            $check = session('offline_cart');
            $cart_check = $check->where('seller_id', $product->user_id)->where('seller_is', $product->added_by)->first();
        } else {
            $cart_check = Cart::where([
                'customer_id' => $user->id,
                'seller_id' => $product->user_id,
                'seller_is' => $product->added_by])->first();
        }

        if (isset($cart_check)) {
            $cart['cart_group_id'] = $cart_check['cart_group_id'];
        } else {
            $cart['cart_group_id'] = ($user == 'offline' ? 'offline' : $user->id) . '-' . Str::random(5) . '-' . time();
        }
        //generate group id end

        $cart['customer_id'] = $user->id ?? 0;
        $cart['quantity'] = $request['quantity'];
        /*$data['shipping_method_id'] = $shipping_id;*/
        $cart['price'] = $price;
        $cart['tax'] = $tax;
        $cart['slug'] = $product->slug;
        $cart['name'] = $product->name;
        $cart['discount'] = Helpers::get_product_discount($product, $price);
        /*$data['shipping_cost'] = $shipping_cost;*/
        $cart['thumbnail'] = $product->thumbnail;
        $cart['seller_id'] = $product->user_id;
        $cart['seller_is'] = $product->added_by;
        if ($product->added_by == 'seller') {
            $cart['shop_info'] = Shop::where(['seller_id' => $product->user_id])->first()->name;
        } else {
            $cart['shop_info'] = Helpers::get_business_settings('company_name');
        }

        if ($user == 'offline') {
            $offline_cart = session('offline_cart');
            $offline_cart->push($cart);
            session()->put('offline_cart', $offline_cart);
        } else {
            $cart->save();
        }

        return [
            'status' => 1,
            'message' => translate('successfully_added!')
        ];
    }

    public static function update_cart_qty($request)
    {
        $user = Helpers::get_customer($request);
        $status = 1;
        $qty = 0;
        $cart = Cart::where(['id' => $request->key, 'customer_id' => $user->id])->first();

        $product = Product::find($cart['product_id']);
        $count = count(json_decode($product->variation));
        if ($count) {
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variation)[$i]->type == $cart['variant']) {
                    if (json_decode($product->variation)[$i]->qty < $request->quantity) {
                        $status = 0;
                        $qty = $cart['quantity'];
                    }
                }
            }
        } else if ($product['current_stock'] < $request->quantity) {
            $status = 0;
            $qty = $cart['quantity'];
        }

        if ($status){
            $qty = $request->quantity;
            $cart['quantity'] = $request->quantity;
        }

        $cart->save();

        return [
            'status' => $status,
            'qty' => $qty,
            'message' => $status == 1 ? translate('successfully_updated!') : translate('sorry_stock_is_limited')
        ];
    }

    public static function getShippingFee($seller_country_code,$Seller_city,$product_weight,$product_width,$product_length,$customer_cityname,$customer_countrycode){

        $client = new \GuzzleHttp\Client([
                'headers'=>array('Content-Type'=>'application/json'),
                'auth' => ['afrikamallCD', 'S!4nJ^2jX^9qB@3y'],
            ]);

        $url = "https://express.api.dhl.com/mydhlapi/test/rates";

        $body = array (
            'customerDetails' =>
                array (
                    'shipperDetails' =>
                        array (
                            'postalCode' => '',
                            'cityName' => $Seller_city,
                            'countryCode' => $seller_country_code,
                            'addressLine1' => 'addres1',
                            'addressLine2' => 'addres2',
                            'addressLine3' => 'addres3',
                            'countyName' => 'Kinshasa',
                        ),
                    'receiverDetails' =>
                        array (
                            'postalCode' => '14800',
                            'cityName' => $customer_cityname,
                            'countryCode' => $customer_countrycode,
                            'provinceCode' => $customer_countrycode,
                            'addressLine1' => 'addres1',
                            'addressLine2' => 'addres2',
                            'addressLine3' => 'addres3',
                            'countyName' => $customer_cityname,
                        ),
                ),
            'accounts' =>
                array (
                    0 =>
                        array (
                            'typeCode' => 'shipper',
                            'number' => '318014863',
                        ),
                ),
            'productCode' => 'P',
            'localProductCode' => 'P',
            'valueAddedServices' =>
                array (
                    0 =>
                        array (
                            'serviceCode' => 'II',
                            'localServiceCode' => 'II',
                            'value' => 100,
                            'currency' => 'GBP',
                            'method' => 'cash',
                        ),
                ),
            'payerCountryCode' => 'CD',
            'plannedShippingDateAndTime' => '2022-02-18T13:00:00GMT+00:00',
            'unitOfMeasurement' => 'metric',
            'isCustomsDeclarable' => true,
            'monetaryAmount' =>
                array (
                    0 =>
                        array (
                            'typeCode' => 'declaredValue',
                            'value' => 100,
                            'currency' => 'GBP',
                        ),
                ),
            'requestAllValueAddedServices' => false,
            'returnStandardProductsOnly' => false,
            'nextBusinessDay' => false,
            'productTypeCode' => 'all',
            'packages' =>
                array (
                    0 =>
                        array (
                            'weight' => $product_weight,
                            'dimensions' =>
                                array (
                                    'length' => $product_length,
                                    'width' => $product_width,
                                    'height' => 15,
                                ),
                        ),
                ),
        );

        $res = $client->post($url,array(
            'json'=> $body
        ));

        $Returned_array = $res->getBody()->getContents();
        $Res1 = json_decode($Returned_array);

//        dd($Res1);

        foreach ($Res1->products as $product){
            foreach ($product->totalPrice as $TotalPrice){
                if($TotalPrice->priceCurrency == "USD"){
                    $return =  $TotalPrice->price;
                }
            }
        }

   return $return;
    }
}
