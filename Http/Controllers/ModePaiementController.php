<?php

namespace Modules\PaiementGateways\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\PaiementGateways\Entities\ModePaiement;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class ModePaiementController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $modesPaiement=ModePaiement::all();
        return view('paiementgateways::modePaiement.index',compact('modesPaiement'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('paiementgateways::modePaiement.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //

        $data['libelle']=$request->libelle;
        $data['frais_fixe_alimentation']=$request->frais_fixe_alimentation;
        $data['frais_variable_alimentation']=$request->frais_variable_alimentation;
        $data['frais_fixe_retrait']=$request->frais_fixe_retrait;
        $data['frais_variable_retrait']=$request->frais_variable_retrait;

        if (isset($request->is_default))
        $data['is_default']=$request->is_default;

        if (isset($request->is_retrait))
        $data['is_retrait']=$request->is_retrait;

        if (isset($request->is_alimentation))
        $data['is_alimentation']=$request->is_alimentation;

        $data['etat']=$request->etat;
        $data['code_html']=$request->code_html;

        $tbfields=$request->get('fields');
        $fields=[];
        $params=new \stdClass();
        if (($tbfields) )
            foreach($tbfields as $key => $value)
            {
                $field=new \stdClass();
                if (trim($value['key'])!='' && $value['value']!='')
                {
                    $field->key=$value['key'];
                    $field->value=$value['value'];
                    $fields[]=$field;
                    $params->{$value['key']}=$value['value'];
                }

            }
        $data['parametres']=json_encode($fields);


        $modePaiement = ModePaiement::create($data);
        if (isset($params->code_dev) )
        {
            if ($params->code_dev=="PAYPAL")
                $this->saveEnvPayPal($params);
            if ($params->code_dev=="STRIPE")
                $this->saveEnvStripe($params);
        }

        return redirect()->route('mode-paiement.index');
    }


    public function get_mode(Request $request)
    {
         $id=$request->id;
         $mode=ModePaiement::find($id);

     

         echo json_encode($mode);

    }
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {

        $modePaiement=ModePaiement::findOrFail($id);
        return view('paiementgateways::modePaiement.show',compact('modePaiement'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {

        $modePaiement=ModePaiement::findOrFail($id);
        return view('paiementgateways::modePaiement.edit',compact('modePaiement'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //

        $id_mode=$request->id_mode;

       $mode=ModePaiement::find($id_mode);

       $mode->libelle=$request->libelle;
       $mode->frais_fixe_alimentation=$request->frais_fixe_alimentation;
       $mode->frais_variable_alimentation=$request->frais_variable_alimentation;
       $mode->frais_fixe_retrait=$request->frais_fixe_retrait;
       $mode->frais_variable_retrait=$request->frais_variable_retrait;
       $mode->icone=$request->photo;
       if (isset($request->is_default))
       $mode->is_default=$request->is_default;

       if (isset($request->is_retrait))
       $mode->is_retrait=$request->is_retrait;

       if (isset($request->is_alimentation))
       $mode->is_alimentation=$request->is_alimentation;

       $mode->etat=$request->etat;
       $mode->code_html=$request->code_html;

       $tbfields=$request->get('fields');
       $params=new \stdClass();
        $fields=[];
        if (($tbfields) )
            foreach($tbfields as $key => $value)
            {
                $field=new \stdClass();
                if (trim($value['key'])!='' && $value['value']!='')
                {
                    $field->key=$value['key'];
                    $field->value=$value['value'];
                    $fields[]=$field;
                    $params->{$value['key']}=$value['value'];
                }

            }



        $mode->parametres=json_encode($fields);
        if (isset($params->code_dev) )
        {
            if ($params->code_dev=="PAYPAL")
                $this->saveEnvPayPal($params);
            if ($params->code_dev=="STRIPE")
                $this->saveEnvStripe($params);
        }

        if ($request->input('icone', false)) {
            if (!$mode->icone || $request->input('icone') !== $mode->icone->file_name) {
                if ($mode->icone) {
                    $mode->icone->delete();
                }

    // $mode->addMedia(storage_path('tmp/uploads/' . basename($request->input('icone'))))->toMediaCollection('icone');
            }
        } elseif ($mode->icone) {
            $mode->icone->delete();
        }

      $mode->save();


       return redirect()->route('mode-paiement.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function delete($id)
    {
        //

        $mode=ModePaiement::find($id);
        $mode->delete();
        return back();
    }

    private function saveEnvPayPal($params)
    {

        $envparam=['PAYPAL_SANDBOX_API_USERNAME',
                    'PAYPAL_SANDBOX_API_PASSWORD',
                    'PAYPAL_SANDBOX_API_SECRET',
                    'PAYPAL_MODE',
                    'PAYPAL_LIVE_API_USERNAME',
                    'PAYPAL_LIVE_API_PASSWORD',
                    'PAYPAL_LIVE_API_SECRET',
        ];
        foreach($envparam as $envp)
        {

            $value=addslashes($params->{$envp});
            Artisan::call("env:set $envp $value");
        }


        Artisan::call('config:clear');
        Artisan::call('cache:clear');

    }

    private function saveEnvStripe($params)
    {

        $envparam=['STRIPE_KEY',
                    'STRIPE_SECRET',
                    'ENDPOINT_SECRET',

        ];
        foreach($envparam as $envp)
        {
            $value=addslashes($params->{$envp});
            Artisan::call("env:set $envp $value");
        }

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

    }

}
