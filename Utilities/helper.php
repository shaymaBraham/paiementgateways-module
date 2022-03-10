<?php 

if(! function_exists('paiementConfig'))
{

    function paiementConfig($key) {

        return config('paiementgateways.paiementConfig.'.$key);
    
    }
}
