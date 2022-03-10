<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModePaiementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mode_paiement', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('icone');
            $table->longText('parametres');
            $table->tinyInteger('is_default');
            $table->tinyInteger('etat');
            $table->float('frais_variable')->default(0);
            $table->float('frais_fixe')->default(0);
            $table->longText('code_html');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mode_paiement');
    }
}
