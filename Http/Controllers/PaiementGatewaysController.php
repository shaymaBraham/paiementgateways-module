<?php

namespace Modules\PaiementGateways\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PorteMonnaie\Http\Controllers\PorteMonnaieController;

use Stripe;
use Omnipay\Omnipay;
use Nwidart\Modules\Facades\Module;

use Auth;

use Modules\PaiementGateways\Helpers\PaymentTransaction;
use Modules\PaiementGateways\Entities\Paiement;
use Modules\PaiementGateways\Entities\ModePaiement;


class PaiementGatewaysController extends Controller
{
   
  public function depositWithGateway($user,$amount,$model,$model_id,$id_mode_paiement)
  {

       

    //$id_mode_paiement=ModePaiement::where('code_dev',$code_dev_paiement)->first()->id;

    $paiement=Paiement::create([
      'model_id' => $model_id,
      'model'=> $model,
      'user_id'=> $user->id,
      'montant'=>$amount,
      'date'=>now(),
      'mode_paiement'=>$id_mode_paiement,
      

    ]);
    
    $paiement->reference='TR'.sprintf("%06d", $paiement->id);
    $paiement->save();

    return PaymentTransaction::directpayment($paiement->id,$id_mode_paiement);
  }
    
}
