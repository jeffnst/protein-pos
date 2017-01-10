@extends('layouts.app')

@section('title')
    - Create Sales
@endsection

@section('content')
    @parent
    <div class="row" id="app" v-cloak>
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-body" id="search-product-panel">
                    <search-product
                            src="{{ route('products.xhr.search') }}"
                            :existing-items="cart"
                            v-on:product-selected="addToCart($event.inventory.product, 1, $event.availableQuantity)"
                            v-on:insufficient-stock="notify('error', $event.remark)"
                    ></search-product>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-body" id="products-panel">
                    <div v-show="isCartEmpty">
                        <span class="label label-primary">No items on cart</span>
                    </div>
                    <table class="table table-hover" v-show="!isCartEmpty">
                        <thead>
                            <tr class="register-items-header">
                                <th class="text-center"></th>
                                <th>Item Name</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Qty.</th>
                                <th class="text-center">Disc %</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(cartItem, index) in cart">
                                <tr>
                                    <td style="vertical-align: middle;" class="text-center">
                                        <a class="btn btn-xs text-danger" v-on:click="removeFromCart(index)">
                                            <i class="fa fa-times-circle fa-2x"></i>
                                        </a>
                                    </td>
                                    <td style="vertical-align: middle;">@{{ cartItem.product.name }}</td>
                                    <td style="vertical-align: middle;" class="text-center">@{{ cartItem.product.price }}</td>
                                    <td class="text-center" style="width: 70px; vertical-align: middle;">
                                        <input type="number" class="form-control" v-model="cartItem.quantity" min="0" v-bind:max="cartItem.availableQuantity" />
                                    </td>
                                    <td class="text-center" style="width: 70px; vertical-align: middle;">
                                        <input type="number" class="form-control" v-model="cartItem.discount" min="0" />
                                    </td>
                                    <td style="vertical-align: middle;" class="text-center">@{{ calculateItemPrice(cartItem) }}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td colspan="5">
                                        <table class="table table-condensed">
                                            <tr>
                                                <td><strong>Barcode</strong></td>
                                                <td>@{{ cartItem.product.barcode || "-" }}</td>
                                                <td><strong>Category</strong></td>
                                                <td>@{{ cartItem.product.category ? cartItem.product.category.name : "-" }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Brand</strong></td>
                                                <td>@{{ cartItem.product.brand ? cartItem.product.brand.name : "-" }}</td>
                                                <td><strong>Available Stock</strong></td>
                                                <td>@{{ cartItem.product.availableQuantity }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-body" id="search-customer-panel">
                    <search-customer src="{{ route('customers.xhr.search') }}" v-on:customer-selected="setCustomer($event.customer)" v-show="!isCustomerSelected"></search-customer>
                    <div class="customer-info" v-show="isCustomerSelected">
                        <div class="row">
                            <div class="col-xs-12">
                                <h4 class="name">
                                    @{{ customer.name }}
                                    <span class="label label-success" v-show="customer.group">
                                        <i class="fa fa-star"></i>
                                        @{{ customer.groupLabel }}
                                    </span>
                                </h4>
                                <div class="screen-name">
                                    <i class="fa fa-phone"></i> @{{ customer.phone || "-" }} &nbsp; <i class="fa fa-envelope"></i> @{{ customer.email }}
                                </div>
                            </div>
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-xs-6">
                                <button class="btn btn-primary btn-block">
                                    <i class="fa fa-search-plus"></i>
                                    Show details
                                </button>
                            </div>
                            <div class="col-xs-6">
                                <button class="btn btn-default btn-block" v-on:click="setCustomer({})">
                                    <i class="fa fa-times"></i>
                                    Change customer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-body" id="sales-summary-panel">
                    <table id="sales-summary-table" class="table">
                        <tbody>
                            <tr>
                                <td>Customer Discount:</td>
                                <td>
                                    @{{ customer.group ? customer.group.discount + "%" : "-" }}
                                </td>
                            </tr>
                            <tr>
                                <td>Sales Discount:</td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" class="form-control" v-model="salesDiscount" min="0" max="100" />
                                        <span class="input-group-addon">%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="success">
                                <td>Subtotal:</td>
                                <td>
                                    <strong>@{{ subTotal }}</strong>
                                </td>
                            </tr>
                            <tr class="separator"><td colspan="2">&nbsp;</td></tr>
                            <tr class="dashed" id="summary-grand-total">
                                <td colspan="2">
                                    <table class="table">
                                        <tr>
                                            <td style="width: 50%;">
                                                <h5 class="sales-info">Total</h5>
                                                <strong class="text-success">@{{ grandTotal }}</strong>
                                            </td>
                                            <td>
                                                <h5 class="sales-info">Change</h5>
                                                <strong class="text-warning">@{{ change }}</strong>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr class="dashed">
                                <td colspan="2">
                                    <h5 class="sales-info">Payment</h5>
                                    <br/>
                                    <div class="row">
                                        <div class="col-xs-offset-1 col-xs-5">
                                            <button type="button" class="btn" v-on:click="setPaymentMethod('cash')" v-bind:class="{ 'btn-success': payment.method === 'cash' }">Cash</button>
                                            <button type="button" class="btn" v-on:click="setPaymentMethod('credit_card')" v-bind:class="{ 'btn-success': payment.method === 'credit_card' }">Credit Card</button>
                                        </div>
                                        <div class="col-xs-6">
                                            <input type="number" name="payment_amount" v-model="payment.amount" class="form-control text-right" placeholder="Enter payment amount" min="0" v-show="payment.method === 'cash'" required />
                                            <input type="text" name="credit_card_number" v-model="payment.cardNumber" class="form-control text-right" placeholder="Enter credit card num." v-show="payment.method === 'credit_card'" required />
                                        </div>
                                    </div>
                                    <input type="hidden" name="payment_method" v-model="payment.method" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <button type="submit" class="btn btn-block btn-primary" v-bind:disabled="!isCompletable">Complete Sale</button>
                                </td>
                            </tr>
                            <tr><td colspan="2">&nbsp;</td></tr>
                            <tr class="dashed">
                                <td colspan="2">
                                    <h5 class="sales-info">Remark</h5>
                                    <br/>
                                    <textarea name="remark" class="form-control" placeholder="Additional info"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script type="text/javascript">
        const app = new Vue({
            el: "#app",
            data: {
                query: "",
                salesDiscount: 0,
                cart: [],
                customer: {},
                payment: {
                    method: 'cash',
                    amount: 0,
                    cardNumber: ''
                },
                taxes: {!!  json_encode($taxes)  !!}
            },
            watch: {
                cart: {
                    deep: true,
                    handler: function (oldItems, newItems) {
                        var $this = this;

                        newItems.forEach(function (item, index) {
                            if (item.quantity === 0) {
                                $this.removeFromCart(index);
                            }
                        });
                    }
                }
            },
            computed: {
                isCartEmpty: function () { return this.cart.length === 0; },
                isCustomerSelected: function () { return this.customer.hasOwnProperty('id'); },
                isPaymentCompleted: function () {
                    if (this.payment.method === 'cash') {
                        return this.payment.amount >= this.grandTotal;
                    } else if (this.payment.method === 'credit_card') {
                        return this.payment.cardNumber;
                    }
                },
                isCompletable: function () {
                    return this.isCartEmpty === false
                            && this.isCustomerSelected
                            && this.isPaymentCompleted;
                },
                subTotal: function () {
                    var itemsTotal = 0,
                        $this = this;

                    this.cart.forEach(function (cartItem) {
                        itemsTotal += $this.calculateItemPrice(cartItem);
                    });

                    if ($this.customer.group) {
                        itemsTotal = $this.applyDiscount(itemsTotal, this.customer.group.discount);
                    }

                    itemsTotal = $this.applyDiscount(itemsTotal, this.salesDiscount);

                    return itemsTotal;
                },
                grandTotal: function () {
                    return this.subTotal;
                },
                change: function () {
                    return Math.max(this.payment.amount - this.grandTotal, 0);
                }
            },
            methods: {
                applyDiscount: function (original, discount) { return original * (100 - discount) / 100; },
                calculateItemPrice: function (item) { return this.applyDiscount(item.product.price * item.quantity, item.discount); },
                setCustomer: function (customer) { this.customer = customer; },
                setPaymentMethod: function (paymentMethod) {
                    this.payment.method = paymentMethod;

                    if (this.payment.method === 'cash') {
                        this.payment.amount = 0;
                    } else {
                        this.payment.amount = this.grandTotal;
                    }
                },
                addToCart: function (product, quantity, availableQuantity) {
                    var sameProduct = false,
                        $this = this;

                    this.cart.forEach(function (cartItem) {
                        if (cartItem.product.id === product.id) {
                            if (cartItem.quantity + quantity > availableQuantity) {
                                $this.notify('error', 'Not enough stock');
                            } else {
                                cartItem.quantity += quantity;
                                sameProduct = true;
                            }
                        }
                    });

                    if (!sameProduct) {
                        product.availableQuantity = availableQuantity;

                        this.cart.push({
                            product: product,
                            quantity: quantity,
                            discount: 0
                        })
                    }
                },
                removeFromCart: function (index) { this.cart.splice(index, 1); },
                notify: function (type, message) {
                    var fn = window.toastr[type];

                    fn(message);
                }
            }
        });

        $(document).ready(function () {
            $(window).on("paste", function (e) {
                if ($(e.target).attr("id") === $queryBox.attr("id")) {
                    setTimeout(function () {
                        app.findByBarcode($queryBox.val());
                    }, 100);
                }
            });
        });
    </script>
@endsection