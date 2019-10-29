<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use App\InvoiceItem;
use App\Employee;
use App\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProformaToCompany;
use App\Mail\InvoiceToCompany;
use App\Mail\PaymentToMember;

class Invoice extends Model
{
    protected $table = 'invoices';
    protected $fillable = ['invoicing_date','company_id','start_date','end_date','total','status'];
    private $_employeeId;
    private $pageSize;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'company_id'=>'required',
            'start_date'=>'required',
            'invoicing_date'=>'required',
            'end_date'=>'required',
            'total'=>'required',
        );
    }
    private static $searchableColumns = ['company_id','invoicing_date','status'];
    public function company()
    {
        return $this->belongsTo('App\Company');
    }    
    public function items()
    {
        return $this->hasMany('App\InvoiceItem');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
        if($request->exists('invoicing_date')){
            $this->invoicing_date = date("Y-m-d",strtotime($request->input('invoicing_date')));
        }
        if($request->exists('start_date')){
            $this->start_date = date("Y-m-d",strtotime($request->input('start_date')));
        }
        if($request->exists('end_date')){
            $this->end_date = date("Y-m-d",strtotime($request->input('end_date')));
        }
    }
    public function getInvoiceItems($employeeId){
        $items = InvoiceItem::where('invoice_id',$this->id)->where('employee_id',$employeeId)->get();
        foreach($items as $item){
            if(substr($item->slug,0,4) == 'time'){
                $item['hours'] = $item->amount;
                $item['hourly_rate'] = $item->employee->activeContract->contract->hourly_rate;
            }
        }
        return $items;
    }
    public function generateAdditionalItems(){
        $employeeIds = [];
        foreach($this->items as $item){
            if($item->employee_id && (in_array($item->employee_id,$employeeIds)==false))$employeeIds[] = $item->employee_id;
        }
        $config = new Config;
        $memberFee = $config->findByName('member_fee');
        foreach($employeeIds as $employeeId){
            $employee = Employee::find($employeeId);
            if($employee->activeContract){
                InvoiceItem::create([
                    'invoice_id'=>$this->id,
                    'employee_id'=>$employeeId,
                    'slug'=>'fee-'.$employeeId,
                    'task'=>'Member Fee',
                    'description'=>$employee->first_name." ".$employee->last_name,
                    'amount'=>$memberFee,
                    'total'=>$memberFee,
                    'pay'=>'member']);
                if($employee->activeContract)$deduction = $employee->activeContract->contract->deduction_item;
                else $deduction = null;
                if($deduction>0)InvoiceItem::create(['invoice_id'=>$this->id,
                                                    'employee_id'=>$employeeId,
                                                    'slug'=>'deduction-'.$employeeId,
                                                    'task'=>'Deduction',
                                                    'description'=>$employee->first_name." ".$employee->last_name,
                                                    'amount'=>$deduction,
                                                    'total'=>$deduction,
                                                    'pay'=>'member']);
            }
        }
    }
    public function sendMailProforma(){//to send Multi Comany
        if($this->status == "Proforma" ){
            foreach($this->company->users as $user){
                Mail::to($user->email)->send(new ProformaToCompany($this->company->name,$user->name,$this->start_date,$this->end_date));
            }
        }
    }
    public function sendMailInvoice(){
        if($this->status == "Invoice" ){
            foreach($this->company->users as $user){
                Mail::to($user->email)->send(new InvoiceToCompany($this->company->name,$user->name,$this->id,$this->total,$this->start_date."~".$this->end_date));
            }
        }
    }
    public function sendMailPayment(){
        if($this->status == "Paid" ){
            foreach($this->company->activeContracts as $activeContract){
                $employee = $activeContract->contract->employee;
                $hours = null;
                $sales = null;
                foreach($this->items as $item){
                    if($item->employee_id == $employee->id){
                        if($item->slug == "time-".$employee->id)$hours = $item->total;
                        if($item->slug == "sales-".$employee->id)$sales = $item->total;                        
                    }
                }
                Mail::to($employee->personal_email)->send(new PaymentToMember($employee->last_name,$hours,$sales,$this->start_date."~".$this->end_date));
            }
        }
    }
    private function getPaymentTotal($items){
        $total = 0;
        foreach($items as $item){
            if($item->pay == 'company')$total += $item->total;
            if($item->pay == 'member')$total -= $item->total;
        }
        return $total;
    }
    public function search($user){
        if($this->_employeeId){
            $where = Invoice::whereRaw('1')
            ->where(function($query){
                $query->whereHas('items', function($q){
                    $q->where('employee_id','=',$this->_employeeId);
                });
            });            
            if($this->statuses)$where->whereIn('status',$this->statuses);            
            $currentPage = $this->pageNumber+1;
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });      
            $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
            $items = $response->items();
            foreach($items as $index=> $invoice){
                $invoice->company;
                $items[$index]['payments']=$invoice->getInvoiceItems($this->_employeeId);
                $invoice->total = $this->getPaymentTotal($items[$index]['payments']);
            }
        }else{
            $where = Invoice::whereRaw('1')
            ->where(function($query){
                foreach(self::$searchableColumns as $property){
                    if($this->{$property}!=null){
                        $query->Where($property,'like','%'.$this->{$property}.'%');
                    }
                }
            });
            if($this->statuses)$where->whereIn('status',$this->statuses); 
            $currentPage = $this->pageNumber+1;
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });      
            $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
            $items = $response->items();
            foreach($items as $index=> $invoice){
                $invoice->company;
                if($user->type=="company"){
                    $invoiceItems = [];
                    foreach($invoice->items as $invocieItem){
                        if($invocieItem->pay == 'company') $invoiceItems[] = $invocieItem;
                    }
                    $items[$index]['items'] = $invoiceItems;
                }
                else $invoice->items;
            }
        }
        return $response;
    }
    public function setEmployeeId($value){
        $this->_employeeId = $value;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
    public static function findRecent($companyId,$limit=10){
        $items = Invoice::where('company_id',$companyId)
            ->whereIn('status',['Proforma','Recheck','Invoice','Paid'])
            ->skip(0)
            ->take($limit)
            ->orderBy('created_at', 'DESC')
            ->get();
        foreach($items as $index=> $invoice){
            $invoice->company;
        }        
        return $items;
    }
}
