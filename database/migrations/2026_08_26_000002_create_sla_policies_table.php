<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlaPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('priority_id')->nullable();
            $table->string('priority_name', 50); // e.g. Critical, Major, Normal, Minor
            $table->integer('response_time_minutes')->default(60); // Waktu tanggap awal
            $table->integer('resolution_time_minutes')->default(240); // Waktu penyelesaian masalah
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('priority_id')->references('id')->on('priority')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sla_policies');
    }
}
