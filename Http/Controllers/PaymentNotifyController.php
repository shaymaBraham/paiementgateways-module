<?php

namespace Modules\PaiementGateways\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\PaiementGateways\Entities\Notifylog;
use Modules\PaiementGateways\Entities\ModePaiement;
use Modules\PaiementGateways\Entities\Paiement;

use Modules\PaiementGateways\Helpers\PaymentTransaction;
use Modules\PorteMonnaie\Entities\Item;
use Modules\PorteMonnaie\Http\Controllers\PorteMonnaieController;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Client as ClientGuzzle;
use Srmklive\PayPal\Services\ExpressCheckout;
use Srmklive\PayPal\Services\AdaptivePayments;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Bavix\Wallet\Models\Transaction;


class PaymentNotifyController extends Controller
{
    
    protected $provider;
    public function __construct() {
        $this->provider = new ExpressCheckout();
    }




    private function savelogsNotify($log)
    {
        $dt = \Carbon\Carbon::now();

        $path=storage_path()."/stripe/".$dt->year;
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }
        $path=storage_path()."/stripe/".$dt->year."/".$dt->month;
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }
        $path=storage_path()."/stripe/".$dt->year."/".$dt->month."/".$dt->day;
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }
        $filename= "/stripe/".$dt->year."/".$dt->month."/".$dt->day."/stripe_notify_".$dt->hour."_".$dt->minute."_".$dt->second.".log";
        @file_put_contents(storage_path(). $filename, PHP_EOL . date("Y-m-d H:i:s") . ": " . PHP_EOL . "---------" . PHP_EOL . print_r(  $log, true) . PHP_EOL . "---------", FILE_APPEND);
    }

    public function paypalPayment(Request $request)
    {
        //dd($request);

        $transaction_id=$request->transaction_id;
        $provider = new ExpressCheckout();

        $currency='EUR';

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

        $cart=PaymentTransaction::getCheckoutData($transaction_id,$request->redirect_url);
        $options = [
            'SOLUTIONTYPE' => 'Sole',
            'LANDINGPAGE' => 'Billing',
            'USERSELECTEDFUNDINGSOURCE' => 'CreditCard',
            ];
dd($cart);
        $response = $provider->setCurrency($currency)->addOptions($options)->setExpressCheckout($cart, false);
//dd($response);
        // if there is no link redirect back with error message
        if (!$response['paypal_link']) {

           /*
            return response()->json(['code' => 'danger',
            'message' => 'Something went wrong with PayPal',
            'response'=>$response,
            'cart'=>$cart
            ]);
            */
            $paymenttransaction=new PaymentTransaction();
            $paymenttransaction->TransactionRejected( $transaction_id,print_r(  $response, true));
            $transaction = Paiement::where('id', $transaction_id)
            ->firstOrFail();
            if (auth()->check())
            {
                return redirect()->route('portemonnaie.index')
                ->withError(__('Annulation de paiment').'- Paiement REF: '.$transaction->reference );
            }
            else
            {
                return redirect()->route('/')
                ->withError(__('Annulation de paiment').'- Paiement REF: '.$transaction->reference );
            }
            //return redirect (url('/payment/paypal/express-checkout-refuse?tr='.$transaction_id));
        // For the actual error message dump out $response and see what's in there
        }

        // redirect to paypal
        // after payment is done paypal
        // will redirect us back to $this->expressCheckoutSuccess
        //print_r($response);
        return redirect($response['paypal_link']);
    }




    public function expressCheckoutSuccess(Request $request) {

        // check if payment is recurring
        $recurring = $request->input('recurring', false) ? true : false;

        $token = $request->get('token');

        $PayerID = $request->get('PayerID');



        $transaction_id=$request->input('tr');


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

        $transaction =Paiement::find($transaction_id);


        $response = $this->provider->setCurrency($currency)->getExpressCheckoutDetails($token);
        $post=$response;

        if (!in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {

           // return redirect('payment_commande')->with(['code' => 'danger', 'message' => 'Error processing PayPal payment']);
           $paymenttransaction=new PaymentTransaction();
           $paymenttransaction->TransactionRejected( $transaction->id,print_r( $response, true));
        }

        $cart=PaymentTransaction::getCheckoutData($transaction_id,$request->redirect_url);
        $payment_status = $this->provider->doExpressCheckoutPayment($cart, $token, $PayerID);
        @file_put_contents(storage_path().'/pppayment_status.log',print_r(  $payment_status, true) .PHP_EOL . "---------", FILE_APPEND);
        $status = isset($payment_status['PAYMENTINFO_0_PAYMENTSTATUS'])?$payment_status['PAYMENTINFO_0_PAYMENTSTATUS']:'ERROR STATUS';
        $log=[];
        try {

            $new_object_request=new Request();
            $new_object_request->id=$transaction->id;
            $new_object_request=$request->redirect_url;

            return redirect()->action('PaymentNotifyController@success',[$new_object_request]);



        } catch (ModelNotFoundException $e) {
            //ici error
            $log['error']="NOT FIND TRANSACTION/APPLICATION";
        }




    }


    public function expressCheckoutSuccessStripe(Request $request) {

        // check if payment is recurring


        $transaction_id=$request->input('tr');


        try {
            $transaction = Paiement::findOrFail($transaction_id);


            $new_object_request=new Request();
            $new_object_request->id=$transaction->id;
            $new_object_request->redirect_url=$request->redirect_url;

            return redirect()->action('PaymentNotifyController@success',[$new_object_request]);




        } catch (ModelNotFoundException $e) {
            //ici error
            $log['error']="NOT FIND TRANSACTION/APPLICATION";
        }




    }


    public function expressCheckoutRefuse(Request $request)
    {
        $transaction_id=$request->input('tr');
        try {
            $transaction = Paiement::findOrFail($transaction_id);


            $new_object_request=new Request();
            $new_object_request->id=$transaction->id;
            $new_object_request->redirect_url=$request->redirect_url;

            return redirect()->action('PaymentNotifyController@refuse',[$new_object_request]);



        } catch (ModelNotFoundException $e) {
            //ici error
            $log['error']="NOT FIND TRANSACTION/APPLICATION";
        }
    }

    public function paypalnotifyapplication(Request $request)
    {
        
        $request->merge(['cmd' => '_notify-validate']);
        $post = $request->all();
		//$post="vvv";
        //echo storage_path(). "/paypal_notif.log";
        $dt = \Carbon\Carbon::now();

        $path=storage_path()."/paypal/".$dt->year;
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }
        $path=storage_path()."/paypal/".$dt->year."/".$dt->month;
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }
        $path=storage_path()."/paypal/".$dt->year."/".$dt->month."/".$dt->day;
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }



        // send the data to PayPal for validation

        //$response = (string) $this->provider->verifyIPN($post);
        $filename= "/paypal/".$dt->year."/".$dt->month."/".$dt->day."/paypal_verifyIPN_".$dt->hour."_".$dt->minute."_".$dt->second.".log";

        @file_put_contents(storage_path().$filename, PHP_EOL . date("Y-m-d H:i:s") . ": " . PHP_EOL . "---------" . PHP_EOL .print_r($post, true) . PHP_EOL . "---------", FILE_APPEND);

        $datalogs=[];
        $datalogs['invoice']=$post['invoice'];

        if ($post['invoice']!="")
        {
            $transaction_id = $post['invoice'];

            $datalogs['response']=print_r(  $post, true);
            try {
                $transaction = Paiement::findOrFail($transaction_id);
                $paymenttransaction=new PaymentTransaction();
                if (strtoupper($post['payment_status'])=='COMPLETED')
                {

                    $datalogs['status']="confirm transaction";
                    $paymenttransaction->TransactionConfirmed( $transaction->id,$datalogs['response']);
                }
                elseif (strtoupper($post['payment_status'])=='PENDING')
                {
                    $datalogs['status']="pending transaction";
                    $paymenttransaction->TransactionPending( $transaction->id,$datalogs['response']);
                }
                else
                {
                    $datalogs['status']="rejected transaction";
                    $paymenttransaction->TransactionRejected( $transaction->id,$datalogs['response']);
                }



            } catch (ModelNotFoundException $e) {

                $datalogs['error']="Transaction not found";
            }

            $filename= "/paypal/".$dt->year."/".$dt->month."/".$dt->day."/paypal_notif_".$dt->hour."_".$dt->minute."_".$dt->second.".log";
            @file_put_contents(storage_path().$filename, PHP_EOL . date("Y-m-d H:i:s") . ": " . PHP_EOL . "---------" . PHP_EOL . print_r($post, true) . PHP_EOL . "---------", FILE_APPEND);



        }
        else //error de structure
        {
            $datalogs['status']="error:structure";
            $datalogs['error']="structure invoice";
            $filename= "/paypal/".$dt->year."/".$dt->month."/".$dt->day."/paypal_notif_error_structure".$dt->hour."_".$dt->minute."_".$dt->second.".log";
            @file_put_contents(storage_path().$filename, PHP_EOL . date("Y-m-d H:i:s") . ": " . PHP_EOL . "---------" . PHP_EOL . print_r($post, true) . PHP_EOL . "---------", FILE_APPEND);
        }
        $datalogs['mode']='paypalnotifyapplication';
        Notifylog::create($datalogs);

        http_response_code(200);
    }

   

    public function stripePostv3(Request $request)
    {
        $transaction_id=$request->transaction_id;
        $transaction=Paiement::find($transaction_id);

        $log=[];
        $log['err']='';
        $log['status'] = '';
        $log['Type']='';
        $log['Code']='';
        $log['param'] ='';
        $log['message'] ='';

        if( $transaction!=null)
        {
            \Stripe\Stripe::setApiKey(config('paiementgateways.stripe.STRIPE_SECRET'));



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



                $amount                 = $transaction->montant*100;

                $items=[
                    [
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $amount ,
                        'product_data' => [
                            'name' => 'Transaction: '.$transaction->id." : ".$transaction->model.' '.$transaction->model,
                              ],
                        ],
                        'quantity' => 1,
                    ]
                ];

            try{
                $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' =>  $items,
                'mode' => 'payment',
                "metadata"  =>  ["transaction_id"=>$transaction_id,"app_origine"=>"PLATEFORME"],
                'success_url' => url('/payment/stripe/stripe-checkout-success?tr='.$transaction->id.'&redirect_url='.$request->redirect_url),
                'cancel_url' => url('/payment/paypal/express-checkout-refuse?tr='.$transaction->id.'&redirect_url='.$request->redirect_url),
                'payment_intent_data'=>[
                                        'metadata'=>  ["transaction_id"=>$transaction_id,"app_origine"=>"PLATEFORME"],
                                     ]
                ]);
                $transaction->session_id=$checkout_session->id;
                $transaction->save();
                return response()->json(['id' => $checkout_session->id]);
            }
            catch(\Stripe\Exception\CardException $e) {
                $log['err']='CardException';
                $log['status'] = ($e->getHttpStatus()!==null) ? 'Status1 is:' . $e->getHttpStatus() : 'Status1 is:NOTDEFINED';
                $log['Type'] = (isset($e->getError()->type))?'Type is:' . $e->getError()->type  : 'Type is:NOTDEFINED';
                $log['Code'] = (isset($e->getError()->code))?'Code is:' . $e->getError()->code   : 'Code is:NOTDEFINED';
                $log['param'] = (isset($e->getError()->param))?'Param is:' . $e->getError()->param   : 'Param is:NOTDEFINED';
                $log['message']= (isset($e->getError()->message))?'Message is:' . $e->getError()->param   : 'Message is:NOTDEFINED';
            } catch (\Stripe\Exception\RateLimitException $e) {
                $log['err']='RateLimitException';
                $log['status'] =  ($e->getHttpStatus()!==null)?'Status2 is:' . $e->getHttpStatus() : 'Status2 is:NOTDEFINED';
                $log['Type'] = (isset($e->getError()->type))?'Type is:' . $e->getError()->type  : 'Type is:NOTDEFINED';
                $log['Code'] = (isset($e->getError()->code))?'Code is:' . $e->getError()->code   : 'Code is:NOTDEFINED';
                $log['param'] = (isset($e->getError()->param))?'Param is:' . $e->getError()->param   : 'Param is:NOTDEFINED';
                $log['message'] = (isset($e->getError()->message))?'Message is:' . $e->getError()->param   : 'Message is:NOTDEFINED';
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $log['err']='InvalidRequestException';
                $log['status'] =  ($e->getHttpStatus()!==null)?'Status3 is:' . $e->getHttpStatus() : 'Status3 is:NOTDEFINED';
                $log['Type'] = (isset($e->getError()->type))?'Type is:' . $e->getError()->type  : 'Type is:NOTDEFINED';
                $log['Code'] = (isset($e->getError()->code))?'Code is:' . $e->getError()->code   : 'Code is:NOTDEFINED';
                $log['param'] = (isset($e->getError()->param))?'Param is:' . $e->getError()->param   : 'Param is:NOTDEFINED';
                $log['message'] = (isset($e->getError()->message))?'Message is:' . $e->getError()->param   : 'Message is:NOTDEFINED';
            } catch (\Stripe\Exception\AuthenticationException $e) {
                $log['err']='AuthenticationException';
                $log['status'] =  ($e->getHttpStatus()!==null)?'Status7 is:' . $e->getHttpStatus() : 'Status7 is:NOTDEFINED';
                $log['Type'] = (isset($e->getError()->type))?'Type is:' . $e->getError()->type  : 'Type is:NOTDEFINED';
                $log['Code'] = (isset($e->getError()->code))?'Code is:' . $e->getError()->code   : 'Code is:NOTDEFINED';
                $log['param'] = (isset($e->getError()->param))?'Param is:' . $e->getError()->param   : 'Param is:NOTDEFINED';
                $log['message'] = (isset($e->getError()->message))?'Message is:' . $e->getError()->param   : 'Message is:NOTDEFINED';
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                $log['err']='ApiConnectionException';
                $log['status'] =  ($e->getHttpStatus()!==null)?'Status4 is:' . $e->getHttpStatus() : 'Status4 is:NOTDEFINED';
                $log['Type'] = (isset($e->getError()->type))?'Type is:' . $e->getError()->type  : 'Type is:NOTDEFINED';
                $log['Code'] = (isset($e->getError()->code))?'Code is:' . $e->getError()->code   : 'Code is:NOTDEFINED';
                $log['param'] = (isset($e->getError()->param))?'Param is:' . $e->getError()->param   : 'Param is:NOTDEFINED';
                $log['message'] = (isset($e->getError()->message))?'Message is:' . $e->getError()->param   : 'Message is:NOTDEFINED';
            } catch (\Stripe\Exception\ApiErrorException $e) {
                $log['err']='ApiErrorException';
                $log['status'] =  ($e->getHttpStatus()!==null)?'Status5 is:' . $e->getHttpStatus() : 'Status5 is:NOTDEFINED';
                $log['Type'] = (isset($e->getError()->type))?'Type is:' . $e->getError()->type  : 'Type is:NOTDEFINED';
                $log['Code'] = (isset($e->getError()->code))?'Code is:' . $e->getError()->code   : 'Code is:NOTDEFINED';
                $log['param'] = (isset($e->getError()->param))?'Param is:' . $e->getError()->param   : 'Param is:NOTDEFINED';
                $log['message'] = (isset($e->getError()->message))?'Message is:' . $e->getError()->param   : 'Message is:NOTDEFINED';
            } catch (Exception $e) {
                $log['err']='Exception';
                $log['status'] =  ($e->getHttpStatus()!==null)?'Status6 is:' . $e->getHttpStatus() : 'Status6 is:NOTDEFINED';
                $log['Type'] = (isset($e->getError()->type))?'Type is:' . $e->getError()->type  : 'Type is:NOTDEFINED';
                $log['Code'] = (isset($e->getError()->code))?'Code is:' . $e->getError()->code   : 'Code is:NOTDEFINED';
                $log['param'] = (isset($e->getError()->param))?'Param is:' . $e->getError()->param   : 'Param is:NOTDEFINED';
                $log['message'] = (isset($e->getError()->message))?'Message is:' . $e->getError()->param   : 'Message is:NOTDEFINED';

            }
            $this->savelogsStripe($log);
            $log['items']=$items;
            return response()->json(['error' => $log], 500);
        }

        return response()->json(['error' => $transaction_id.'Transaction not found.'], 500);
    }


    


    private function savelogsStripe($charge)
    {
        $dt = \Carbon\Carbon::now();

        $path=storage_path()."/stripe/".$dt->year;
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }
        $path=storage_path()."/stripe/".$dt->year."/".$dt->month;
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }
        $path=storage_path()."/stripe/".$dt->year."/".$dt->month."/".$dt->day;
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }
        $filename= "/stripe/".$dt->year."/".$dt->month."/".$dt->day."/stripe_".$dt->hour."_".$dt->minute."_".$dt->second.".log";
        @file_put_contents(storage_path(). $filename, PHP_EOL . date("Y-m-d H:i:s") . ": " . PHP_EOL . "---------" . PHP_EOL . print_r(  $charge, true) . PHP_EOL . "---------", FILE_APPEND);
    }

    public function stripenotifysessionapplication()
    {

        // You can find your endpoint's secret in your webhook settings

        $endpoint_secret = env('ENDPOINT_SECRET', '');
        if ($endpoint_secret=='')
        {
            http_response_code(401);
            exit();
        }

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        $this->savelogsNotify($payload);
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        if ($event->type == "payment_intent.succeeded") {
            $etat_bug='-2';
            @file_put_contents(storage_path().'/bug_stripe.log',$etat_bug .PHP_EOL . "---------", FILE_APPEND);
            $intent = $event->data->object;
            $transaction_id= (isset($event->data->object->metadata->transaction_id))?$event->data->object->metadata->transaction_id:0;
            $app_origine= (isset($event->data->object->metadata->app_origine))?$event->data->object->metadata->app_origine:'';
            if ($app_origine!="PLATEFORME") return 0;

            $datalogs=[];
            $datalogs['invoice']=$transaction_id;

            $datalogs['response']=print_r( $payload, true);

            if ($transaction_id)
            {


                try {

                    if($transaction_id)
                        $transaction = Paiement::findOrFail($transaction_id);
                    else
                        $transaction = Paiement::where('session_id',$session_id)
                        ->firstOrFail();
                    $paymenttransaction=new PaymentTransaction();


                    $datalogs['status']="confirm transaction";
                    $paymenttransaction->TransactionConfirmed( $transaction->id,$datalogs['response']);





                } catch (ModelNotFoundException $e) {

                    $datalogs['error']="Transaction not found";
                }
            }
            else
            {

                $datalogs['status']="error:structure";
                $datalogs['error']="structure";


            }

            $datalogs['mode']='stripenotifysessionapplication';
            Notifylog::create($datalogs);
            http_response_code(200);
            exit();
        }
        if ($event->type == "payment_intent.payment_failed") {
            $intent = $event->data->object;
            $transaction_id= (isset($event->data->object->metadata->transaction_id))?$event->data->object->metadata->transaction_id:0;
            $app_origine= (isset($event->data->object->metadata->app_origine))?$event->data->object->metadata->app_origine:'';
            if ($app_origine!="PLATEFORME") return 0;
            $datalogs=[];
            $datalogs['transaction_id']=$transaction_id;

            $datalogs['response']=print_r( $payload, true);

            if ($transaction_id)
            {


                try {

                    if($transaction_id)
                        $transaction = Paiement::findOrFail($transaction_id);
                    else
                        $transaction = Paiement::where('session_id',$session_id)
                        ->firstOrFail();
                    $paymenttransaction=new PaymentTransaction();


                    $datalogs['status']="payment_failed transaction";
                    $paymenttransaction->TransactionRejected( $transaction->id,$datalogs['response']);





                } catch (ModelNotFoundException $e) {

                    $datalogs['error']="Transaction not found";
                }
            }
            else
            {

                $datalogs['status']="error:structure";
                $datalogs['error']="structure";


            }

            @file_put_contents(storage_path().'/bug_stripe.log',$etat_bug .PHP_EOL . "---------", FILE_APPEND);
            $datalogs['mode']='stripenotifysessionapplication';
            Notifylog::create($datalogs);
            http_response_code(200);
            exit();
        }
        if ($event->type == "payment_intent.canceled") {
            $intent = $event->data->object;
            $transaction_id= (isset($event->data->object->metadata->transaction_id))?$event->data->object->metadata->transaction_id:0;
            $app_origine= (isset($event->data->object->metadata->app_origine))?$event->data->object->metadata->app_origine:'';
            if ($app_origine!="PLATEFORME") return 0;
            $datalogs=[];
            $datalogs['transaction_id']=$transaction_id;

            $datalogs['response']=print_r( $payload, true);

            if ($transaction_id)
            {


                try {

                    if($transaction_id)
                        $transaction = Paiement::findOrFail($transaction_id);
                    else
                        $transaction = Paiement::where('session_id',$session_id)
                        ->firstOrFail();
                    $paymenttransaction=new PaymentTransaction();


                    $datalogs['status']="canceled transaction";
                    $paymenttransaction->TransactionRejected( $transaction->id,$datalogs['response']);





                } catch (ModelNotFoundException $e) {

                    $datalogs['error']="Transaction not found";
                }
            }
            else
            {

                $datalogs['status']="error:structure";
                $datalogs['error']="structure";


            }

            //@file_put_contents(storage_path().'/bug_stripe.log',$etat_bug .PHP_EOL . "---------", FILE_APPEND);
            $datalogs['mode']='stripenotifysessionapplication';
            Notifylog::create($datalogs);
            http_response_code(200);
            exit();
        }
    }

    public function success(Request $request){

        $transaction = Paiement::where('id', $request->id)
        ->firstOrFail();
        if (auth()->check())
        {
            return redirect(urldecode($request->redirect_url))
            ->withStatus(__('Votre transaction est réussie!').' - '.__('Une confirmation de votre commande vous sera envoyée par e-mail!') );
        }
        else
        {
            return redirect()->route('/')
            ->withStatus(__('Votre transaction est réussie!').' - '.__('Une confirmation de votre commande vous sera envoyée par e-mail!') );
        }

    }

    public function refuse($id,$redirect_url){

        $transaction = Paiement::where('id', $id)
        ->firstOrFail();
       /* $transaction->status = 4;
        $transaction->payment_response="ANNULATION DU CLIENT";
        $transaction->save();*/
        if (auth()->check())
        {
            return redirect(urldecode($redirect_url))
            ->withError(__('Annulation de paiment').'- Transaction REF: '.$transaction->id );
        }
        else
        {
            return redirect()->route('/')
            ->withError(__('Annulation de paiment').'- Transaction REF: '.$transaction->id );
        }

    }


    

}
