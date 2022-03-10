<?php

namespace Modules\PaiementGateways\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModePaiement extends Model
{
    use HasFactory;

    
    public $table = 'mode_paiement';

    protected $dates = [
        'created_at',
        'updated_at',
       
    ];

    protected $fillable = [
        'libelle',
        'icone',
        'parametres',
        'is_default',
        'etat',
        'is_alimentation',
        'is_retrait',
        'frais_variable_alimentation',
        'frais_fixe_alimentation',
        'frais_variable_retrait',
        'frais_fixe_retrait',
        'code_html'
        
    ];

  

    
    
}
