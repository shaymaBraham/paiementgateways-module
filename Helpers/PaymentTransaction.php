<?php
namespace Modules\PaiementGateways\Helpers;


use Carbon\Carbon;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use App\Models\User;
use Illuminate\Support\Facades\File;

use Modules\PaiementGateways\Entities\ModePaiement;
use Modules\PorteMonnaie\Entities\Item;
use Modules\PorteMonnaie\Http\Controllers\PorteMonnaieController;
use Modules\PaiementGateways\Entities\Paiement;


use Modules\PaiementGateways\Emails\RefusePaymentDirect;
use Modules\PaiementGateways\Emails\PendingPaymentDirect;
use Modules\PaiementGateways\Emails\SuccessPaymentDirect;

use Illuminate\Support\Facades\View;
use GuzzleHttp\Exception\RequestException;
use Bavix\Wallet\Models\Transaction;


class PaymentTransaction
{

      
        public function TransactionConfirmed($transaction_id,$payment_response='')
        {
            $transaction = Paiement::find($transaction_id);



            //copier transaction to payment

            $payment_date = Carbon::now();




                $transaction->status = 1;
                $transaction->payment_response=$payment_response;
                $transaction->save();
               
                    //alimentation solde

                    $amount=$transaction->montant;
                    $payment_method  = ModePaiement::find($transaction->mode_paiement);

                    $meta=[
                        'title' => 'Alimentation via Gateway',
                        'source'=>$payment_method->libelle,
                        'origine' => $transaction->payment_response,


                    ];
                    $etat_bug='-1';
                   @file_put_contents(storage_path().'/bug_buyproduct.log',$etat_bug .PHP_EOL . "---------", FILE_APPEND);
                   
                   $paiement=new PorteMonnaieController();

                    
                    $paiement->alimentation($transaction->user,$amount*100,$meta);

                    //transaction d'achat en plus
                    if($transaction->model != NULL && $transaction->model_id != NULL  )
                    {
                        $etat_bug='-2';
                        @file_put_contents(storage_path().'/bug_buyproduct.log',$etat_bug .PHP_EOL . "---------", FILE_APPEND);
                        
                       
                            $model=$transaction->model;
    
                            $produit=$model::find($transaction->model_id);
                            $etat_bug='-3';
                            @file_put_contents(storage_path().'/bug_buyproduct.log',$etat_bug .PHP_EOL . "---------", FILE_APPEND);
                            
    
                            $retour=$paiement->buy_product($produit,$transaction->user_id);
                            
                            $etat_bug='-4';
                            @file_put_contents(storage_path().'/bug_buyproduct.log',$etat_bug .PHP_EOL . "---------", FILE_APPEND);
                            
                           
                    }

                    
                   
                    
               // }

                $this->sendSuccessDirect($transaction);

                if($transaction->callbackfunction)
                {
                    call_user_func_array($transaction->callbackfunction,[$transaction->id,'Confirmed']);
                }

                return [
                    'success' => true
                ];





            return [
                'success' => false
            ];
        }

        public function TransactionPending($transaction_id,$payment_response='')
        {
            $transaction = Paiement::find($transaction_id);
            $transaction->status = 3;
            $transaction->payment_response=$payment_response;
            $transaction->save();
            //send notification application annulation paiment


            // \LogActivity::addToLog("PENDING TRANSACTION",
            //        ['transaction_id'=>$transaction->id],NULL);


            $this->sendPendingDirect($transaction);
           if($transaction->callbackfunction)
            {
                call_user_func_array($transaction->callbackfunction,[$transaction->id,'pending']);
            }

            return [
                'success' => true
            ];
        }

        public function TransactionRejected($transaction_id,$payment_response='')
        {
            $transaction = Paiement::find($transaction_id);
            $transaction->status = 2;
            $transaction->payment_response=$payment_response;
            $transaction->save();
            //send notification application annulation paiment


            //\LogActivity::addToLog("REJECTED TRANSACTION",
            //        ['transaction_id'=>$transaction->id],NULL);

           // PortMonnaieHelper::updateSolde($transaction->user,$transaction);
            $this->sendRefuseDirect($transaction);

            if($transaction->callbackfunction)
            {
                call_user_func_array($transaction->callbackfunction,[$transaction->id,'pending']);
            }

            return [
                'success' => true
            ];
        }



        protected function sendSuccessDirect($transaction)
        {

            $data['transaction'] = $transaction->toArray();

            $userId = $transaction->user_id;

            $data['user'] = $transaction->user->toArray();

            $email = $data['user']['email'];

            if (!$email) {
                return false;
            }




            \Mail::to($email)->send(new SuccessPaymentDirect($data));



            return true;
        }


        protected function sendPendingDirect($transaction)
        {

            $userId = $transaction->user_id;
            $data['transaction'] = $transaction->toArray();
            $data['user'] = $transaction->user->toArray();

            $email = $data['user']['email'];

            if (!$email) {
                return false;
            }



           // \Mail::to($email)->send(new PendingPaymentDirect($data));


            return true;
        }



        protected function sendRefuseDirect($transaction)
        {

            $data['transaction'] = $transaction->toArray();

            $userId = $transaction->user_id;

            $data['user'] = $transaction->user->toArray();

            $email = $data['user']['email'];

            if (!$email) {
                return false;
            }



           // \Mail::to($email)->send(new RefusePaymentDirect($data));



            return true;
        }




