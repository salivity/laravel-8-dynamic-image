<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicImageCpuCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamic_image_cpu_credits', function (Blueprint $table) {
            $table->increments("id");
            $table->string('ip_address_v4')->nullable()->default(NULL)->index();
            $table->mediumInteger("credits")->unsigned();
            $table->timestamps()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dynamic_image_cpu_credits');
    }
}
