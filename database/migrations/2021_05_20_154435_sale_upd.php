<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SaleUpd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->string('assigned_type')->nullable();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->string('image')->nullable();
            $table->integer('purchase_id')->unsigned()->nullable()->default(NULL);
            $table->foreign('purchase_id')->references('id')->on('purchases')
                ->onDelete('cascade');
            $table->integer('status_id')->nullable();
            $table->integer('supplier_id')->nullable()->default(NULL);
            $table->integer('nds')->nullable()->default(20);
            $table->integer('user_verified_id')->unsigned()->nullable();
            $table->foreign('user_verified_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->dateTime('last_checkout')->nullable();
            $table->softDeletes();
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
            $table->dropColumn('assigned_type');
            $table->dropColumn('company_id');
            $table->dropForeign(['company_id']);
            $table->dropColumn('image');
            $table->dropForeign(['purchase_id']);
            $table->dropColumn('purchase_id');
            $table->dropColumn('status_id');
            $table->dropColumn('supplier_id');
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('nds');
            $table->dropColumn('user_verified_id');
            $table->dropForeign(['user_verified_id']);
            $table->dropColumn('last_checkout');
        });
    }
}
