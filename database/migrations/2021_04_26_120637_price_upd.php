<?php

use Illuminate\Database\Migrations\Migration;

class PriceUpd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `purchases` CHANGE COLUMN `final_price` `final_price` double(14,2) NOT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `purchases` CHANGE COLUMN `final_price` `final_price` double (8,2) NOT NULL;');
    }
}
