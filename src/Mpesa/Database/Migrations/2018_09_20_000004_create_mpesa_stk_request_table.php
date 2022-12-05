<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMpesaStkRequestTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'mpesa_stk_request';

    /**
     * Run the migrations.
     * @table mpesa_stk_request
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('phone', 191);
            $table->double('amount');
            $table->string('reference', 191);
            $table->string('description', 191);
            $table->string('status', 191)->default('Requested');
            $table->tinyInteger('complete')->default('1');
            $table->string('MerchantRequestID', 191);
            $table->string('CheckoutRequestID', 191);
            $table->unsignedInteger('user_id')->nullable()->default(null);

            $table->unique(["CheckoutRequestID"], 'mpesa_stk_request_checkoutrequestid_unique');

            $table->unique(["MerchantRequestID"], 'mpesa_stk_request_merchantrequestid_unique');
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
