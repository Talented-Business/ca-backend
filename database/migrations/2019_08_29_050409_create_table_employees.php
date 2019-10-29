<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('id_number')->uique();
            $table->enum('gender',['Male', 'Female'])->default('Male');
            $table->date('birthday');
            $table->string('nationality');
            $table->bigInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('home_phone_number');
            $table->string('mobile_phone_number');
            $table->string('personal_email');
            $table->enum('marital',['Single', 'Married', 'Divorce'])->default('Single');
            $table->string('skype_id');
            $table->string('referal_name')->nullable();
            $table->string('country');
            $table->string('state');
            $table->string('home_address');
            $table->boolean('deport_america');
            $table->boolean('check_america');
            $table->boolean('check_background');
            $table->enum('english_level',['Basic', 'Conversational', 'Fluent','Native'])->default('Basic');
            $table->enum('available_works',['Less_20', '40', 'over_40'])->default('Less_20');
            $table->boolean('have_computer');
            $table->boolean('have_monitor');
            $table->boolean('have_headset');
            $table->boolean('have_ethernet');
            $table->boolean('visit')->default(false);
            $table->date('approve_date')->nullable();
            $table->string('primary_contact')->nullable();
            $table->string('secondary_contact')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->enum('bank_account_type',['Ahorro', 'Corriente'])->nullable();
            $table->enum('status',['Pending', 'Rejected', 'approved', 'hired', 'disabled'])->default('Pending');
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
        Schema::dropIfExists('employees');
    }
}
