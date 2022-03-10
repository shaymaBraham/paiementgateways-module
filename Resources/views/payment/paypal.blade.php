<div id="lient"></div>
<script>
    $( document ).ready(function() {
      @if($linkp!='')


            let base="{{ route('paypal.express-checkout') }}"
            let payLink;
            let url=base+'?transaction_id={{$linkp}}'
            /*
             payLink = document.createElement("a");

             payLink.href = url;

             payLink.style.display = "block";
             document.body.appendChild(payLink);
                 $("#lient").html( url)
             payLink.click();

             */
             window.location.replace(url)

    @endif
    })
</script>
