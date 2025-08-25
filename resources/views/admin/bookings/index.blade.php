@extends('layouts.main')
@section('content')
    <style>
        .disabledTr td {
            background: #ddd;
            cursor: none;
            pointer-events: none;
        }
    </style>
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <ul class="d-flex gap-1 align-items-center list-unstyled">
                        <li>
                            <a @class([
                                'btn btn-sm d-none',
                                'btn-primary ' => $type == 'today' ? true : false,
                            ])
                                href="{{ route('bookingsview', 'today') }}?booking_status=1&approved_at=approved">Today
                                Booking</a>
                        </li>
                        <li>
                            <a @class([
                                'btn btn-sm',
                                'btn-primary' => $type == 'upcoming' ? true : false,
                            ])
                                href="{{ route('bookingsview', 'upcoming') }}?booking_status=1&approved_at=approved">Current
                                Booking</a>
                        </li>
                        <li><a @class([
                            'btn btn-sm',
                            'btn-primary' => $type == 'past' ? true : false,
                        ])
                                href="{{ route('bookingsview', 'past') }}?booking_status=1&approved_at=approved">Past
                                Booking</a></li>
                    </ul>

                    <ul class="d-flex gap-1 align-items-center list-unstyled">
                        <li>
                            <strong> Booking Status : </strong>
                        </li>
                        @foreach ([0, 1, 2] as $s)
                            <li>
                                <a @class(['btn btn-sm', 'btn-success' => $booking_status == $s])
                                    href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['booking_status' => $s])) }}">
                                    {{ $s == 1 ? 'Confirmed' : ($s == 2 ? 'Cancelled' : 'Pending') }}
                                </a>
                            </li>
                        @endforeach

                    </ul>
                    <ul class="d-flex gap-1 align-items-center list-unstyled">
                        <li>
                            <strong> Service Approval :</strong>
                        </li>
                        @foreach (['pending', 'approved'] as $a)
                            <li>
                                <a @class([
                                    'btn btn-sm text-capitalize',
                                    'btn-info' => $approved_at == $a,
                                ])
                                    href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['approved_at' => $a])) }}">
                                    {{ $a }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <ul class="d-flex gap-1 align-items-center list-unstyled">
                        <li>
                            <strong> Payment :</strong>
                        </li>
                        @foreach (['paid', 'partial', 'unpaid'] as $a)
                            <li>
                                <a @class([
                                    'btn btn-sm text-capitalize',
                                    'btn-warning' => $payment_filter == $a,
                                ])
                                    href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['payment_filter' => $a])) }}">
                                    {{ $a }}
                                </a>
                            </li>
                        @endforeach
                    </ul>


                </div>
                <div class="col-md-8">
                    <div class="d-flex w-100 align-items-center">
                        <form action="" method="get" class="w-100">
                            <div class="row g-1">
                                <input type="hidden" name="booking_status" value="{{ $booking_status }}">
                                <input type="hidden" name="approved_at" value="{{ $approved_at }}">
                                <div class="col-md-2">
                                    <label for="">Studio</label>
                                    <select class="form-select form-select-sm" name="studio_id"
                                        onchange="getServiceByStudio(event)" id="studio_id">
                                        <option value="">All</option>
                                        @foreach ($studios as $std)
                                            <option value="{{ $std->id }}" @selected($std->id == $studio_id)>
                                                {{ $std->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="">Service</label>
                                    <select class="form-select form-select-sm" name="service_id" id="service_id">
                                        <option value="">All</option>
                                        @foreach ($services as $sv)
                                            <option value="{{ $sv->id }}" @selected($sv->id == $service_id)>
                                                {{ $sv->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="">User</label>
                                    <input type="text" name="keyword" id="" class="form-control form-control-sm"
                                        value="{{ $keyword }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="">Booking ID</label>
                                    <input type="text" name="booking_id" id=""
                                        class="form-control form-control-sm" value="{{ $booking_id }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="">Booking Date</label>
                                    <input type="date" id="booking_date_form" class="form-control form-control-sm"
                                        name="booking_date_form" value="{{ $bdf }}" id="">
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const bookingDateInput = document.getElementById('booking_date_form');
                                            const type = @json($type);
                                            const today = new Date();
                                            const yesterday = new Date(today);
                                            const tomorrow = new Date(today);
                                            yesterday.setDate(today.getDate() - 1);
                                            tomorrow.setDate(today.getDate() + 1);
                                            const formatDate = (date) => date.toISOString().split('T')[0];
                                            if (type === 'today') {
                                                bookingDateInput.min = formatDate(today);
                                                bookingDateInput.max = formatDate(today);
                                            } else if (type === 'past') {
                                                bookingDateInput.min = formatDate(yesterday);
                                                bookingDateInput.max = formatDate(today);
                                            } else if (type === 'upcoming') {
                                                bookingDateInput.min = formatDate(tomorrow);
                                            }
                                        });
                                    </script>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex mt-4 gap-1 align-items-center">

                                        <button class="btn  btn-sm btn-primary">
                                            <i class="fas fa-sliders-h"></i>
                                            Filter
                                        </button>
                                        <a href="{{ route('booking.create') }}" class="btn btn-sm btn-gradient">Add New</a>
                                        <a href="{{ route('bookingsview', ['slug' => $type] + request()->query() + ['export' => 'excel']) }}"
                                            class="btn btn-sm btn-info">
                                            Export
                                        </a>




                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
                <div class="col-md-12">

                    <div class="w-100 table-responsive">
                        <table class="table text-wrap table-warning  table-fixed table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>User</th>
                                    <th>Booking</th>
                                    <th>Studio</th>

                                    <th>Equipment Rental</th>
                                    <th>
                                        Payment Status
                                    </th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($bookings) == 0)
                                    <tr>
                                        <td colspan="7">
                                            <p class="text-center">
                                                No Booking found.
                                            </p>
                                        </td>
                                    </tr>
                                @endif
                                @foreach ($bookings as $i => $b)
                                    <tr class="@if ($b->booking_status == '2') disabledTr @endif">
                                        <td>
                                            {{ ++$i }}
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0">
                                                <li>
                                                    <b>Id :</b> {{ $b->id }}
                                                </li>
                                                <li>
                                                    <b>Name :</b> {{ $b->user?->name }}
                                                </li>
                                                <li>
                                                    <b>Email :</b> {{ $b->user?->email }}
                                                </li>
                                                <li>
                                                    <b>Mobile :</b> {{ $b->user?->mobile }}
                                                </li>
                                                <li>
                                                    <b>Created By :</b> {{ $b->creater?->name }}
                                                </li>
                                            </ul>
                                        </td>
                                        <td style="width:250px;">
                                            <ul class="mb-0 list-unstyled p-0 m-0">
                                                <li class="text-nowrap">
                                                    <b>Start Time :</b>
                                                    {{ date('d/m/Y : h:i A', strtotime($b->booking_start_date)) }}
                                                </li>
                                                <li>
                                                    <b>End Time :</b>
                                                    {{ date('d/m/Y : h:i A', strtotime($b->booking_end_date)) }}
                                                </li>
                                                <li>
                                                    <b>Duration :</b>
                                                    {{ $b->duration }} hours
                                                </li>
                                                <li>
                                                    <b>Created At</b> :
                                                    {{ $ct = date('d/m/Y-h:i A', strtotime($b->created_at)) }}
                                                </li>
                                                <li>
                                                    <b>Approved At</b> : {!! $b->approved_at
                                                        ? date('d/m/Y : h:i A', strtotime($b->approved_at))
                                                        : '<span class="badge bg-warning" > Pending </span>' !!}
                                                </li>
                                                @if (!$b->approved_at && $b->booking_status == '0')
                                                    <li>
                                                        <div class="d-flex gap-1">
                                                            <a href="{{ route('approve_booking', $b->id) }}"
                                                                class="btn btn-gradient btn-sm">Approved Now</a>
                                                            @if ($b->booking_status != '2')
                                                                {!! Form::open(['route' => ['booking.destroy', $b->id], 'method' => 'DELETE']) !!}
                                                                <button class="btn btn-danger btn-sm">Reject</button>
                                                                {!! Form::close() !!}
                                                            @endif
                                                        </div>

                                                    </li>
                                                @endif

                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0 text-nowrap">
                                                <li>
                                                    {{ $b->studio?->name }}
                                                </li>
                                                <li>
                                                    Service : {{ $b->service?->name }}
                                                </li>
                                            </ul>
                                        </td>

                                        <td>
                                            <table class="w-100 text-nowrap table-fixed">
                                                <thead>
                                                    <tr>
                                                        <td>
                                                            Item
                                                        </td>
                                                        <td>Charge</td>
                                                        <td>Hour</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($b->rents as $r)
                                                        <tr>
                                                            <td>
                                                                {{ $r->name }}
                                                            </td>
                                                            <td>
                                                                {{ $r->pivot->charge }}
                                                            </td>
                                                            <td>
                                                                {{ $r->pivot->uses_hours }}
                                                            </td>

                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                        </td>

                                        <td>
                                            @if ($b->booking_status == '0')
                                                <span class="badge bg-warning text-white">Pending</span>
                                                <a href="{{ route('confirm_booking', ['id' => $b->id]) }}"
                                                    class="btn btn-danger btn-sm">Confirm Now</a>
                                            @endif
                                            @if ($b->booking_status == '1')
                                                <span class="badge bg-success text-white">Confirm</span>
                                            @endif
                                            @if ($b->booking_status == '2')
                                                <span class="badge bg-danger text-white">Cancelled</span>
                                            @endif
                                            @if ($b->booking_status != '2')
                                                <ul class="list-unstyled text-nowrap">


                                                    <li>
                                                        Studio Charges : {{ $b->studio_charge_sum ?? 0 }}
                                                    </li>
                                                    <li>
                                                        Extra Charges : {{ $b->extra_added_sum_amount ?? 0 }}
                                                    </li>
                                                    <li>
                                                        Overnight Charges : {{ $b->extra_charge }}
                                                    </li>
                                                    <li>
                                                        rentcharge : {{ $b->rent_charges }}
                                                    </li>
                                                    <li>
                                                        Promo : {{ $b->promo_code }}
                                                    </li>
                                                    <li>
                                                        Promo Code Discount :
                                                        {{ floatval($b->promo_discount_calculated) }}
                                                    </li>

                                                    <li>
                                                        Admin Discount :
                                                        {{ floatval($b->discount) }}
                                                    </li>
                                                    <li>
                                                        Total Discount :
                                                        {{ $discount = $b->discount_total }}
                                                    </li>
                                                    <li>
                                                        GST : {{ $b->gst_sum }}
                                                    </li>
                                                    <li>
                                                        Total Amount : {{ $b->total_amount }}

                                                    </li>
                                                    <li>
                                                        Paid Amount : {{ $paid = $b->paid_sum }}
                                                    </li>
                                                    <li>
                                                        Remaining Amount :
                                                        {{ $remainingamount = floor($b->balance) }}
                                                    </li>
                                                    @if (floor($remainingamount) > 0 && $b->approved_at != null)
                                                        <li>
                                                            <div class="d-flex gap-2">
                                                                <form action="{{ route('pay_now_razorpay', $b->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <button class="btn btn-gradient btn-sm text-xs">Pay
                                                                        Online</button>
                                                                </form>
                                                                <button onclick="setbookingid({{ $b->id }})"
                                                                    class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                                    data-bs-target="#staticBackdrop">Add Payment</button>
                                                                <button class="btn btn-sm btn-outline-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#staticBackdropTransactions{{ $b->id }}">View
                                                                    Payments</button>
                                                                @include(
                                                                    'admin.bookings.TransactionsPopup',
                                                                    ['bid' => $b->id]
                                                                )

                                                            </div>


                                                        </li>
                                                    @endif
                                                </ul>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($b->booking_status != '2')
                                                <div class="d-flex w-full text-nowrap flex-wrap gap-2 align-items-center">
                                                    <button data-bs-toggle="modal"
                                                        data-bs-target="#staticBackdropdisgst{{ $b->id }}"
                                                        class="btn btn-sm btn-outline-success">GST Details</button>

                                                    <a target="_blank" href="{{ route('generate_bill', $b->id) }}"
                                                        class="btn btn-sm btn-gradient">Billing</a>
                                                    <button data-bs-toggle="modal"
                                                        data-bs-target="#staticBackdrop{{ $b->id }}{{ $b->studio->id }}"
                                                        class="btn btn-sm btn-primary">Add Item</button>
                                                    <button data-bs-toggle="modal"
                                                        data-bs-target="#staticBackdropRent{{ $b->id }}{{ $b->studio->id }}"
                                                        class="btn btn-sm btn-outline-info">Edit Item</button>
                                                    <button data-bs-toggle="modal"
                                                        data-bs-target="#staticBackdropExtra{{ $b->id }}"
                                                        class="btn btn-sm btn-primary">Add Extra Amount</button>
                                                    <button data-bs-toggle="modal"
                                                        data-bs-target="#staticBackdropdiscount{{ $b->id }}"
                                                        class="btn btn-sm btn-primary">Add Discount Amount</button>
                                                    <div class="dropdown inline-block">
                                                        <button class="btn btn-success  btn-sm dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                            <i class="fab fa-whatsapp"></i> Whatsapp
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @foreach (['confirm', 'payment', 'cancel', 'reschedule'] as $itm)
                                                                <li><button
                                                                        onclick="sendWhatsapp('{{ $itm }}', '{{ date('d/m/Y', strtotime($b->booking_start_date)) }}', '{{ date('h:i A', strtotime($b->booking_start_date)) }}', '{{ date('h:i A', strtotime($b->booking_end_date)) }}', '{{ $b->studio?->name }}', '{{ $remainingamount }}', {{ $b->duration }}, 'https://rbstudios.info/add-payment-online/{{ $b->id }}')"
                                                                        class="dropdown-item text-capitalize">{{ $itm }}
                                                                        Booking</button></li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <button
                                                        onclick="copyLink('https://rbstudios.info/add-payment-online/{{ $b->id }}')"
                                                        class="btn btn-sm btn-info">
                                                        <i class="far fa-copy"></i> Copy
                                                    </button>
                                                    <a class="btn btn-sm btn-soft-success"
                                                        href="{{ route('rebook', ['id' => $b['id']]) }}">
                                                        Book Again
                                                    </a>
                                                    @if ($b->booking_status != '2')
                                                        <a href="{{ route('booking.edit', $b->id) }}"
                                                            class="btn btn-warning btn-sm">Rescheule</a>
                                                        {!! Form::open([
                                                            'route' => ['booking.destroy', $b->id],
                                                            'method' => 'DELETE',
                                                            'onsubmit' => 'return confirmCancel()',
                                                        ]) !!}
                                                        <button type="submit"
                                                            class="btn btn-danger btn-sm">Cancel</button>
                                                        {!! Form::close() !!}

                                                        <script>
                                                            function confirmCancel() {
                                                                return confirm("Are you sure you want to cancel this booking?");
                                                            };
                                                        </script>
                                                    @endif
                                                    @if ($b->booking_status == '2')
                                                        <button class="btn btn-sm btn-danger">Add Refund</button>
                                                    @endif
                                                    @include('admin.bookings.booking_items', [
                                                        'bid' => $b->id,
                                                        'sid' => $b->studio->id,
                                                    ])
                                                    @include('admin.bookings.booking_items_edit', [
                                                        'bid' => $b->id,
                                                        'booking' => $b,
                                                    ])
                                                    @include('admin.bookings.ExtraAmontPopup', [
                                                        'bid' => $b->id,
                                                        'items' => $b->extra_added,
                                                    ])
                                                    @include('admin.bookings.DiscountPopup', [
                                                        'bid' => $b->id,
                                                        'discount' => $b->discount,
                                                    ])
                                                    @include('admin.bookings.editgst', [
                                                        'bid' => $b->id,
                                                        'dgst' => $b->gst,
                                                        'states' => $states,
                                                    ])
                                                </div>
                                            @endif

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $bookings->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-------------------===================Filter Offcanvas========================------------------->

    {{-- <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header bg-gradient text-white">
            <h5 class="offcanvas-title text-white" id="offcanvasExampleLabel">Filter</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="w-100">
                <form action="" method="GET">
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <label for="">Select Vendor</label>
                            <select class="form-select" onchange="getStudiosByVendor(event)" name="vendor_id">
                                <option value="">All</option>
                                @foreach ($vendors as $v)
                                    <option value="{{ $v->id }}" @selected($vendor_id == $v->id)>{{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="">Select Studio</label>
                            <select class="form-select" name="studio_id" onchange="getServiceByStudio(event)"
                                id="studio_id">
                                <option value="">All</option>
                                @foreach ($studios as $std)
                                    <option value="{{ $std->id }}" @selected($std->id == $studio_id)>{{ $std->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="">Select Service</label>
                            <select class="form-select" name="service_id" id="service_id">
                                <option value="">All</option>
                                @foreach ($services as $sv)
                                    <option value="{{ $sv->id }}" @selected($sv->id == $service_id)>{{ $sv->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <script>
                            const getStudiosByVendor = (e) => {
                                let vid = e.target.value;
                                $.post("{{ route('ajax_studios') }}", {
                                    vendor_id: vid
                                }, function(res) {
                                    $("#studio_id").html(res)
                                })
                            };
                            const getServiceByStudio = (e) => {
                                let vid = e.target.value;
                                $.post("{{ route('ajax_services') }}", {
                                    studio_id: vid
                                }, function(res) {
                                    $("#service_id").html(res)
                                })
                            };
                        </script>
                        <div class="col-md-6">
                            <label for="">Booking Date From</label>
                            <input type="datetime-local" onchange="setEndDate(event)" name="booking_date_form"
                                value="{{ $bdf }}" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label for="">Booking End Date</label>
                            <input type="datetime-local" name="booking_date_to" id="booking_date_to"
                                value="{{ $bdt }}" class="form-control" />
                        </div>
                        <script>
                            const setEndDate = (e) => {
                                const dtm = e.target.value;
                                $("#booking_date_to").attr('min', dtm);
                            };
                        </script>
                        <div class="col-md-6">
                            <label for="">Created By</label>
                            <select class="form-select" name="created_by">
                                <option value="">All</option>
                                <option value="1" @selected($created_by == '1')>Admin</option>
                                <option value="User" @selected($created_by == 'User')>User</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="">Duration</label>
                            <input type="text" name="duration" placeholder="Duration in hours"
                                value="{{ $duration }}" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label for="">Payment</label>
                            <select class="form-select" name="payment_status">
                                <option value="">All</option>
                                <option value="0">Pending</option>
                                <option value="1">Paid</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="">Booking St</label>
                            <select class="form-select" name="booking_status">
                                <option value="">All</option>
                                <option value="0">Pending</option>
                                <option value="1">Done</option>
                                <option value="2">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="">Approval</label>
                            <select class="form-select" name="approved_at">
                                <option value="">All</option>
                                <option value="0">Pending</option>
                                <option value="1">Done</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button class="btn w-100 bg-gradient text-white">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}
    <!-------------------------====================Filter Offcanvas End===================----------------------->








    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-gradient">
                    <h1 class="modal-title fs-5 text-white" id="staticBackdropLabel">Add Payment</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'transactions.store', 'method' => 'POST']) !!}
                    <input type="hidden" name="booking_id" id="booking_id">
                    <div class="form-group mb-2">
                        <label for="">
                            Enter Mode
                        </label>
                        <input type="text" name="mode" id="mode" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label for="">
                            Enter Transaction Id
                        </label>
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label for="">
                            Enter Amount
                        </label>
                        <input type="text" name="amount" id="amount" class="form-control">
                    </div>
                    <div class="form-group mb-4">
                        <label for="">Enter Date</label>
                        <input type="date" name="transaction_date" id="transaction_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <button class="btn w-100 btn-gradient">Submit</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>




    <script>
        const copyLink = (string) => {
            navigator.clipboard.writeText(string)
                .then(() => {
                    console.log('Link copied to clipboard!');
                    alert('Link copied successfully')
                })
                .catch(err => {
                    console.error('Could not copy text: ', err);
                });
        }
        const setbookingid = (id) => {
            $("#booking_id").val(id);
        };
    </script>
    <script>
        const sendWhatsapp = (type, date = null, stime = null, etime = null, name = null, amount = null, duration, link =
            null) => {
            let msg = "";
            if (type == "confirm") {
                msg =
                    `Hello you have booked the studio on ${date}  from ${stime} to ${etime}. Your session will be in ${name}. Total amount for ${duration} hours is ${amount} inclusive of all taxes. Please Note: This is a non-cancellable booking. Cancellation will incur a 50% charge. Setup and packup time will be added to your booking hours. See you at the studios !! R AND B STUDIOS`
            }
            if (type == "cancel") {
                msg =
                    `Hello Customer, on ${date} from ${stime} has been cancelled. Cancelation charge of 50% will be deducted. Any remaining balance will be returned to you via UPI within 24-48 hours. Hope to see you again at the studio. Thanks R AND B STUDIOS`
            }
            if (type == "payment") {
                msg =
                    `Hello, your payment of ${amount} including taxes is pending for your booking on ${date} from ${stime} for ${duration} hours . Request you to please use the following link ${link} or UPI the same on 9820996688. Thanks. R AND B STUDIOS`
            }
            if (type == "reschedule") {
                msg =
                    `Hello you have booked the studio on ${date}  from ${stime} to ${etime}. Your session will be in ${name}. Total amount for ${duration} hours is ${amount} inclusive of all taxes. Please Note: This is a non-cancellable booking. Cancellation will incur a 50% charge. Setup and packup time will be added to your booking hours. See you at the studios !! R AND B STUDIOS`
            }
            const encodedMsg = encodeURIComponent(msg);
            const whatsappNumber = '9820996688';
            const whatsappLink = `https://wa.me/${whatsappNumber}?text=${encodedMsg}`;
            window.open(whatsappLink, '_blank');

        }
    </script>
@endsection
