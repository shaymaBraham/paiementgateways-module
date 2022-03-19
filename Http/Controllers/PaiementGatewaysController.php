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
   
  public $currency;
  public $currency_symbol;
  
  public function __construct($currency="EUR",$currency_symbol="€")
  {
      $this->currency=$currency;
      $this->currency_symbol=$currency_symbol;
      
  }
  public function depositWithGateway($user,$amount,$frais_variable,$frais_fixe,$id_mode_paiement,$redirect_url,$model=NULL,$model_id=NULL,$callbackfunction=null)
  {
   

    $paiement=Paiement::create([
      'model_id' => $model_id,
      'model'=> $model,
      'user_id'=> $user->id,
      'montant'=>$amount,
      'frais_variable,'=>$frais_variable,
      'frais_fixe'=>$frais_fixe,    
      'date'=>now(),
      'mode_paiement'=>$id_mode_paiement,
      'callbackfunction'=>$callbackfunction,



    ]);
    
    
    $paiement->reference='TR'.sprintf("%06d", $paiement->id);
    $paiement->save();
  
    return PaymentTransaction::directpayment($paiement->id,$id_mode_paiement,$redirect_url,
                                $this->currency,$this->currency_symbol);

  }
    
}
