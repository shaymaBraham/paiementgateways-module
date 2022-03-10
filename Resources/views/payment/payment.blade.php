<div class="container">
    <h2 class="text-success">{{ __('Votre transaction est réussie') }}!</h2>
    <p>
        {!! $html !!}
    </p>
    <p>

        {{ __('Une confirmation de votre transaction vous sera envoyée par e-mail') }}!<br>
        {{ __('La référence de votre transaction') }}: <b>{{ $transaction->reference }}</b><br>

    </p>
</div>
