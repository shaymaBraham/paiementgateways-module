@extends('frontend.master')

@section('content')
<style>
    .invoice-view-page {
    padding-left: 570px !important;
    }

        .invoice-view-page .invoice-sidebar {
        width: 300px;
        height: 100vh;
        height: 100%;
        left: 240px;
        padding: 60px 0 10px;
        position: fixed;
        top: 0;
        width: 300px;
        z-index: 25;
        background: #FFFFFF;
        }

        .invoice-view-page .inv-search {
        background: #F9FBFF !important;
        }

        .invoice-view-page .side-invoice {
        cursor: pointer;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid rgba(185, 193, 209, 0.41);
        border-left: 3px solid transparent;
        }

        .invoice-view-page .side-invoice:last-child {
        margin-bottom: 98px;
        }

        .invoice-view-page .side-invoice.router-link-exact-active {
        border-left: 3px solid #5851D8;
        background-color: #F9FBFF;
        }

        .invoice-view-page .side-invoice:hover {
        background-color: #F9FBFF;
        }

        .invoice-view-page .side-invoice .left .inv-name {
        font-style: normal;
        font-weight: normal;
        font-size: 14px;
        line-height: 21px;
        text-transform: capitalize;
        color: #040405;
        margin-bottom: 6px;
        }

        .invoice-view-page .side-invoice .left .inv-number {
        font-style: normal;
        font-weight: 500;
        font-size: 12px;
        line-height: 18px;
        color: #A5ACC1;
        margin-bottom: 6px;
        }

        .invoice-view-page .side-invoice .left .inv-status {
        font-style: normal;
        font-weight: normal;
        font-size: 10px;
        line-height: 15px;
        padding: 2px 10px;
        display: inline-block;
        }

        .invoice-view-page .side-invoice .right .inv-amount {
        font-style: normal;
        font-weight: 600;
        font-size: 20px;
        line-height: 30px;
        text-align: right;
        color: #263B5E;
        }

        .invoice-view-page .side-invoice .right .inv-date {
        font-style: normal;
        font-weight: normal;
        font-size: 14px;
        line-height: 21px;
        text-align: right;
        color: #A5ACC1;
        }

        .invoice-view-page .no-result {
        color: #B9C1D1;
        display: flex;
        justify-content: center;
        margin-top: 20px;
        }

        .invoice-view-page .side-header {
        height: 100px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30px 15px;
        border-bottom: 1px solid rgba(185, 193, 209, 0.41);
        }

        .invoice-view-page .side-header .inv-button {
        background: #F9FBFF;
        border: 1px solid #EBF1FA;
        box-sizing: border-box;
        color: #B9C1D1;
        box-shadow: none !important;
        }

        .invoice-view-page .side-content {
        overflow-y: scroll;
        height: 100%;
        }

        .invoice-view-page .invoice-view-page-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
        min-height: 0;
        overflow: hidden;
        }

        .invoice-view-page .frame-style {
        flex: 1 1 auto;
        border: 1px solid #B9C1D1;
        border-radius: 7px;
        }

        .invoice-view-page .inv-filter-fields-btn:focus, .invoice-view-page .inv-filter-sorting-btn:focus {
        border-color: inherit;
        box-shadow: none;
        outline: none !important;
        }

        .invoice-view-page .filter-container {
        margin-left: 12px;
        }

        .invoice-view-page .filter-container .filter-title {
        padding: 5px 10px;
        border-bottom: 1px solid rgba(185, 193, 209, 0.41);
        margin-bottom: 10px;
        }

        .invoice-view-page .filter-container .filter-items {
        display: flex;
        padding: 4px 9px;
        cursor: pointer;
        }

        .invoice-view-page .filter-container .filter-items:first-child {
        margin-top: auto;
        }

        .invoice-view-page .filter-container .inv-label {
        font-style: normal;
        font-weight: normal;
        font-size: 14px;
        line-height: 12px;
        text-transform: capitalize;
        color: #040405;
        margin-bottom: 6px;
        margin-left: 10px;
        cursor: pointer;
        }

        .invoice-view-page .filter-container .base-input {
        width: 20%;
        }

        .invoice-view-page .filter-container .dropdown-container {
        padding: 0px !important;
        left: auto;
        right: 0px;
        width: 155px;
        }

        .invoice-view-page .filter-invoice-date .vdp-datepicker div .vdp-datepicker__clear-button {
        margin-left: -21px;
        margin-top: 2px;
        font-size: 20px;
        font-weight: 800;
        }

        .invoice-view-page .date-group {
        display: flex;
        }

        .invoice-view-page .to-text {
        padding: 8px;
        }

        @media (max-width: 768px) {
        .invoice-view-page {
            padding-left: 310px !important;
        }

        .invoice-sidebar {
            transition: 0.2s all;
            left: 0px !important;
        }
        }
