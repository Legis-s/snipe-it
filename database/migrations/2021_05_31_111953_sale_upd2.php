<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SaleUpd2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {


            $table->integer('user_responsible_id')->unsigned()->nullable();
            $table->foreign('user_responsible_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->integer('contract_id')->unsigned()->nullable();
            $table->foreign('contract_id')->references('id')->on('contracts');
            $table->boolean('closing_documents')->default(0);
            $table->dateTime('sold_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('user_responsible_id');
            $table->dropForeign(['user_responsible_id']);
            $table->dropColumn('contract_id');
            $table->dropForeign(['contract_id']);
            $table->dropColumn('closing_documents');
            $table->dropColumn('sold_at');
        });

    }
}
