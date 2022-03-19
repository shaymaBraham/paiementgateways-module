<?php

namespace Modules\PaiementGateways\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Paiement extends Model
{
    use HasFactory;

    public $table = 'paiements';

    protected $fillable = [ 'user_id',
                            'model_id',
                            'model',
                            'status',
                            'date',
                            'mode_paiement',
                            'montant',
                            'reference',
                            'payment_response',
                            'session_id',
                            'frais_variable',
                            'frais_fixe',    
                            'callbackfunction'];

    public const STATUS_SELECT = [
        0 => 'En cours de paiement',
        1 => 'Payée et validée',
        2 => 'Echec de paiement',
        3 => 'En attendant',
        4 => 'Annulation de paiement'
    ];

    public function modePaiement()
    {
        return $this->hasOne(ModePaiement::class, 'id', 'mode_paiement');
    }

    
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
