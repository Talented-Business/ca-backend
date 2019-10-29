<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class InvoiceItemTest extends TestCase
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
        $commissionGroup = factory(\App\CommissionGroup::class)->create(['member_id'=>$employee->id]);
        return [$employee,$invoice,$invoiceItem,$commissionGroup];
    }
    public function testCreateInvoiceItemContain()
    {
        list($employee,$invoice,$invoiceItem,$commissionGroup) = $this->createInvoice();
        factory(\App\Commission::class,3)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-07 13:51:40','updated_at'=>'2019-10-07 13:51:40']);
        foreach($commissionGroup->items as $item){
            //var_dump($item->created_at);
        }
        $invoiceItem->assignCommission();
        $this->assertDatabaseHas('commission_groups',['invoice_id'=>$invoice->id,'member_id'=>$employee->id]);
    }
    public function testCreateInvoiceItemOut()
    {
        list($employee,$invoice,$invoiceItem,$commissionGroup) = $this->createInvoice();
        factory(\App\Commission::class,3)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-14 13:51:40','updated_at'=>'2019-10-14 13:51:40']);
        foreach($commissionGroup->items as $item){
            //var_dump($item->created_at);
        }
        $invoiceItem->assignCommission();
        $this->assertDatabaseHas('commission_groups',['invoice_id'=>null,'member_id'=>$employee->id]);
    }
    public function testCreateInvoiceItemCross()
    {
        list($employee,$invoice,$invoiceItem,$commissionGroup) = $this->createInvoice();
        factory(\App\Commission::class,3)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-07 13:51:40','updated_at'=>'2019-10-07 13:51:40']);
        factory(\App\Commission::class)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-14 13:51:40','updated_at'=>'2019-10-14 13:51:40']);
        foreach($commissionGroup->items as $item){
            //var_dump($item->created_at);
        }
        $invoiceItem->assignCommission();
        $this->assertDatabaseHas('commission_groups',['invoice_id'=>$invoice->id,'member_id'=>$employee->id]);
        $this->assertDatabaseHas('commission_groups',['invoice_id'=>null,'member_id'=>$employee->id]);
        $this->assertDatabaseHas('commissions',['group_id'=>$commissionGroup->id+1]);
    }
    public function testUpdateInvoiceItemContain()
    {
        list($employee,$invoice,$invoiceItem,$commissionGroup) = $this->createInvoice();
        factory(\App\Commission::class,3)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-07 13:51:40','updated_at'=>'2019-10-07 13:51:40']);
        $commission = factory(\App\Commission::class)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-14 13:51:40','updated_at'=>'2019-10-14 13:51:40']);
        $invoiceItem->assignCommission();
        $invoice->end_date = "2019-10-15";
        $invoice->save();
        $invoiceItem->invoice->end_date = "2019-10-15";
        $invoiceItem->assignCommission(true);
        $this->assertDatabaseHas('commissions',['group_id'=>$commissionGroup->id,'id'=>$commission->id]);
    }
    public function testUpdateInvoiceItemCross()
    {
        list($employee,$invoice,$invoiceItem,$commissionGroup) = $this->createInvoice();
        factory(\App\Commission::class,3)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-07 13:51:40','updated_at'=>'2019-10-07 13:51:40']);
        $commission = factory(\App\Commission::class)->create(['group_id'=>$commissionGroup->id,'created_at'=>'2019-10-13 13:51:40','updated_at'=>'2019-10-13 13:51:40']);
        $invoiceItem->assignCommission();
        $invoice->end_date = "2019-10-12";
        $invoice->save();
        $invoiceItem->invoice->end_date = "2019-10-12";
        $invoiceItem->assignCommission();
        $this->assertDatabaseHas('commissions',['group_id'=>$commissionGroup->id+1,'id'=>$commission->id]);
        $this->assertDatabaseHas('commission_groups',['invoice_id'=>null,'member_id'=>$employee->id]);
    }
}
