<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class InvoiceItemUnassignTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    private function createInvoice(){
        $employee = factory(\App\Employee::class)->create();
        $invoice = factory(\App\Invoice::class)->create(['start_date'=>'2019-10-07','end_date'=>'2019-10-13','status'=>'Draft']);
        $invoiceItem = factory(\App\InvoiceItem::class)->create(['invoice_id'=>$invoice->id,'employee_id'=>$employee->id]);
        $commissionGroup = factory(\App\CommissionGroup::class)->create(['member_id'=>$employee->id,'invoice_id'=>$invoice->id]);
        return [$employee,$invoice,$invoiceItem,$commissionGroup];
    }
    public function testUpdateInvoiceItem()
    {
        list($employee,$invoice,$invoiceItem,$commissionGroup) = $this->createInvoice();
        factory(\App\Commission::class,3)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-07 13:51:40','updated_at'=>'2019-10-07 13:51:40']);
        foreach($commissionGroup->items as $item){
            //var_dump($item->created_at);
        }
        $invoiceItem->unAssignCommission();
        $this->assertDatabaseHas('commission_groups',['invoice_id'=>null,'member_id'=>$employee->id,'id'=>$commissionGroup->id]);
    }
    public function testUpdateInvoiceItemTwo()
    {
        list($employee,$invoice,$invoiceItem,$commissionGroup) = $this->createInvoice();
        $commission1 = factory(\App\Commission::class)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-07 13:51:40','updated_at'=>'2019-10-07 13:51:40']);
        $commission2 = factory(\App\Commission::class)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-07 13:51:40','updated_at'=>'2019-10-07 13:51:40']);
        $commission3 = factory(\App\Commission::class)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-07 13:51:40','updated_at'=>'2019-10-07 13:51:40']);
        $commissionGroup1 = factory(\App\CommissionGroup::class)->create(['member_id'=>$employee->id,'invoice_id'=>null]);
        factory(\App\Commission::class)->create(['group_id'=>$commissionGroup1->id,'created_at'=>'2019-10-14 13:51:40','updated_at'=>'2019-10-14 13:51:40']);
        foreach($commissionGroup->items as $item){
            //var_dump($item->created_at);
        }
        $invoiceItem->unAssignCommission();
        $this->assertDatabaseHas('commissions',['group_id'=>$commissionGroup1->id,'id'=>$commission1->id]);
        $this->assertDatabaseHas('commissions',['group_id'=>$commissionGroup1->id,'id'=>$commission2->id]);
        $this->assertDatabaseHas('commissions',['group_id'=>$commissionGroup1->id,'id'=>$commission3->id]);
        $group = \App\CommissionGroup::find($commissionGroup->id);
        $this->assertNull($group);
    }
}