</style>
<div class="content invoice-view-page" style="padding: 10px!important;display: flex;">
    <div class="main-content invoice-view-page" style="width:100%;padding: 10px!important;display: flex;">
        <div class="invoice-view-page-container" style="width:65%;">
            <iframe src="{{ route('get.invoice.pdf',[$invoice->unique_hash]) }}" class="frame-style" ></iframe>
        </div>
        @if($ispaid)
            <div class="text-center text-danger" style="padding:12px;margin:auto; font-size:48px;line-height: 1em;">
                {{ __('La facture est payée') }}
            </div>
        @else
            <!--
            <div style="padding:50px;margin:auto" id="payment_section">
                <h2>{{ __('Payment method') }}</h2><br>
                @foreach  ($paymentMethods as $paymentMethod)
                    <button type="button" class="btn btn-primary btn-block paybotton" data-id="{{ $paymentMethod->id }}">
                        {{ $paymentMethod->libelle }}

                    </button>

                @endforeach


            </div>
        -->
            <div style="padding:50px;margin:auto" id="payment_section">
                <div class="col-12">
                    <div class="wt-articlesingle-holder wt-bgwhite">
                        <div class="row justify-content-md-center">
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 float-left">
                                            <div class="wt-articletabshold wt-articlelist">

                                                <div class="wt-radioboxholder" style="width:100%">
                                                    <div class="wt-title">
                                                        <h4>{{__('choisir le mode d\'alimentation')}}</h4>
                                                    </div>
                                                    @foreach($paymentMethods as $mode)


                                                            <div class="row">

                                                                <div class="col-md-7">
                                                                    <span class="wt-radio">
                                                                    <input  type="radio" name="modepaiement" id="mode_{{$mode->id}}" value="{{$mode->id}}" >
                                                                    <label for="mode_{{$mode->id}}">{{$mode->libelle}}</label>
                                                                    </span>
                                                                </div>

                                                                <div class="col-md-5">
                                                                        @if ($mode->getImageAttribute() != '')
                                                                            <img src="{{ $mode->getImageAttribute()->url }}" alt="{{$mode->libelle}}">

                                                                        @endif


                                                                </div>
                                                            </div>
                                                            <hr>






                                                    @endforeach
                                                </div>
                                            </div>

                                        </div>

                        </div>
                    </div>
                </div>

                <div class="col-12">
                        <div class="wt-articlesingle-holder wt-bgwhite">
                                <div class="row justify-content-md-center">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 float-left">
                                        <div class="wt-articletabshold wt-articlelist" id="deposit"></div>
                                    </div>
                                </div>
                        </div>
                </div>


            </div>
        @endif
    </div>
</div>
@endsection
@push('js')


