<?php
namespace Modules\PaiementGateways\Helpers;

//use PDF;

use Carbon\Carbon;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use App\Models\User;

//use App\Models\Paiement;
//use App\Models\Transaction;
use Modules\PaiementGateways\Entities\ModePaiement;
use Modules\PorteMonnaie\Entities\Item;
use Modules\PorteMonnaie\Http\Controllers\PorteMonnaieController;
use Modules\PaiementGateways\Entities\Paiement;


/*use App\Mail\RefusePaymentDirect;
use App\Helpers\PortMonnaieHelper;
use App\Mail\PendingPaymentDirect;
use App\Mail\SuccessPaymentDirect;*/
use Illuminate\Support\Facades\View;
///use App\Http\Requests\TransactionRequest;
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
                //\LogActivity::addToLog("CONFIRMED TRANSACTION & CREATE PAIMENT",
                //    ['payment_id'=>$payment->id,'transaction_id'=>$transaction->id],$payment);

                //send notification application confirmation paiment
               /* if ( $transaction->modele_type==get_class(new Facture()))
                {
                    //create paiement facture
                    $facture=Facture::find($transaction->modele_id );
                    if ($facture)
                    {
                        if ($facture->rest_a_paye == $transaction->mnt_debit) {
                            $facture->status = Facture::STATUS_COMPLETE;
                            $facture->status_paye = Facture::STATUS_PAYEE;
                            $facture->rest_a_paye = 0;
                        } elseif ( $facture->rest_a_paye != $transaction->mnt_debit) {
                            $facture->rest_a_paye = (double)$facture->rest_a_paye - (double)$transaction->mnt_debit;
                            if ($facture->rest_a_paye < 0) {

                                
                                $facture->rest_a_paye=0;
                                $facture->status = Facture::STATUS_COMPLETE;
                                $facture->status_paye = Facture::STATUS_PAYEE;
                            }
                            else
                            {
                                $facture->status_paye = Facture::STATUS_PARTIELLEMENT_PAYEE;
                            }
                        }
                        $facture->save();
                        $payment = Paiement::create([
                            'date'              =>$payment_date,
                            'info'              =>'',
                            'montant'           =>$transaction->mnt_debit,
                            'user_id'           =>$transaction->id_user,
                            'facture_id'        =>$facture->id,
                            'mode_paiement_id'  =>$transaction->id_mode_paiement,

                        ]);
                        $payment->unique_hash=str_random(60).$payment->id;
                        $payment->save();
                    }
                }
                else
                {*/
                    //alimentation solde
                    //PortMonnaieHelper::updateSolde($transaction->user,$transaction);

                    $amount=$transaction->montant;
                    $payment_method  = ModePaiement::find($transaction->mode_paiement);

                    $meta=[
                        'title' => 'Alimentation via Gateway',
                        'source'=>$payment_method->libelle,
                        'origine' => $transaction->payment_response,


                    ];
                   
                    
                    $paiement=new PorteMonnaieController();

                    

                    ///if(model exists)=> ( plus transaction de achat  )

                   
                        $model=get_class($transaction->model);

                        $produit=$model::find($transaction->model_id);
                    
                       
                        $paiement->buy_product($produit,$transaction->user_id);

                        $paiement->alimentation($transaction->user,$amount,$meta);
                   
                    
               // }

                $this->sendSuccessDirect($transaction);


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




            //\Mail::to($email)->send(new SuccessPaymentDirect($data));



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




        public static function getCheckoutData($transaction_id,$redirect_url)
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
                    //'return_url' => url('/payment/paypal/express-checkout-success?price='.$price.'&cmd='.$cmd_id.'&mode='.$mode_paiement),
                    'return_url' => url('/payment/paypal/express-checkout-success?tr='.$transaction->id.'&redirect_url='.$redirect_url),
                    
                    // every invoice id must be unique, else you'll get an error from paypal
                    'invoice_id' =>  $transaction->id ,

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

    public static function directpayment($transaction_id,$ModePaiement_id,$redirect_url)
    {
        $transaction = Paiement::find($transaction_id);
        if (!$transaction)
        {
            return response()->json(['message'=>'Ressource Not Found'],403);
        }

        $currency = config('paiementgateways.paiementConfig.currency');
        $currency_symbol=config('paiementgateways.paiementConfig.symbole_devise');
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
                    $link_payment=route('paypal.express-checkout').'?transaction_id='.$transaction->id.'&redirect_url='.$redirect_url;
    
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
                        'redirect_url'
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

            $html='<h4> votre paiement par portemonnaie est passé avec succée </h4>';

            return response()->json([
                'transaction' => $transaction,
                'html'=>$html,
                'currency'=>$currency,
                'currency_symbol'=>$currency_symbol,
                'success' => 1
            ]);
        }
      



       
        
    }

    
}
