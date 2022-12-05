<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMpesaBulkPaymentRequestTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'mpesa_bulk_payment_request';

    /**
     * Run the migrations.
     * @table mpesa_bulk_payment_request
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('conversation_id', 191);
            $table->string('originator_conversation_id', 191);
            $table->double('amount');
            $table->string('phone', 20);
            $table->string('remarks', 191)->nullable()->default(null);
            $table->string('CommandID', 191)->default('BusinessPayment');
            $table->unsignedInteger('user_id')->nullable()->default(null);
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists($this->set_schema_table);
     }
}
