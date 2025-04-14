<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <title>Payment Page</title>
</head>

<body>

    <button id="rzp-button">Pay with Razorpay</button>

    <script>
        const options = {
            key: "{{ $razorpay_key }}",
            amount: "{{ $payment_value }}" * 100,
            currency: "INR",
            name: "{{ $booking->user->name }}",
            description: "{{ $booking->id . __(' payment page') }}",
            order_id: "{{ $goi }}",
            handler: function(response) {
                fetch("{{ route('paymentCallbackRazorpay') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(response)
                });
            }
        };
        const rzp = new Razorpay(options);
        rzp.open();
    </script>

</body>

</html>
