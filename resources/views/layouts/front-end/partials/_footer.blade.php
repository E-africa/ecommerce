<!-- Footer -->
<style>
    .page-footer {
        background: #fff !important;
        color: black;
    }

    .social-btn {
        border: 1px solid white;
        border-radius: 50%;
        height: 2rem;
        width: 2rem;
    }

    #button:hover {
        color: #ff3c20 !important;
        transform: scale(1.05);
        transition: all .2s ease-in-out;
    }

    #footer{
        background: black !important;
        width: 100%;
    }


    .social-btn i {
        line-height: 1.90rem;
    }

    .for-margin {
        margin-top: 10px;
    }

    .font-weight-bold {
        font-weight: 600 !important;
    }

    .font-weight-bold:hover{
        color: #ff3c20 !important;
        cursor: pointer;
    }

    .footer-heder {
        color: #FFFFFF;
    }

    .widget-list-link:hover {
        color: #ff3c20 !important;
    }

    .page-footer hr {
        border: 0.1px solid #2d3542;
    }

    .social-media :hover {
        color: {{$web_config['secondary_color']}}            !important;
    }
</style>

<footer class="page-footer font-small mdb-color pt-3 rtl">
    <!-- Footer Links -->
    <div class="container text-center" style="padding-bottom">

        <!-- Footer links -->
        <div
            class="row text-center {{Session::get('direction') === "rtl" ? 'text-md-right' : 'text-md-left'}} mt-3 pb-3">
            <!-- Grid column -->
            <div class="col-md-3 col-lg-3 col-xl-3 mt-3">
                <center>
                    <div class="text-nowrap mb-4">
                        <a class="d-inline-block mt-n1" href="{{route('home')}}">
                            <img width="250" style="height: 60px!important;"
                                 src="{{asset("storage/app/public/company/")}}/{{ $web_config['footer_logo']->value }}"
                                 onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                 alt="{{ $web_config['name']->value }}"/>
                        </a>
                    </div>

                    @php($social_media = \App\Model\SocialMedia::where('active_status', 1)->get())
                    @if(isset($social_media))
                        @foreach ($social_media as $item)
                            <span class="social-media">
                                <a class="social-btn sb-light sb-{{$item->name}} {{Session::get('direction') === "rtl" ? 'ml-2' : 'mr-2'}} mb-2"
                                   target="_blank" href="{{$item->link}}">
                                    <i class="{{$item->icon}} text-dark fa-2x" aria-hidden="true" id="button"></i>
                                </a>
                            </span>
                        @endforeach
                    @endif

                    <div class="widget mb-4 for-margin">
                        @php($ios = \App\CPU\Helpers::get_business_settings('download_app_apple_stroe'))
                        @php($android = \App\CPU\Helpers::get_business_settings('download_app_google_stroe'))

                        @if($ios['status'] || $android['status'])
                            <h6 class="text-uppercase font-weight-bold footer-heder text-dark">
                                {{\App\CPU\translate('download_our_app')}}
                            </h6>
                        @endif


                        <div class="store-contents" style="display: flex;max-width: 260px">
                            @if($ios['status'])
                                <div class="{{Session::get('direction') === "rtl" ? 'ml-2' : 'mr-2'}} mb-2 bg-dark">
                                    <a class="" href="{{ $ios['link'] }}" role="button"><img
                                            src="{{asset("public/assets/front-end/png/apple_app.png")}}"
                                            alt="">
                                    </a>
                                </div>
                            @endif

                            @if($android['status'])
                                <div class="{{Session::get('direction') === "rtl" ? 'ml-2' : 'mr-2'}} mb-2 bg-dark">
                                    <a href="{{ $android['link'] }}" role="button">
                                        <img src="{{asset("public/assets/front-end/png/google_app.png")}}"
                                             alt="">
                                    </a>
                                </div>
                            @endif
                        </div>

                    </div>
                </center>
            </div>
            <!-- Grid column -->

            <hr class="w-100 clearfix d-md-none">

            <!-- Grid column -->
            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h6 class="text-uppercase font-weight-bold footer-heder text-dark">{{\App\CPU\translate('special')}}</h6>

                <ul class="menu widget-list mt-2">
                    @php($flash_deals=\App\Model\FlashDeal::where(['status'=>1,'deal_type'=>'flash_deal'])->whereDate('start_date','<=',date('Y-m-d'))->whereDate('end_date','>=',date('Y-m-d'))->first())
                    @if(isset($flash_deals))
                        <li class="widget-list-item">
                            <a class="widget-list-link"
                               href="{{route('flash-deals',[$flash_deals['id']])}}">
                                {{\App\CPU\translate('flash_deal')}}
                            </a>
                        </li>
                    @endif
                    <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                    href="{{route('products',['data_from'=>'featured','page'=>1])}}">{{\App\CPU\translate('featured_products')}}</a>
                    </li>
                    <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                    href="{{route('products',['data_from'=>'latest','page'=>1])}}">{{\App\CPU\translate('latest_products')}}</a>
                    </li>
                    <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                    href="{{route('products',['data_from'=>'best-selling','page'=>1])}}">{{\App\CPU\translate('best_selling_product')}}</a>
                    </li>
                    <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                    href="{{route('products',['data_from'=>'top-rated','page'=>1])}}">{{\App\CPU\translate('top_rated_product')}}</a>
                    </li>

                    <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                    href="{{route('brands')}}">{{\App\CPU\translate('all_brand')}}</a>
                    </li>
                    <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                    href="{{route('categories')}}">{{\App\CPU\translate('all_category')}}</a>
                    </li>

                </ul>
            </div>
            <!-- Grid column -->

            <hr class="w-100 clearfix d-md-none">

            <!-- Grid column -->
            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h6 class="text-uppercase font-weight-bold footer-heder text-dark">{{\App\CPU\translate('account&shipping_info')}}</h6>

                @if(auth('customer')->check())
                    <ul class="widget-list mt-2">
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{route('user-account')}}">{{\App\CPU\translate('profile_info')}}</a>
                        </li>
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{route('wishlists')}}">{{\App\CPU\translate('wish_list')}}</a>
                        </li>
                        {{--<li class="widget-list-item">
                            <a class="widget-list-link"
                               href="{{route('customer.auth.login')}}">{{\App\CPU\translate('chat_with_seller_s')}}
                            </a>
                        </li>--}}
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{route('track-order.index')}}">{{\App\CPU\translate('track_order')}}</a>
                        </li>
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{ route('account-address') }}">{{\App\CPU\translate('address')}}</a>
                        </li>
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{ route('account-tickets') }}">{{\App\CPU\translate('support_ticket')}}</a>
                        </li>
                        {{--<li class="widget-list-item">
                            <a class="widget-list-link"
                               href="{{route('customer.auth.login')}}">{{\App\CPU\translate('tansction_history')}}
                            </a>
                        </li>--}}
                    </ul>
                @else
                    <ul class="widget-list mt-2">
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{route('customer.auth.login')}}">{{\App\CPU\translate('profile_info')}}</a>
                        </li>
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{route('customer.auth.login')}}">{{\App\CPU\translate('wish_list')}}</a>
                        </li>
                        {{--<li class="widget-list-item">
                            <a class="widget-list-link"
                               href="{{route('customer.auth.login')}}">{{\App\CPU\translate('chat_with_seller_s')}}
                            </a>
                        </li>--}}
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{route('track-order.index')}}">{{\App\CPU\translate('track_order')}}</a>
                        </li>
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{route('customer.auth.login')}}">{{\App\CPU\translate('address')}}</a>
                        </li>
                        <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                        href="{{route('customer.auth.login')}}">{{\App\CPU\translate('support_ticket')}}</a>
                        </li>
                        {{--to do--}}
                        {{--<li class="widget-list-item">
                            <a class="widget-list-link"
                               href="{{route('customer.auth.login')}}">{{\App\CPU\translate('tansction_history')}}
                            </a>
                        </li>--}}
                    </ul>
                @endif
            </div>

            <!-- Grid column -->
            <hr class="w-100 clearfix d-md-none">

            <!-- Grid column -->
            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h6 class="text-uppercase font-weight-bold footer-heder text-dark">{{\App\CPU\translate('about_us')}}</h6>


                <ul class="widget-list mt-2">
                    {{-- <p class="widget-list-item">{!! substr($web_config['about']->value,0,100) !!}</p> --}}
                    <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                    href="{{route('about-us')}}">{{\App\CPU\translate('about_company')}}</a>
                    </li>
                    <li class="widget-list-item"><a class="widget-list-link text-dark"
                                                    href="{{route('helpTopic')}}">{{\App\CPU\translate('faq')}}</a></li>
                    <li class="widget-list-item "><a class="widget-list-link text-dark"
                                                     href="{{route('terms')}}">{{\App\CPU\translate('terms_&_conditions')}}</a>

                    </li>

                    <li class="widget-list-item ">
                        <a class="widget-list-link text-dark href="{{route('privacy-policy')}}">
                        {{\App\CPU\translate('privacy_policy')}}
                        </a>
                    </li>
                    <li class="widget-list-item "><a class="widget-list-link text-dark"
                                                     href="{{route('contacts')}}">{{\App\CPU\translate('contact_us')}}</a>

                    </li>
                </ul>
            </div>
            <!-- Grid column -->
        </div>
        <!-- Footer links -->
    </div>

    <!-- Grid row -->
    <div class="container-fluid text-center" style="background: black !important;">
        <div class="row d-flex align-items-center footer-end block">
            <div class="col-lg-12 mt-3">
                <p class="text-center text-light" style="font-size: 16px; cursor:pointer;">{{ $web_config['copyright_text']->value }}</p>
            </div>
        </div>
        <!-- Grid row -->
    </div>
    <!-- Footer Links -->
</footer>
<!-- Footer -->
