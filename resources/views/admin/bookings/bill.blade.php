@extends('layouts.main')
@section('content')
    <section>
        <div class="container">
            <div class="row">

                <div class="col-md-3 mb-5">
                    <div class="text-end">

                        <a target="_blank" href="{{ route('download_bill', $booking->id) }}"
                            class="btn btn-gradient">Download</a>
                        <a href="{{ route('bookingsview', 'upcoming') }}?booking_status=1" class="btn btn-gradient">Back</a>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="w-100" style="max-width: 800px;background:#ccc;margin: 0 auto;margin-left:auto;">
                        <table class="table table-sm table-bordered" style="background:#ccc;border:2px solid #ccc;">
                            <tbody>
                                <tr>
                                    <td colspan="4">
                                        <div style="margin-inline-start: 10px;margin-top:10px;">
                                            <img src="{{ url('public/images/logo.png') }}" style="width: 100px"
                                                alt="">

                                        </div>
                                    </td>
                                    <td colspan="4">
                                        <h2>
                                            {{ $studio->name }}
                                        </h2>

                                        <p>
                                            {{ $studio->address . ' , ' . $studio->district?->city . ' , ' . $studio->state?->state . ' , ' . $studio->country?->country . ' , ' . $studio->pincode }}
                                        </p>
                                    </td>
                                    <td colspan="4">
                                        <p style="margin-bottom: 10px"></p>
                                        <p class="mb-0">Phone No: +91-989285600</p>
                                        <p class="mb-0">Email: admin@rbstudios.info</p>
                                        <p class="mb-0">Website: rbstudios.info</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="12" style="text-align: center">
                                        <h4>Receipt</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8" style="text-align: center">
                                        <h4>Customer Details</h4>
                                    </td>
                                    <td colspan="4">
                                        <h4>Receipt Details</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8">


                                        <div class="w-100">
                                            <h5>
                                                <b>Name of Guest :</b>
                                                {{ $user->name }}
                                            </h5>
                                            <p>
                                                <b>Mobile : </b>
                                                {{ $user->mobile }}
                                            </p>
                                            <p>
                                                <b>Email : </b>
                                                {{ $user->email }}
                                            </p>
                                            <p>
                                                <b>GST Number : </b>
                                                {{ $booking->gst?->gst }}
                                            </p>
                                            <p>
                                                <b>Booking Status :</b>
                                                <span
                                                    class="badge p-2 {{ $booking->booking_status == '2' ? 'bg-danger' : 'bg-gradient' }}">
                                                    {{ $bstatus[$booking->booking_status] }}
                                                </span>

                                            </p>
                                        </div>
                                    </td>
                                    <td colspan="4">
                                        <div class="w-100">
                                            <p>
                                                <b>Date : </b> {{ date('d-M-Y') }}
                                            </p>
                                            <p>
                                                <b>Bill No : </b> {{ $booking->bill_no }}
                                            </p>

                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2">
                                        <div class="w-100" style="padding:10px;">
                                            Date
                                        </div>
                                    </td>
                                    <td colspan="4">
                                        <div class="w-100" style="padding:10px;">
                                            Description
                                        </div>
                                    </td>
                                    <td colspan="2">
                                        <div class="w-100" style="padding:10px;">
                                            Hours
                                        </div>
                                    </td>
                                    <td colspan="2">
                                        <div class="w-100" style="padding:10px;">
                                            Rate
                                        </div>
                                    </td>
                                    <td colspan="2">
                                        <div class="w-100" style="padding:10px;">
                                            Amount
                                        </div>
                                    </td>


                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="" style="padding:10px;">
                                            {{ date('d-M-Y', strtotime($booking->booking_start_date)) }}
                                        </div>
                                    </td>
                                    <td colspan="4">
                                        <div class="w-100" style="padding:10px;">
                                            {{-- <p>
                                                {{ $booking->studio_charge }}/hour X {{ $booking->duration }} hours =
                                            </p> --}}
                                            <p>
                                                {{ $booking->studio->name }}
                                            </p>
                                            <p>
                                                From {{ date('d-M-Y h:i A', strtotime($booking->booking_start_date)) }}
                                                to {{ date('d-M-Y h:i A', strtotime($booking->booking_end_date)) }}
                                            </p>
                                        </div>
                                    </td>
                                    <td colspan="2">
                                        <div class="w-00" style="padding:10px;">
                                            {{ $booking->duration }}
                                        </div>
                                    </td>
                                    <td colspan="2">
                                        <div class="w-00" style="padding:10px;">
                                            ₹ {{ $booking->studio_charge }}
                                        </div>
                                    </td>

                                    <td colspan="2">
                                        <div class="w-00" style="padding:10px;">
                                            ₹ {{ $booking->studio_charge * $booking->duration }}
                                        </div>
                                    </td>


                                </tr>
                                @php
                                    $arr = [];
                                @endphp
                                @foreach ($items as $item)
                                    <tr>
                                        <td colspan="2">
                                            <div class="" style="padding:10px;">
                                                {{ date('d-M-Y', strtotime($booking->booking_start_date)) }}</div>
                                        </td>
                                        <td colspan="4">
                                            <div class="w-100" style="padding:10px;">
                                                <p>

                                                    {{ $item->rents->name }}


                                                </p>
                                            </div>
                                        </td>
                                        <td colspan="2">
                                            <div class="w-00" style="padding:10px;">
                                                {{ $item->uses_hours }}

                                            </div>
                                        </td>
                                        <td colspan="2">
                                            <div class="w-00" style="padding:10px;">
                                                ₹ {{ $item->charge }}
                                            </div>
                                        </td>
                                        <td colspan="2">
                                            <div class="w-00" style="padding:10px;">
                                                {{ $item->charge * $item->uses_hours }}
                                                @php
                                                    array_push($arr, $item->charge * $item->uses_hours);
                                                @endphp
                                            </div>
                                        </td>

                                        <!--<td>-->
                                        <!--    <div class="d-flex gap-1">-->
                                        <!--        <a href="{{ route('booking_item.destroy', $item->id) }}" class="btn btn-soft-danger btn-sm border-danger">Delete</a>-->
                                        <!--    </div>-->
                                        <!--</td>-->
                                    </tr>
                                @endforeach
                                @php
                                    $crr = [];
                                @endphp
                                @foreach ($trans as $t)
                                    <tr>
                                        <td colspan="2">
                                            <div class="" style="padding:10px;">
                                                {{ date('d-M-Y', strtotime($t->created_at)) }}</div>
                                        </td>
                                        <td colspan="4">
                                            <div class="w-100" style="padding:10px;">
                                                <p>
                                                    <b>
                                                        Transaction Id : {{ $t->id }}
                                                    </b>

                                                </p>
                                            </div>
                                        </td>
                                        <td colspan="2">
                                            <div class="w-00" style="padding:10px;">

                                            </div>
                                        </td>
                                        <td colspan="2">

                                        </td>
                                        <td colspan="2">
                                            <div class="w-00" style="padding:10px;">
                                                ₹ {{ number_format($t->amount, 2) }}

                                                @php
                                                    array_push($crr, $t->amount);
                                                @endphp
                                            </div>
                                        </td>


                                    </tr>
                                @endforeach

                                <tr>
                                    <td colspan="6" style="text-align: right;">



                                        Extra Added Amount
                                    </td>
                                    <td colspan="6">
                                        ₹ {{ $extm =  $booking->extra_added_sum_amount ?? 0 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right;">


                                        Sub Total
                                    </td>
                                    <td colspan="6">
                                        ₹
                                        {{ $subt = array_sum($arr) + $booking->studio_charge * $booking->duration + $booking->extra_added_sum_amount }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right;">


                                        GST
                                    </td>
                                    <td colspan="6">
                                        ₹ {{ $gst = $subt * 0.18 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right;">


                                        Discount
                                    </td>
                                    <td colspan="6">
                                        {!! Form::open(['route' => ['booking.discount']]) !!}
                                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                        <div class="input-group">
                                            <input type="text" name="discount" id="discount"
                                                value="{{ $d = $booking->discount }}" class="form-control">
                                            <button class="btn btn-gradient">Submit</button>
                                        </div>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                                <tr>
                                     <td colspan="6" style="text-align: right;">
                                      Promo Discount
                                    </td>
                                      <td colspan="6">
                                        <span
                                            class="">₹{{ $booking->promo_discount_calculated }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right;">
                                        Payment Status
                                    </td>
                                    <td colspan="6">
                                        <span
                                            class="badge bg-gradient p-2">₹{{ $pstatus[$booking->payment_status] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right;">
                                        Balance Due
                                    </td>
                                    <td colspan="6">
                                        ₹ {{ $subt + $gst + $extm - array_sum($crr) - $d }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right;">
                                        <p>
                                            Certified that the particulars given above are true
                                            and correct. (E & O.E.)
                                        </p>
                                    </td>
                                    <td colspan="6">
                                        <p>
                                            This is computer-generated document
                                        </p>
                                        <p>
                                            No signature is required
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Item</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => ['booking_item.add', $booking->id]]) !!}
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <input type="hidden" name="studio_id" value="{{ $studio->id }}">
                    <div class="form-group mb-2">
                        <label for="">Select Item</label>
                        <select name="item_id" id="" class="form-select">
                            <option value="">---Select---</option>
                            @foreach ($ritems as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label for="">Enter Hours</label>
                        <input type="number" name="uses_hours" id="uses_hours" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <button class="btn w-100 btn-gradient rounded-pill">Add Item</button>
                    </div>
                    {!! Form::close() !!}
                </div>

            </div>
        </div>
    </div>
@endsection
