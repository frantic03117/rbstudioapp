<!DOCTYPE html>
<html>

<head>
    <title>Bill</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        .btn-gradient {
            background-image: linear-gradient(to bottom, #5A296B, #B92D53);
            color: #fff !important;
            outline: none !important;
            box-shadow: none;
        }

        .btn-gradient:hover,
        .btn-gradient:active {
            color: #fff !important;
            box-shadow: none;
            outline: none !important;
        }

        .bg-gradient {
            background-image: linear-gradient(to bottom, #5A296B, #B92D53) !important;
            color: #fff;
        }

        table tr td {
            font-size: 14px !important;

        }

        table tr.no-border td {

            border-color: #fff !important;
        }

        table tr td p,
        table tr td h4 {
            font-size: 14px !important;
        }
    </style>
</head>

<body>
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8 mb-5">
                    <div class="text-end d-flex justify-content-end gap-2 gap-x-2 gx-1">

                        <button onclick="printPdfBill()" class="btn btn-gradient me-2">Download</button>
                        <span class="mx-2"></span>
                        <a href="{{ route('bookingsview', 'upcoming') }}?booking_status=1"
                            class="btn btn-gradient">Back</a>
                    </div>
                </div>
                <div class="col-md-12">

                    {{-- <button onclick="generatePDF()">Download PDF</button> --}}
                    <div class="w-100" style="max-width: 800px;margin:0 auto;background:#fff;">
                        <div id="invoice" class="w-100"
                            style="max-width: 800px;background:#fff;margin: 0 auto;margin-left:auto;">
                            <table border="0" cellpadding="20" class="table table-sm table-bordered"
                                style="background:#fff;border:none;table-layout:fixed;">
                                <tbody>
                                    <tr class="no-border">
                                        <td colspan="4">
                                            <div style="margin-inline-start: 10px;margin-top:10px;">
                                                <img src="{{ url('public/images/logo.png') }}" style="width: 100px"
                                                    alt="">

                                            </div>
                                        </td>
                                        <td colspan="4">
                                            <h2 class="mb-0 " style="font-size:16px;">
                                                R & B Studio
                                            </h2>

                                            <p class="mb-0">
                                                905 B Wing, Venus Tower, Veera Desai Road, Azad Nagar
                                                <br />
                                                Mumbai - 400052
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
                                        <td colspan="12" class="p-0 m-0">

                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="12" style="text-align: center;font-size:14px;">
                                            <h4>Receipt</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" style="text-align: center;font-size:14px;">
                                            <h4>Customer Details</h4>
                                        </td>
                                        <td colspan="5" style="font-size:14px;">
                                            <h4>Receipt Details</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7">


                                            <div class="w-100">

                                                <h5 style="font-size: 14px;" class="mb-0">
                                                    <b>Name :</b>

                                                    @if ($booking->gst?->company)
                                                        {{ $booking->gst?->company }}
                                                    @else
                                                        {{ $user->name }}
                                                    @endif

                                                </h5>
                                                @if ($user?->gst)
                                                    <p class="mb-0">
                                                        <b>Address : </b>

                                                        {{ $user?->gst?->address }} {{ $user?->gst?->pincode }}
                                                    </p>
                                                @endif
                                                <p class="mb-0">
                                                    <b>Mobile : </b>
                                                    {{ $user->mobile }}
                                                </p>
                                                <p class="mb-0">
                                                    <b>Email : </b>
                                                    {{ $user->email }}
                                                </p>
                                                <p class="mb-0">
                                                    <b>GST Number : </b>
                                                    {{ $booking->gst?->gst }}
                                                </p>
                                                <p class="mb-0">
                                                    <b>Artist/Band : </b>
                                                    {{ $booking?->artist }}
                                                </p>

                                            </div>
                                        </td>
                                        <td colspan="5">
                                            <div class="w-100">
                                                <p class="mb-0">
                                                    <b>Date : </b> {{ date('d-M-Y') }}
                                                </p>
                                                <p class="mb-0">
                                                    <b>Bill No : </b> {{ $booking->bill_no }}
                                                </p>

                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="2">
                                            <div style="padding:10px;width:40px;">
                                                Date
                                            </div>
                                        </td>
                                        <td colspan="5">
                                            <div style="padding:10px;width:400px;flex:1">
                                                Description
                                            </div>
                                        </td>
                                        <td colspan="1">
                                            <div style="padding:10px;width:50px;">
                                                Hours
                                            </div>
                                        </td>
                                        <td colspan="2">
                                            <div style="padding:10px;width:50px;">
                                                Rate
                                            </div>
                                        </td>
                                        <td colspan="2">
                                            <div style="padding:10px;width:50px;">
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
                                        <td colspan="5">
                                            <div class="w-100" style="padding:10px;">
                                                {{-- <p>
                                                {{ $booking->studio_charge }}/hour X {{ $booking->duration }} hours =
                                            </p> --}}
                                                <p class="mb-0">
                                                    {{ $booking->studio->name }} {{ $booking->duration }} hours,
                                                    {{ $booking->service?->name }}
                                                </p>
                                            </div>
                                        </td>
                                        <td>
                                            <div c style="padding:10px;width:80px;">
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
                                            <td colspan="5">
                                                <div class="w-100" style="padding:10px;">
                                                    <p>

                                                        {{ $item->rents->name }}


                                                    </p>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="padding:10px;">
                                                    {{ $item->uses_hours }}

                                                </div>
                                            </td>
                                            <td colspan="2">
                                                <div style="padding:10px;">
                                                    ₹ {{ $item->charge }}
                                                </div>
                                            </td>
                                            <td colspan="2">
                                                <div style="padding:10px;">
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
                                            <td colspan="5">
                                                <div class="w-100" style="padding:10px;">
                                                    <p>
                                                        {{ $t->type }}
                                                    </p>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="padding:10px;">

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
                                        <td colspan="8" style="text-align: right;">
                                            Amount
                                        </td>
                                        <td colspan="4" style="text-align: right;">
                                            ₹
                                            {{ $subt = array_sum($arr) + $booking->studio_charge * $booking->duration }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="8" style="text-align: right;">
                                            Extra Amount
                                        </td>
                                        <td colspan="4" style="text-align: right;">
                                            ₹ {{ $booking->extra_added_sum_amount ?? 0 }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" style="text-align: right;">
                                            Sub Total
                                        </td>
                                        <td colspan="4" style="text-align: right;">
                                            ₹
                                            {{ $subt = array_sum($arr) + $booking->studio_charge * $booking->duration + $booking->extra_added_sum_amount }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" style="text-align: right;">
                                            Discount
                                        </td>
                                        <td colspan="4" style="text-align: right;">
                                            {{ $d = $booking->discount + $booking->promo_discount_calculated }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" style="text-align: right;">
                                            Net Amount
                                        </td>
                                        <td colspan="4" style="text-align: right;">
                                            ₹ {{ $netremain = $subt - $d }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="8" style="text-align: right;">
                                            GST
                                        </td>
                                        <td colspan="4" style="text-align: right;">
                                            ₹ {{ $gst = $netremain * 0.18 }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="8" style="text-align: right;">
                                            Advance
                                        </td>
                                        <td colspan="4" style="text-align: right;">
                                            ₹ {{ number_format(array_sum($crr), 2) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="8" style="text-align: right;">
                                            Balance
                                        </td>
                                        <td colspan="4" style="text-align: right;">
                                            ₹ {{ number_format($netremain + $gst - array_sum($crr), 2) }}

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
                    <script>
                        function printPdfBill() {
                            const element = document.getElementById("invoice");

                            html2pdf()
                                .set({
                                    margin: 0.2,
                                    filename: 'invoice.pdf',
                                    image: {
                                        type: 'jpeg',
                                        quality: 1
                                    },
                                    html2canvas: {
                                        scale: 1
                                    },
                                    jsPDF: {
                                        unit: 'in',
                                        format: 'a4',
                                        orientation: 'portrait'
                                    }
                                })
                                .from(element)
                                .save();
                        };
                    </script>
                </div>
            </div>
        </div>
    </section>
</body>

</html>
