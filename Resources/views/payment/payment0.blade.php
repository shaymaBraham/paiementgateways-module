<div class="container">
    <div class="row">
        <div class="col-xl-9 mx-auto">
            <h1 class="mb-5">
                Paiement
            </h1>
        </div>
    </div>
    <div class="row " >
        <div class="col-4"></div>
        <div class="col-4 label_produit mx-auto">Montant à payer: {{ $invoice->real_due_amount }}</div>
        <div class="col-4"></div>

    </div>
    <div class="row">
        <div class="col-4"></div>
        <div class="col-4 text-left label_produit">
            @foreach($paymentMethods as $paymentMethod)
                <div class="form-check">
                    <input class="form-check-input paymentmethod" type="radio"
                    name="PaymentMethod"
                    id="PaymentMethod_{{ $paymentMethod->id }}"
                    value="{{ $paymentMethod->id }}" checked>
                    <label class="form-check-label"
                    for="PaymentMethod_{{ $paymentMethod->id }}">
                        {{ $paymentMethod->name }}
                    </label>
                </div>
            @endforeach

        </div>
        <div class="col-4"></div>
    </div>
    <div class="row">
        <div class="col-xl-5"></div>
        <div class="col-xl-2 mx-auto">
            <button type="button"
            class="btn-sm base-button btn btn-success default-size " id="btnpayement">
            <i class="fas fa-clipboard-check"></i>
            <!---->
            Payer
            <!----></button>
        </div>
        <div class="col-xl-5"></div>
    </div>

</div>
