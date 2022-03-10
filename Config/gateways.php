

<?php
return [

    

    'stripe'=> [
        

        'STRIPE_KEY'=>env('STRIPE_KEY'),
        'STRIPE_SECRET'=>env('STRIPE_SECRET'),
        'ENDPOINT_SECRET'=>env('ENDPOINT_SECRET')

    ]

    ,

    'paiementConfig' => [
        'currency'=> 'EUR',
        'symbole_devise' => '€'
    ]



];