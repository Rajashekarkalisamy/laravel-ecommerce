<?php $paypalStandard = app('Webkul\Paypal\Payment\Standard');
$paymentData = collect($paypalStandard->getFormFields());
?>

<body data-gr-c-s-loaded="true" cz-shortcut-listen="true">
    <span id="loader-message"> Please wait Razor pay popup will be open in few seconds...
    </span>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <form name='razorpayform' action="{{ $paymentData->get('return') }}" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    </form>
    <script>
        var options = {
            "key": "{{ env('RAZORPAY_KEY') }}",
            "amount": "{{ $paymentData->get('amount') * 100 }}",
            "name": "Aark Tech",
            "description": "Razorpay payment",
            "image": "/images/logo-icon.png",
            "prefill": {
                "name": "{{ $paymentData->get('first_name') }}",
                "email": "{{ $paymentData->get('email') }}",
            },
            "theme": {
                "color": "#F37254"
            }
        };
        options.handler = function(response) {
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.razorpayform.submit();
        };
        options.theme.image_padding = false;
        options.modal = {
            ondismiss: function() {
                window.location.href = "{{$paymentData->get('cancel') }}"
            },
            escape: true,
            backdropclose: false
        };
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function(response) {
            // alert(response.error.code);
            // alert(response.error.description);
            // alert(response.error.source);
            // alert(response.error.step);
            // alert(response.error.reason);
            // alert(response.error.metadata.order_id);
            // alert(response.error.metadata.payment_id);
        });
        document.getElementById("loader-message").style.display = 'none';
        rzp1.open();
    </script>

</body>