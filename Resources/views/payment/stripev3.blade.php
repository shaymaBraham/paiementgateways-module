
<!--<button id="checkout-button">Checkout</button>-->

<script type="text/javascript">

    function addScript( src,callback) {
    var s = document.createElement( 'script' );
    s.setAttribute( 'src', src );
    s.onload=callback;
    document.body.appendChild( s );
    }

    scriptload=function()
    {
        let stripe = Stripe("{{ config('paiementgateways.stripe.STRIPE_KEY')}}");
        fetch("{{route('stripe.postv3')}}", {
            method: "POST",
            headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json, text-plain, */*",
                    "X-Requested-With": "XMLHttpRequest"

            },
            body: JSON.stringify({
                    transaction_id: {{$transaction->id}}
                })
            })
            .then(function (response) {
                console.log('response',response)
            return response.json();
            })
            .then(function (session) {
                console.log('session',session)
                return stripe.redirectToCheckout({ sessionId: session.id });
            })
            .then(function (result) {
                // If redirectToCheckout fails due to a browser or network
                // error, you should display the localized error message to your
                // customer using error.message.
                if (result.error) {
                    alert(result.error.message);
                }
            })
            .catch(function (error) {
            console.error("Error:", error);
            });

    }

    setTimeout(function(){
        addScript('https://js.stripe.com/v3/',scriptload)
    }, 3000);
  </script>
