<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateModePaiementColsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mode_paiement', function (Blueprint $table) {
            $table->string('icone')->nullable()->change();
            $table->longText('parametres')->nullable()->change();
            $table->integer('is_default')->default('0')->change();
            $table->integer('etat')->default('1')->change();
           
            $table->longText('code_html')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
}
