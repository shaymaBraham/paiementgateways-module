<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotifylogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifylogs', function (Blueprint $table) {
            $table->id();
           
            $table->string('mode')->nullable();
            $table->string('invoice')->nullable();
            $table->string('status')->nullable();
            $table->text('error')->nullable();
            $table->text('response')->nullable();

            $table->timestamps();
        });

        Schema::table('mode_paiement', function (Blueprint $table) {
            $table->tinyInteger('is_alimentation')->default(0);
            $table->tinyInteger('is_retrait')->default(0);
            $table->float('frais_variable_alimentation')->default(0);
            $table->float('frais_fixe_alimentation')->default(0);
           
            $table->renameColumn('frais_variable','frais_variable_retrait');
            $table->renameColumn('frais_fixe','frais_fixe_retrait');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifylogs');
    }
}
