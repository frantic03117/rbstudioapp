<!DOCTYPE html>
<html>

<head>
    <title>Laravel 10 Generate PDF Example - ItSolutionStuff.com</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="w-100" style="max-width: 800px;margin:0 auto;background:#fff;">
                        <table class="table table-sm table-bordered" style="background:#fff;border:2px solid #ccc;">
                            <tbody>
                                <tr>
                                    <td colspan="8" style="border: 1px solid #ccc;padding:5px;">
                                        <h2>
                                            {{ $studio->name }}
                                        </h2>
                                        <p>
                                            {{ $studio->address . ' , ' . $studio->district?->city . ' , ' .
                                            $studio->state?->state . ' , ' . $studio->country?->country . ' , ' .
                                            $studio->pincode }}
                                        </p>

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
                                        </div>
                                    </td>
                                    <td colspan="4" style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100">
                                            <p>
                                                <b>Date : </b> {{ date('d-M-Y') }}
                                            </p>
                                            <p>
                                                <b>Bill No : </b> {{ $booking->bill_no }}
                                            </p>
                                            <p>
                                                <b>No of Persons :</b> 10
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background: #ddd;padding:10px;">
                                            check In Date
                                        </div>
                                    </td>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background: #ddd;padding:10px;">
                                            check Out Date
                                        </div>
                                    </td>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background: #ddd;padding:10px;">
                                            Payment Thorough
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100">
                                            {{ date('d-M-Y', strtotime($booking->booking_start_date)) }}
                                        </div>
                                    </td>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100">
                                            {{ date('d-M-Y', strtotime($booking->booking_end_date)) }}
                                        </div>
                                    </td>
                                    <td colspan="4" rowspan="3"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="text-center">
                                            Cash
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background: #ddd;padding:10px;">
                                            check In Time
                                        </div>
                                    </td>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background: #ddd;padding:10px;">
                                            check Out Time
                                        </div>
                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100">
                                            {{ date('h:i A', strtotime($booking->booking_start_date)) }}
                                        </div>
                                    </td>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100">
                                            {{ date('h:i A', strtotime($booking->booking_end_date)) }}
                                        </div>
                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="12">
                                        <div class="w" style="width: 100%;padding:10px;"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background:#ddd;padding:10px;">
                                            Date
                                        </div>
                                    </td>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background:#ddd;padding:10px;">
                                            Description
                                        </div>
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background:#ddd;padding:10px;">
                                            Charges
                                        </div>
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background:#ddd;padding:10px;">
                                            Credit
                                        </div>
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="background:#ddd;padding:10px;">
                                            Balance
                                        </div>
                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="" style="padding:10px;">{{date('d-M-Y')}}</div>
                                    </td>
                                    <td colspan="4"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-100" style="padding:10px;">
                                            <p>
                                                {{$booking->studio_charge}}/hour X {{$booking->duration}} hours =
                                            </p>
                                        </div>
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-00" style="padding:10px;">
                                            {{$booking->studio_charge*$booking->duration}}
                                        </div>
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        <div class="w-00" style="padding:10px;"> </div>
                                    </td>
                                    <td>
                                        {{$booking->studio_charge*$booking->duration}}
                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="12">
                                        <div class="w" style="width: 100%;padding:10px;"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8">

                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        Sub Total
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        14000
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8">

                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        GST
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        1000
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8">

                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        Discount
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        1000
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8">

                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        Status
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        Unpaid
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8">

                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        Net Balance
                                    </td>
                                    <td colspan="2"style="border: 1px solid #ccc;padding:5px;">
                                        0
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>