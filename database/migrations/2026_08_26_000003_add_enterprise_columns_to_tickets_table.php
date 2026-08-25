<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnterpriseColumnsToTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('ticket_type', ['incident', 'service_request', 'change_request'])
                ->default('incident')
                ->after('status');
            $table->unsignedBigInteger('department_id')->nullable()->after('kategori_id');
            $table->timestamp('response_due_at')->nullable()->after('assign_by');
            $table->timestamp('resolution_due_at')->nullable()->after('response_due_at');
            $table->timestamp('first_responded_at')->nullable()->after('resolution_due_at');
            $table->timestamp('resolved_at')->nullable()->after('first_responded_at');
            $table->boolean('is_sla_breached')->default(false)->after('resolved_at');

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'ticket_type',
                'department_id',
                'response_due_at',
                'resolution_due_at',
                'first_responded_at',
                'resolved_at',
                'is_sla_breached'
            ]);
        });
    }
}
