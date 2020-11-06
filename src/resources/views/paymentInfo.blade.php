<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" media="screen" href="/css/app.css" />
        <style>
            .success{border: solid 1px blue;}
            .error {border:solid 1px red;}
        </style>
        <script src="/js/app.js"></script>
    </head>
    <body>
        <div class="container-fluid">
            
            <div class="row">
                <div class="form-order col-md-9">
                    <div class="container">
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        @if (session('alert'))
                        <div class="alert alert-danger">
                            {!! session('alert') !!}
                        </div>
                        @endif
                        <ul id="progressbar">
                            <li id="account">01 THÔNG TIN KHÁCH HÀNG</li>
                            <li class="active" id="personal">02 THÔNG TIN THANH TOÁN</li>
                        </ul>
                            <fieldset>
                            <form id="form_checkout" action="{{ route('order.create') }}" method="POST">
                                @csrf
                                <div class="form-card">
                                    <h3>Phương thức thanh toán</h3>
                                    <div class="row mt-4">
                                        <div class="checkbx col-12">
                                            <label class="cbx">
                                                <div>
                                                    <div class="cbx-width d-flex justify-content-between">
                                                        <div>Thanh toán khi nhận hàng</div>
                                                    </div>
                                                    <div><p>Thời gian nhận hàng từ 7 đến 14 ngày</p></div>
                                                </div>
                                                <input checked type="radio" value="1" name="payment_method">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="checkbx col-12">
                                            <label class="cbx">
                                                <div>
                                                    <div class="cbx-width d-flex justify-content-between">
                                                        <div>Thanh toán online (VNPay)</div>
                                                    </div>
                                                    <p>Thời gian nhận hàng từ 4 đến 7 ngày</p>
                                                </div>
                                                <input type="radio" value="2" name="payment_method">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                {{-- <a href="{{route("order.back")}}"> <i class="fa fa-arrow-left icon-back" aria-hidden="true"></i><input type="button" name="previous" class="previous action-button-previous" value="Quay lại" /></a> --}}
                                <i class="fa fa-arrow-left icon-back" aria-hidden="true"></i><input onclick="goBack()" type="button" name="previous" class="previous action-button-previous" value="Quay lại" />
                                <button type="button" class="action-button col-12 col-md-3" data-toggle="modal" data-target="#confirmModal">Thanh toán</button>
                            </fieldset>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 background">
                        <div class="row ">
                            <div class="product-info">
                                <div class="product-head d-flex justify-content-between">
                                    <h4>Giỏ hàng</h4>
                                    <div class="value d-flex align-items-center">
                                        <div class="circle d-flex align-items-center justify-content-center">{!! $cartItemsCount !!}</div>
                                    </div>
                                </div>
                                @foreach ($carts->cartItems as $cartItem)
                                <div class="product-item row">
                                    <div class="col-12 col-md-12 col-lg-6">
                                        <div class="img "><img src="{!! $cartItem->product->thumbnail !!}" width="100%" alt=""></div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-6">
                                        <div class="product-name">{!! $cartItem->product->name !!}</div>
                                        <div class="product-price">Đơn giá : {!! number_format($cartItem->price) !!} đ
                                        </div>
                                        <div class="product-price">Số lượng : {!! $cartItem->quantity !!}</div>
                                    </div>
                                </div>
                                @endforeach
                                <div class="total-price">
                                    <div class="d-flex justify-content-between">
                                        <div>Tổng giá</div>
                                        <div>{!! number_format($carts->total) !!} đ</div>
                                        <input type="" name="total" value="{!! $carts->total !!}" hidden >
                                    </div>
                                </div>
                                <div>
                                    <input type="" name="cart_id" value="{{ Cookie::get('cart') }}" hidden>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="confirmModal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content mt-5">
                            <div class="modal-body mb-5">
                                <h4 class="text-center mt-3">Xác nhận thanh toán hóa đơn ?</h4>
                                <div class="d-flex justify-content-center mt-5">
                                    <input type="submit" class="btn btn-primary col-12 col-md-3"  value="Thanh toán !" />
                                <button type="button" class="btn btn-danger ml-5" data-dismiss="modal">Quay lại</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </body>

<script>
function goBack() {
  window.history.back();
}
</script>

</html>
