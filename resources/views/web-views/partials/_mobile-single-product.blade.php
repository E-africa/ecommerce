@php($overallRating = \App\CPU\ProductManager::get_overall_rating($product->reviews))
<div class="product-card ml-5 card
 {{$product['current_stock']==0?'stock-card':''}}"
     style="width: 15rem; height: 22rem;">
    @if($product['current_stock']<=0)
        <label style="left: 29%!important; top: 29%!important;" class="badge badge-danger stock-out">Stock Out</label>
    @endif

    <div class="card-header inline_product clickable ali" style="cursor: pointer;" id="mobile">
        @if($product->discount > 0)
            <div class="d-flex justify-content-center for-dicount-div discount-hed" style="right: 0;position: absolute">
                    <span class="for-discoutn-value text-light" style="background-color: #ff3c20 !important;">
                    @if ($product->discount_type == 'percent')
                            {{round($product->discount,2)}}%
                        @elseif($product->discount_type =='flat')
                            {{\App\CPU\Helpers::currency_converter($product->discount)}}
                        @endif
                        OFF
                    </span>
            </div>
        @else
            <div class="d-flex justify-content-end for-dicount-div-null">
                <span class="for-discoutn-value-null"></span>
            </div>
        @endif
        <div class="d-flex align-items-center justify-content-center d-block" >
            <a href="{{route('product',$product->slug)}}" class=" rounded">
                <img src="{{\App\CPU\ProductManager::product_image_path('thumbnail')}}/{{$product['thumbnail']}}"
                     onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                     style="width: 100%;height: 100%; object-fit:cover: !important">
            </a>
        </div>
    </div>

    <div class="card-body inline_product text-center p-1 clickable"
         style="cursor: pointer; height:5.5rem; max-height: 5.5rem">
        <div class="rating-show">
            <span class=" font-size-sm text-body">
                @for($inc=0;$inc<5;$inc++)
                    @if($inc<$overallRating[0])
                        <i class="sr-star czi-star-filled active"></i>
                    @else
                        <i class="sr-star czi-star"></i>
                    @endif
                @endfor
                <label class="badge-style">( {{$product->reviews()->count()}} )</label>
            </span>
        </div>
        <div style="position: relative; font-size: 15px !important;" class="product-title1 text-dark">
            <a href="{{route('product',$product->slug)}}">
                {{ Str::limit($product['name'], 30) }}
                <!-- <span style="display: block; height: auto; width: auto; !important">
                    <img src="https://flagcdn.com/48x36/{{$product->seller->country}}.png" alt="..." width="5px !important" length="1px !important"/>
                </span> -->
            </a>

        </div>
        <div class="justify-content-between text-center">
            <div class="product-price text-center">
                @if($product->discount > 0)
                    <strike style="font-size: 12px!important;color: grey!important;">
                        {{\App\CPU\Helpers::currency_converter($product->unit_price)}}

                    </strike>
                    <br>

                @endif
                <span class="text-accent" style="color: #ff3c20 !important; font-size: 15px!important;">
                    {{\App\CPU\Helpers::currency_converter(
                        $product->unit_price-(\App\CPU\Helpers::get_product_discount($product,$product->unit_price))

                    )}}
                </span>

            </div>
        </div>
    </div>

    <div class="card-body card-body-hidden" style="padding-bottom: 5px!important;">
        <div class="text-center">
            @if(Request::is('product/*'))
                <a class="btn btn-dark btn-sm btn-block mb-2" href="{{route('product',$product->slug)}}">
                    <i class="czi-forward align-middle {{Session::get('direction') === "rtl" ? 'ml-1' : 'mr-1'}}" style="color: #ff3c20 !important;"></i>
                    {{\App\CPU\translate('View')}}
                </a>
            @else
                <a class="btn btn-dark btn-sm btn-block mb-2" href="javascript:"
                   onclick="quickView('{{$product->id}}')">
                    <i class="czi-eye align-middle {{Session::get('direction') === "rtl" ? 'ml-1' : 'mr-1'}}"></i>
                    {{\App\CPU\translate('Quick')}}   {{\App\CPU\translate('View')}}
                </a>
            @endif
        </div>
    </div>
</div>