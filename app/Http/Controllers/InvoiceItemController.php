<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Invoice;
use App\InvoiceItem;
use App\User;
use App\ActiveContract;
use App\Employee;
use App\Config;

class InvoiceItemController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Invoice::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $invoice = new InvoiceItem;
        $invoice->assign($request);        
        $invoice->save();
        return response()->json(array('status'=>'ok','invoice'=>$invoice));
    }
    public function destroy($id){
        $invoice = InvoiceItem::find($id);
        if($invoice){
            $destroy=InvoiceItem::destroy($id);
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
        $items = InvoiceItem::where('invoice_id',$request->input('invoice_id'))->get();
        return response()->json($items);
    }
    public function worksnap(Request $request){
        $validator = InvoiceItem::validateWorksnaps();
        if ($validator==false) {
            return response()->json(array('status'=>'failed','errors'=>"Worksnaps Api Key is incorrect, please check"));
        }
        $companyId = $request->input('company_id');
        $activeContracts = ActiveContract::with('contract')->where('company_id','=',$companyId)->get();
        $worksnapIds = [];
        $employeeIds = [];
        $employeeWorksnapIds = [];
        foreach($activeContracts as $activeContract){
            $worksnapIds[] = $activeContract->contract->worksnap_id;
            $employeeIds[] = $activeContract->employee_id;
            $employeeWorksnapIds[$activeContract->employee_id] = ['id'=>$activeContract->contract->worksnap_id,'rate'=>$activeContract->contract->hourly_rate];
        }
        $fromDate = date("Y-m-d 00:00:00",strtotime($request->input('from')));
        $toDate = date("Y-m-d 23:59:59",strtotime($request->input('to')));
        //$workingHours = ["47675"=>3460,"44962"=>1890,"41712"=>1140];
        $workingHours = InvoiceItem::getWorkingHours($worksnapIds,$fromDate,$toDate);
        $sales = InvoiceItem::getSales($employeeIds,$fromDate,$toDate);
        $invoiceItems = [];
        foreach( $employeeIds as $employeeId){
            $employee = Employee::find($employeeId);
            if(isset($sales[$employeeId]))$invoiceItems[] = ['task'=>'Sales',
                'description'=>$employee->first_name." ".$employee->last_name,
                'employee_id'=>$employeeId,
                'slug'=>'sales-'.$employeeId,
                'amount'=>round($sales[$employeeId],2),
                'rate'=>null,
                'total'=>round($sales[$employeeId],2)];
            if(isset($workingHours[$employeeWorksnapIds[$employeeId]['id']]))$invoiceItems[] = ['task'=>'Time',
                'slug'=>'time-'.$employeeId,
                'description'=>$employee->first_name." ".$employee->last_name,
                'employee_id'=>$employeeId,
                'amount'=>round($workingHours[$employeeWorksnapIds[$employeeId]['id']]/60,2),
                'rate'=>$employeeWorksnapIds[$employeeId]['rate'],
                'total'=>round($workingHours[$employeeWorksnapIds[$employeeId]['id']]/60*$employeeWorksnapIds[$employeeId]['rate'],2)];
        }
        $config = new Config;
        $companyFee = $config->findByName('company_fee');
        $invoiceItems[] = ['task'=>'Fee','description'=>"Company Fee", 'slug'=>'company-fee','amount'=>$companyFee,'rate'=>null,'total'=>$companyFee];
        return response()->json($invoiceItems);
    }
}