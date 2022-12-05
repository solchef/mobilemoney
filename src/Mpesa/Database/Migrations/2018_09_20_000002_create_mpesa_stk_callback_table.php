<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMpesaStkCallbackTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'mpesa_stk_callback';

    /**
     * Run the migrations.
     * @table mpesa_stk_callback
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('MerchantRequestID', 191);
            $table->string('CheckoutRequestID', 191);
            $table->integer('ResultCode');
            $table->string('ResultDesc', 191);
            $table->double('Amount')->nullable()->default(null);
            $table->string('MpesaReceiptNumber', 191)->nullable()->default(null);
            $table->string('Balance', 191)->nullable()->default(null);
            $table->string('TransactionDate', 191)->nullable()->default(null);
            $table->string('PhoneNumber', 191)->nullable()->default(null);

            $table->index(["CheckoutRequestID"], 'mpesa_stk_callback_checkoutrequestid_index');

            $table->index(["MerchantRequestID"], 'mpesa_stk_callback_merchantrequestid_index');
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
