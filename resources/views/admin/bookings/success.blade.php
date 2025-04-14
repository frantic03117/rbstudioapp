<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        .keycustom {
            font-weight: 400;
            letter-spacing: 2px;
            font-size: 12px;
            position: relative;
            min-width: 50%;
            display: inline-block;
        }

        .keycustom::before {
            content: ":";
            position: absolute;
            right: -5px;
            top: 0;
        }

        .valuecustom {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .tableborder {

            position: relative;
            background: #fffacc;
            border-radius: 20px;
            box-shadow: -1px 8px 10px #b2b1b1;
            padding: 10px;
        }

        .tableborder tr td {
            background: transparent;
            padding: 3px 25px;
        }

        .tableborder::before {
            content: "";
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: -20px;
            width: 40px;
            height: 40px;
            background: #fff;
            z-index: 9;
            border-radius: 50%;
        }

        .tableborder::after {
            content: "";
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: -20px;
            width: 40px;
            height: 40px;
            background: #fff;
            z-index: 9;
            border-radius: 50%;
        }
    </style>

</head>

<body>

    <section>
        <div class="container">
            <div class="row  justify-content-center">
                <div class="col-md-4">
                    <div class="w-100 vh-100 d-flex align-items-center  rounded-5 bg-emerald-500">
                        <table class="table table-borderless table-sm tableborder">
                            <tbody>
                                @php
                                    $orderstatus = json_decode($transaction->ret_resp);
                                @endphp
                                <tr>
                                    <td>
                                        <span class="keycustom">
                                            Transaction Id
                                        </span>

                                    </td>
                                    <td>
                                        <span class="valuecustom">
                                            {{ $orderstatus->id }}
                                        </span>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="keycustom">
                                            Order Id
                                        </span>

                                    </td>
                                    <td>
                                        <span class="valuecustom">
                                            {{ $orderstatus->receipt }}
                                        </span>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="keycustom">
                                            Transaction Date
                                        </span>

                                    </td>
                                    <td>
                                        <span class="valuecustom">
                                            {{ $transaction->updated_at }}
                                        </span>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="keycustom">
                                            Transaction amount
                                        </span>

                                    </td>
                                    <td>
                                        <span class="valuecustom">
                                            {{ $transaction->amount }}
                                        </span>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="keycustom">
                                            Currency
                                        </span>

                                    </td>
                                    <td>
                                        <span class="valuecustom">
                                            {{ $orderstatus->currency }}
                                        </span>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="keycustom">
                                            Payment Status
                                        </span>

                                    </td>
                                    <td>
                                        <span class="valuecustom">
                                            {{ $transaction->status }}
                                        </span>

                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
