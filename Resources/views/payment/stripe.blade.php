<form action="{{route('stripe.post')}}" method="POST" id="formStripePost" >
    @csrf

    <input type="hidden" name="transaction_id"  id="transaction_id" value='{{$transaction->id}}'>


    <div id="btnStripe" style="display: none;" >
        <script
            src="https://checkout.stripe.com/checkout.js" id="payStripe"
            class="stripe-button"
            data-key="{{ env('STRIPE_KEY') }}"
            data-name="Sale Insightfull"
            data-description= "test"
            data-amount="{{$transaction->amount}}"
            data-currency="{{ $currency }}"
            data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
            data-close="alert('2')"
            data-locale="auto">
        </script>
    </div>
</form>
<script>
    /*
     $( document ).ready(function() {
        $("#btnStripe button").click()
        document.getElementById("myCheck").click()
     })
     */

     //document.addEventListener('DOMContentLoaded', (event) => {
        //let buttonstripe=document.querySelector("#btnStripe button")
        //buttonstripe.click()
        //console.log(divstripe)
    //})
    setTimeout(function(){
        let buttonstripe=document.querySelector("#btnStripe button")
        buttonstripe.click()

        $(document).on("DOMNodeRemoved",".stripe_checkout_app", function(){
           alert('close')
        });
        }, 3000);
</script>
