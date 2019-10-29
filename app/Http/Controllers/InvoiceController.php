<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Invoice;
use App\InvoiceItem;
use App\User;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Invoice::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $invoice = new Invoice;
        $invoice->assign($request);        
        //save draft;
        $invoice->save();
        foreach($request->input('items') as $invoiceItem){
            $item = new InvoiceItem;
            $item->assign($invoiceItem);
            $item->invoice_id = $invoice->id;
            $item->save();
            $item->assignCommission();
        }
        if($invoice->status == "Proforma")$invoice->sendMailProforma();
        return response()->json(array('status'=>'ok','invoice'=>$invoice));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), Invoice::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $invoice = Invoice::find($id);
        if($invoice->status == "Invoice" || $invoice->status == "Proforma"){
            return response()->json(array('status'=>'failed','errors'=>"Invoice never changed"));
        }
        $oldItems = [];
        foreach($invoice->items as $item){
            $oldItems[$item->id] = $item;
        }
        $invoice->assign($request);
        $invoice->save();
        foreach($request->input('items') as $invoiceItem){
            if(isset($invoiceItem['id'])){
                $item = InvoiceItem::find($invoiceItem['id']);
                unset($oldItems[$invoiceItem['id']]);
            }else{
                $item = new InvoiceItem;
                $item->invoice_id = $invoice->id;
            }
            $item->assign($invoiceItem);
            $item->save();
            $item->assignCommission();
        }
        if(count($oldItems)>0)foreach($oldItems as $item){
            $item->unAssignCommission();
            $item->delete();
        }
        if($invoice->status == "Proforma")$invoice->sendMailProforma();
        return response()->json(array('status'=>'ok','invoice'=>$invoice));
    }
    public function updateStatus($id,Request $request)
    {
        $invoice = Invoice::find($id);
        if($invoice->status == "Invoice" || $invoice->status == "Recheck"){
            return response()->json(array('status'=>'failed','errors'=>"Invoice never changed"));
        }
        $invoice->status = $request->input("status");
        $invoice->save();
        if($request->input("status") == "Invoice"){
            $invoice->generateAdditionalItems();
            $invoice->sendMailInvoice();
        }
        return response()->json(array('status'=>'ok','invoice'=>$invoice));
    }
    public function paid($id)
    {
        $invoice = Invoice::find($id);
        if($invoice->status != "Invoice"){
            return response()->json(array('status'=>'failed','errors'=>"Invoice never changed"));
        }
        $invoice->status = "Paid";
        $invoice->sendMailPayment();
        $invoice->save();
        return response()->json(array('status'=>'ok','invoice'=>$invoice));
    }
    public function show($id){
        $invoice = Invoice::find($id);
        return response()->json($invoice);
    }
    public function destroy($id){
        $invoice = Invoice::find($id);
        if($invoice){
            $destroy=Invoice::destroy($id);
        }
        if ($destroy){
            $data=[
                'status'=>'1',
                'msg'=>'success'
            ];
        }else{
            $data=[
                'status'=>'0',
                'msg'=>'fail'
            ];
        }        
        return response()->json($data);
    }
    public function index(Request $request){
        $invoice = new Invoice;
        $invoice->assignSearch($request);
        $user = $request->user('api');
        if($user->type=="company"){
            $invoice->company_id = $user->companies[0]->id;
            $invoice->statuses = ['Proforma','Recheck','Invoice','Paid'];
        }
        if($user->type=="member" || $user->type=="employee" ){
            
            $invoice->statuses = ['Paid','Invoice'];
            $invoice->setEmployeeId($user->member->id);
        }
        if(($user->type=="admin" || $user->type=="super")&&$request->exists("employee_id") ){
            
            $invoice->statuses = ['Paid','Invoice'];
            $invoice->setEmployeeId($request->input('employee_id'));
        }
        return response()->json($invoice->search($user));
    }
    public function list(Request $request){
        $invoices = Invoice::whereIn('status',['Invoice','Paid'])->get();
        foreach($invoices as $invoice){
            $invoice->company;
        }
        return $invoices;
    }
}