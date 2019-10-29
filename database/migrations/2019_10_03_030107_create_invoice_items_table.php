<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('slug')->nullable();
            $table->string('task');
            $table->string('description');
            $table->decimal('rate',8,2)->nullable();
            $table->decimal('amount',8,2);
            $table->decimal('total',8,2);
            $table->enum('pay',['company','member'])->default('company');
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on("invoices")
                ->onDelete('cascade');
            $table->foreign('employee_id')
                ->references('id')
                ->on("employees")
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
}
