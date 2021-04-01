<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreaCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titleKey')->nullable()->comment('titleKey');
            $table->string('typeName')->nullable()->comment('typeName');
            $table->integer('parentId')->nullable()->comment('parentId');
            $table->integer('index')->nullbale()->comment('index');
            $table->string('value')->nullable()->comment('value');
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
        Schema::dropIfExists('area_codes');
    }
}
