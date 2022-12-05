<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMpesaC2BCallbackTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'mpesa_c2b_callback';

    /**
     * Run the migrations.
     * @table mpesa_c2b_callback
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('TransactionType', 191);
            $table->string('TransID', 191);
            $table->string('TransTime', 191);
            $table->double('TransAmount');
            $table->integer('BusinessShortCode');
            $table->string('BillRefNumber', 191);
            $table->string('InvoiceNumber', 191)->nullable()->default(null);
            $table->string('ThirdPartyTransID', 191)->nullable()->default(null);
            $table->double('OrgAccountBalance');
            $table->string('MSISDN', 191);
            $table->string('FirstName', 191)->nullable()->default(null);
            $table->string('MiddleName', 191)->nullable()->default(null);
            $table->string('LastName', 191)->nullable()->default(null);

            $table->unique(["TransID"], 'mpesa_c2b_callback_transid_unique');
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
