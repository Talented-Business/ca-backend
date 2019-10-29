<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedInteger('company_id');
            $table->string('title')->nullable();
            $table->date('start_date');//hired date
            $table->date('end_date')->nullable();
            $table->string('position');
            $table->unsignedInteger('department_id');
            $table->string('work_location');
            $table->string('employment_type');
            $table->string('employment_status');
            $table->string('manager');
            $table->string('worksnap_id');
            $table->string('pay_days');
            $table->decimal('deduction_item',8,2)->default(0);
            $table->string('compensation');
            $table->decimal('hourly_rate',8,2);
            $table->decimal('hours_per_day_period',8,2);

            $table->foreign('employee_id')
                ->references('id')
                ->on("employees")
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->foreign('department_id')
                ->references('id')
                ->on("departments")
                ->onDelete('cascade');

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
        Schema::dropIfExists('contracts');
    }
}