<script>
$(function () {
    $('input[type=radio][name=modepaiement]').change(function() {


            let mode_id=this.value

            $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
            });

            $.ajax(
            {
                    url: "{{ route('frontend.mode-paiement.get_mode')}}",
                    type: 'POST',
                    data: {
                    id : mode_id
                },
                success: function (data)
                {
                            let mode=JSON.parse(data);
                            let deposit="<h4>{{ __('Paiement via ')}}"+mode.libelle+"</h4>"
                            deposit+='<form id="formAliment">'
                            deposit+='<input type="hidden" name="id_mode_paiement" value="'+mode.id+'">'
                            deposit+='<input type="hidden" name="facture_id" value="{{  $invoice->id  }}">'
                            deposit+='<div class="form-group row">'
                            deposit+=' <label class="form-label col-md-3">{{ __("Reste à payer")}}</label>';
                            deposit+=' <div class="input-group col-md-9 mb-3">'

                            deposit+='<input type="number" placeholder="Montant" name="montant" id="deposit_amount" value="{{ $invoice->rest_a_paye }}" class="form-control col-md-6" readonly>'
                            deposit+='<div class="input-group-append">'
                            deposit+=' <span class="input-group-text">{{plateformConfig('symbole_devise')}} </span>'
                            deposit+=' </div>'
                            deposit+='</div>'

                            deposit+='</div>';
                            deposit+='<div class="form-group row">'
                            deposit+=' <label class="form-label col-md-4">{{ __("Frais d\'opération")}}</label>';
                            deposit+='<span id="frais" class="col-md-6">0.00 {{plateformConfig('symbole_devise')}} </span>'
                            deposit+='</div>';

                            deposit+='<div class="form-group row">'
                            deposit+=' <label class="form-label col-md-4"><b>{{ __("Total")}}</b></label>';
                            deposit+='<span id="total" class="col-md-6"></span>'
                            deposit+='</div>';
                            deposit+='<div id="div_alimenter_btn"><a class="wt-btn btn btn-info" id="alimenter"> </a></div>'
                            deposit+="</form>"



                            $('#deposit').html(deposit)
                            $('#formAliment').on('submit', function(event){
                                event.preventDefault();

                            });
                            let amount =parseFloat($("#deposit_amount").val());
                            let total=0;

                            let frais = parseFloat(mode.frais_fixe_alimentation,2)+parseFloat((amount*mode.frais_variable_alimentation/100),2)

                            $('#frais').text(parseFloat(frais).toFixed(2) +' {{plateformConfig('symbole_devise')}} ')
                            total=parseFloat(amount+frais).toFixed(2)
                            $('#total').html('<b>'+total +' {{plateformConfig('symbole_devise')}}  </b>')
                            $('#alimenter').text('{{ __("Confirmer et payer")}}   '+total +' {{plateformConfig('symbole_devise')}} ')



                            $('#alimenter').on('click',function()
                            {
                                        if(amount <= 0)
                                        {
                                        Swal.fire({
                                                title: "Echec",
                                                icon : 'error',
                                                text: "{{ trans('le montant doit etre superieur à 0') }}",
                                                showConfirmButton: true
                                            })
                                        }
                                        else
                                        {

                                        $("#div_alimenter_btn").html('').addClass('loaderspinner')

                                        $.ajaxSetup({
                                            headers: {
                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                    }
                                                });


                                        $.ajax({
                                                url: "{{ route('frontend.porte-monnaie.paiement.facture')}}",
                                                type: 'POST',
                                                data: $('#formAliment').serialize(),
                                                success: function (data) {
                                                //console.log(data)
                                                if (data.link_payment!='')
                                                {
                                                    window.location.replace(data.link_payment)
                                                    return true
                                                }

                                                if (data.html!='')
                                                {
                                                    $("#estimate").html(data.html);

                                                    Swal.fire({

                                                        html: data.html

                                                    })
                                                    .then((result) => {
                                                        location.reload()
                                                    })
                                                    return true
                                                }
                                                if(data.append_html!='')
                                                {
                                                $("body").append(data.append_html)
                                                return true


                                                }

                                                //location.reload()

                                                },
                                                error:function(err){

                                                }
                                            });

                                        }


                            });





                },
                error:function(err){

                }
            });

    });


});

</script>
@endpush
