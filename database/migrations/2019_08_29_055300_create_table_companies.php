<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('website');
            $table->string('state_incoporation');
            $table->string('entity_type');
            $table->string('industry');
            $table->string('size');
            $table->text('description')->nullable();;
            $table->string('headquaters_addresses');
            $table->string('legal_address');
            $table->string('billing_address');
            $table->string('document_agreement');
            $table->string('document_signed_by');
            $table->date('document_signature_date');
            $table->string('bank_name');
            $table->string('bank_account_name');
            $table->string('bank_account_number');
            $table->string('admin_first_name')->nullable();
            $table->string('admin_last_name')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_phone_number')->nullable();
            $table->string('admin_level')->nullable();
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('companies');
    }
}
