<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumberingsTable extends Migration
{
    public function up()
    {
        Schema::create('numberings', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('no_ticket'); // This could be used to store a sequence number
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('numberings');
    }
}

