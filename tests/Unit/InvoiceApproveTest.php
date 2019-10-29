<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class InvoiceApproveTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    private function createInvoice(){
        factory(\App\Config::class)->create(['name'=>'company_fee','value'=>10]);
        factory(\App\Config::class)->create(['name'=>'member_fee','value'=>30]);
        $department = factory(\App\Department::class)->create();
        $employee = factory(\App\Employee::class)->create();
        $company = factory(\App\Company::class)->create();
        $contract = factory(\App\Contract::class)->create(['company_id'=>$company->id,'employee_id'=>$employee->id,'deduction_item'=>50,'department_id'=>$department->id]);
        $activeContract = factory(\App\ActiveContract::class)->create(['company_id'=>$company->id,'employee_id'=>$employee->id,'contract_id'=>$contract->id]);
        $invoice = factory(\App\Invoice::class)->create(['start_date'=>'2019-10-07','end_date'=>'2019-10-13','status'=>'Invoice']);
        $invoiceItem = factory(\App\InvoiceItem::class)->create(['invoice_id'=>$invoice->id,'employee_id'=>$employee->id]);
        $commissionGroup = factory(\App\CommissionGroup::class)->create(['member_id'=>$employee->id]);
        return [$employee,$invoice,$invoiceItem,$commissionGroup];
    }
    public function testApproveInvoice()
    {
        list($employee,$invoice,$invoiceItem,$commissionGroup) = $this->createInvoice();
        factory(\App\Commission::class,3)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-07 13:51:40','updated_at'=>'2019-10-07 13:51:40']);
        $invoice->generateAdditionalItems();

        $this->assertDatabaseHas('invoice_items',['invoice_id'=>$invoice->id,'pay'=>"member",'total'=>30]);
        $this->assertDatabaseHas('invoice_items',['invoice_id'=>$invoice->id,'pay'=>"member",'total'=>50]);
    }

}
