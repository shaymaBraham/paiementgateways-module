<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('paiementgateways')->group(function() {
 
    
  // mode de paiement
  Route::delete('mode-paiement/delete/{id}', 'ModePaiementController@delete')->name('mode-paiement.delete');
  Route::resource('mode-paiement', 'ModePaiementController', ['except' => ['update','get_mode']]);
  Route::post('mode-paiement/update', 'ModePaiementController@update')->name('mode-paiement.update');

  Route::post('mode-paiement/get_mode', 'ModePaiementController@get_mode')->name('mode-paiement.get_mode');

});

  //paiement notification
Route::group(['prefix' => 'payment', 'as' => '', 'middleware' => []], function () {

    Route::post('paypal/notifyapp', 'PaymentNotifyController@paypalnotifyapplication')->name('paypal.notifyapp');
  
    Route::get('paypal/express-checkout', 'PaymentNotifyController@paypalPayment')->name('paypal.express-checkout');
  
    Route::get('paypal/express-checkout-success', 'PaymentNotifyController@expressCheckoutSuccess');
    Route::get('paypal/express-checkout-refuse', 'PaymentNotifyController@expressCheckoutRefuse');
  
  
    Route::post('stripe/post', 'PaymentNotifyController@stripePost')->name('stripe.post');
    Route::get('stripe/checkout', 'PaymentNotifyController@checkout')->name('stripe.checkout');
    Route::post('stripe/notifyapp', 'PaymentNotifyController@stripenotifyapplication')->name('stripe.notifyapp');
    Route::post('stripe/notifyappsession', 'PaymentNotifyController@stripenotifysessionapplication')->name('stripe.notifyappsession');
    Route::post('stripe/postv3', 'PaymentNotifyController@stripePostv3')->name('stripe.postv3');
    Route::get('stripe/stripe-checkout-success', 'PaymentNotifyController@expressCheckoutSuccessStripe');
  
    Route::get('transaction/success', 'PaymentNotifyController@success')->name('transaction.success');
    Route::get('transaction/refuse', 'PaymentNotifyController@refuse')->name('transaction.refuse');
  });