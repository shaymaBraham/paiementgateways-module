@extends('frontend.master')

@section('content')
<!--Register Form Start-->
<section class="wt-haslayout wt-dbsectionspace wt-proposals">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xl-8">
            <div class="wt-dashboardbox">
                <h2>{{ __('Votre transaction est réussie!') }}</h2>
                <p>
                    {{ __('Une confirmation de votre commande vous sera envoyée par e-mail!') }}<br>

                </p>
            </div>
        </div>
    </div>
</section>
@endsection