        public static function getCheckoutData($transaction_id,$redirect_url,$currency,$currency_symbol)
        {

            try
            {

                $transaction =Paiement::find($transaction_id);


                    $items = [];

                    array_push($items,
                    [
                        'name' =>'Transaction: '.$transaction->reference,
                        'desc'  => 'Paiment de '.$transaction->model.' ID:  '.$transaction->model_id,
                        'price' => $transaction->montant,
                        'qty' => 1//$item->quantity
                    ]);


                $cart= [
                    'items' =>   $items,
                    // return url is the url where PayPal returns after user confirmed the payment
                    'return_url' => url('/payment/paypal/express-checkout-success?tr='.$transaction->id.'&redirect_url='.$redirect_url.'&currency='.$currency.'&currency_symbol='.$currency_symbol),
                    
                    // every invoice id must be unique, else you'll get an error from paypal
                    'invoice_id' =>  'ex-'.$transaction->id ,

                    'invoice_description' => 'Paiment de '.$transaction->model.' ID:  '.$transaction->model_id ,
                    'cancel_url' => url('/payment/paypal/express-checkout-refuse?tr='.$transaction->id.'&redirect_url='.$redirect_url),
                    // total is calculated by multiplying price with quantity of all cart items and then adding them up
                    // in this case total is 20 because Product 1 costs 10 (price 10 * quantity 1) and Product 2 costs 10 (price 5 * quantity 2)
                    'total' => $transaction->montant,
                ];

                return $cart;
            } catch(\Exception $e) {

                return [];
            }
        }

    public static function directpayment($transaction_id,$ModePaiement_id,$redirect_url,$currency,$currency_symbol)
    {
        $transaction = Paiement::find($transaction_id);
        if (!$transaction)
        {
            return response()->json(['message'=>'Ressource Not Found'],403);
        }

        if (($currency=null) || ($currency==''))
        {
            $currency='EUR';
        }

        if (($currency_symbol=null) || ($currency_symbol==''))
        {
            $currency_symbol='€';
        }



        $carbon = new Carbon();
        $transaction_date = $carbon->format('Y-m-d');

        $transaction_prefix = '';

        if($ModePaiement_id != 0)
        {
            $payment_method  = ModePaiement::find($ModePaiement_id);
            if (!$payment_method)
            {
                return response()->json(['message'=>'Mode paiement Not Found'],403);
            }
            

            $link='';
            $link_payment='';
            $append_html='';
    
    
            $html=$payment_method->code_html;
            $templatepayment="paiementgateways::payment.payment";
            $append_html = view($templatepayment,compact('html','transaction'))->render();
    
            $payment_parametres=new \stdClass();
            if($payment_method->parametres != NULL)
            {
                $params=json_decode($payment_method->parametres);
                foreach ($params as $key =>  $fieldarray)
                {
                    $field=json_decode(json_encode($fieldarray), FALSE);
                    $payment_parametres->{$field->key}=$field->value;
                }
            }
            if (isset($payment_parametres->code_dev))
            {
    
                $code_dev=$payment_parametres->code_dev;
    
    
    
    
                if($code_dev=='PAYPAL')
                {
                    $html='';
                    $link_payment=route('paypal.express-checkout').'?transaction_id='.$transaction->id.'&redirect_url='.$redirect_url.'&currency='.$currency.'&currency_symbol='.$currency_symbol;
    
                }
    
                if($code_dev=='STRIPE')
                {
                    $html='';
                    $templatepayment="paiementgateways::payment.stripev3";
                    $link='';
                    $linkp=$transaction->id;
    
                    $append_html = view($templatepayment,
                    compact(
                        'transaction',
                        'currency',
                        'redirect_url',
                        'currency_symbol',
                    ))->render();
    
                }
            }

            return response()->json([
                'transaction' => $transaction,
                'payment_method'=>$payment_method,
                'html'=>$html,
                'link_payment'=>$link_payment,
                'append_html'=>$append_html,
                'currency'=>$currency,
                'currency_symbol'=>$currency_symbol,
                'success' => 1
            ]);
        }
        else{

           

            
               
               
                    //alimentation solde

                    $amount=$transaction->montant;
                    
                   
                   $paiement=new PorteMonnaieController();
                    
                 
                    ///if(model exists)=> ( plus transaction de achat  )
                    
                   
                        $model=$transaction->model;

                        $produit=$model::find($transaction->model_id);
                      

                        $retour=$paiement->buy_product($produit,$transaction->user_id);
                        
                        if($retour)
                        { 
                            $transaction->status = 1;
                           
                            $transaction->save();
                            $html='<h4> votre paiement par portemonnaie est passé avec succée </h4>';
                            return response()->json([
                                'transaction' => $transaction,
                                'html'=>$html,
                                'currency'=>$currency,
                                'link_payment'=>'',
                                'append_html'=>'',
                                'currency_symbol'=>$currency_symbol,
                                'success' => 1
                            ]);
                        }
                        else{

                            $transaction->status = 2;
                            
                            $transaction->save();
                            $html='<h4> problème de paiement par portemonnaie </h4>';
                            return response()->json([
                                'transaction' => $transaction,
                                'html'=>$html,
                                'currency'=>$currency,
                                'link_payment'=>'',
                                'append_html'=>'',
                                'currency_symbol'=>$currency_symbol,
                                'success' => 0
                            ]);
                        }

            
        }
      



       
        
    }

    
}
