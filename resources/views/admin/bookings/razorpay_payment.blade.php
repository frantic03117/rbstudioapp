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

    <button id="rzp-button" class="d-none" style="opacity:0;">Pay with Razorpay</button>

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
                    }).then(res => res.json())
                    .then(data => {
                        window.location.href = "{{ route('success_page', ['id' => $custom_order_id]) }}"
                    })
                    .catch(error => {
                        console.error('Payment callback failed:', error);
                        alert("Something went wrong. Please contact support.");
                    });
            }
        };
        const rzp = new Razorpay(options);
        rzp.open();
    </script>

</body>

</html>
