<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreateInvoice()
    {
        $employee1 = factory(\App\Employee::class)->create(['id'=>1, 'first_name'=>'Columnbus', 'last_name'=>'Zhone' ]);
        $employee2 = factory(\App\Employee::class)->create(['id'=>3, 'first_name'=>'Member', 'last_name'=>'Last' ]);
        $employee3 = factory(\App\Employee::class)->create(['id'=>4, 'first_name'=>'Member3', 'last_name'=>'Last' ]);
        $employee4 = factory(\App\Employee::class)->create(['id'=>5, 'first_name'=>'Member5', 'last_name'=>'Last' ]);
        $data = [
            'company_id' => 1,
            'invoicing_date' => "2019-10-07",
            'start_date' => "2019-10-07",
            'end_date' => "2019-10-13",
            'status' => "Draft",
            'total' => 2973.33,
            'items' =>[
                    ['amount'=>34,'description'=>'Member5 Last','employee_id'=>5,'rate'=>null,'slug'=>'sales-5','task'=>"Sales","total"=>34],
                    ['amount'=>57.67,'description'=>'Member5 Last','employee_id'=>5,'rate'=>"32.00",'slug'=>'time-5','task'=>"Time","total"=>1845.33],
                    ['amount'=>39.83,'description'=>'Columnbus Zhone','employee_id'=>1,'rate'=>"12.00",'slug'=>'time-1','task'=>"Time","total"=>478],
                    ['amount'=>31.5,'description'=>'Member Last','employee_id'=>3,'rate'=>"12.00",'slug'=>'time-3','task'=>"Time","total"=>378],
                    ['amount'=>19,'description'=>'Member3 Last','employee_id'=>4,'rate'=>"12.00",'slug'=>'time-4','task'=>"Time","total"=>228],
                    ['amount'=>10,'description'=>'Company Fee','employee_id'=>null,'rate'=>null,'slug'=>'company-fee','task'=>"Fee","total"=>10],
                ]
            ];            
        $response = $this->json('POST', '/api/invoices',$data);        
        $this->assertDatabaseHas('invoices',['id'=>1,'company_id'=>1]);
    }
    public function testUpdateInvoice()
    {
        $invoice = factory(\App\Invoice::class)->create(['start_date'=>'2019-10-07','end_date'=>'2019-10-13']);        
        $employee1 = factory(\App\Employee::class)->create(['id'=>1, 'first_name'=>'Columnbus', 'last_name'=>'Zhone' ]);
        $employee2 = factory(\App\Employee::class)->create(['id'=>2, 'first_name'=>'Member2', 'last_name'=>'Last' ]);
        $employee3 = factory(\App\Employee::class)->create(['id'=>3, 'first_name'=>'Member', 'last_name'=>'Last' ]);
        $employee4 = factory(\App\Employee::class)->create(['id'=>4, 'first_name'=>'Member3', 'last_name'=>'Last' ]);
        $employee5 = factory(\App\Employee::class)->create(['id'=>5, 'first_name'=>'Member5', 'last_name'=>'Last' ]);
        $invoiceItem1 = factory(\App\InvoiceItem::class)->create(['invoice_id'=>$invoice->id,'employee_id'=>$employee1->id]);
        $invoiceItem2 = factory(\App\InvoiceItem::class)->create(['invoice_id'=>$invoice->id,'employee_id'=>$employee3->id]);
        $invoiceItem3 = factory(\App\InvoiceItem::class)->create(['invoice_id'=>$invoice->id,'employee_id'=>$employee4->id]);
        $invoiceItem4 = factory(\App\InvoiceItem::class)->create(['invoice_id'=>$invoice->id,'employee_id'=>null]);
        $invoiceItem5 = factory(\App\InvoiceItem::class)->create(['invoice_id'=>$invoice->id,'employee_id'=>$employee2->id]);
        $data = [
            'company_id' => 1,
            'invoicing_date' => "2019-10-07",
            'start_date' => "2019-10-07",
            'end_date' => "2019-10-13",
            'status' => "Draft",
            'total' => 2973.33,
            'items' =>[
                    ['amount'=>34,'description'=>'Member5 Last','employee_id'=>$employee5->id,'rate'=>null,'slug'=>'sales-5','task'=>"Sales","total"=>34],
                    ['amount'=>57.67,'description'=>'Member5 Last','employee_id'=>$employee5->id,'rate'=>"32.00",'slug'=>'time-5','task'=>"Time","total"=>1845.33],
                    ['amount'=>39.83,'description'=>'Columnbus Zhone','employee_id'=>$employee1->id,'rate'=>"12.00",'slug'=>'time-1','task'=>"Time","total"=>478,'id'=>$invoiceItem1->id],
                    ['amount'=>31.5,'description'=>'Member Last','employee_id'=>$employee3->id,'rate'=>"12.00",'slug'=>'time-3','task'=>"Time","total"=>378,'id'=>$invoiceItem2->id],
                    ['amount'=>19,'description'=>'Member3 Last','employee_id'=>$employee4->id,'rate'=>"12.00",'slug'=>'time-4','task'=>"Time","total"=>228,'id'=>$invoiceItem3->id],
                    ['amount'=>10,'description'=>'Company Fee','employee_id'=>null,'rate'=>null,'slug'=>'company-fee','task'=>"Fee","total"=>10,'id'=>$invoiceItem4->id],
                ]
            ];            
        $response = $this->json('put', '/api/invoices/'.$invoice->id,$data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('invoice_items',['invoice_id'=>$invoice->id,'slug'=>'sales-5']);
        $this->assertDatabaseHas('invoice_items',['invoice_id'=>$invoice->id,'slug'=>'time-5']);
        $this->assertDatabaseHas('invoice_items',['invoice_id'=>$invoice->id,'slug'=>'time-1','id'=>$invoiceItem1->id]);
        $this->assertDatabaseHas('invoice_items',['invoice_id'=>$invoice->id,'slug'=>'time-3','id'=>$invoiceItem2->id]);
        $this->assertDatabaseHas('invoice_items',['invoice_id'=>$invoice->id,'slug'=>'time-4','id'=>$invoiceItem3->id]);
        $this->assertDatabaseHas('invoice_items',['invoice_id'=>$invoice->id,'slug'=>'company-fee','id'=>$invoiceItem4->id]);
        $invoiceItem = \App\InvoiceItem::find($invoiceItem5->id);
        $this->assertNull($invoiceItem);
    }
}
