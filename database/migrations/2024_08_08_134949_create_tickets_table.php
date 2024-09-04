<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id_ticket');
            $table->string('ticket_number')->unique();
            $table->unsignedBigInteger('numbering_id')->nullable()->unique();
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->unsignedBigInteger('priority_id');
            $table->unsignedBigInteger('kategori_id');
            $table->string('subject');
            $table->text('issue');
            $table->string('attachment')->nullable(); 
            $table->string('assign_by')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kategori_id')->references('id')->on('kategoris')->onDelete('cascade');
            $table->foreign('numbering_id')->references('id')->on('numberings')->onDelete('cascade'); 
            $table->foreign('priority_id')->references('id')->on('priority')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
