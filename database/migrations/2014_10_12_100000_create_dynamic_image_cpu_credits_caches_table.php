<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicImageCpuCreditsCachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamic_image_cpu_credits_caches', function (Blueprint $table) {
            $table->increments("id");
            $table->string('ip_address_v4')->nullable()->default(NULL)->index();
            $table->integer("minutes")->index()->nullable()->default(NULL);
            $table->mediumInteger("credits")->unsigned()->nullable()->default(NULL);
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
        Schema::dropIfExists('dynamic_image_cpu_credits_caches');
    }
}
