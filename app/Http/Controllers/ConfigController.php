<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Config;
use App\Timeoff;
use App\Proposal;
use App\Invoice;
use App\Employee;
use App\Department;
use App\Contract;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    public function save(Request $request)
    {
        $contract = new Config;
        $contract->saveConfig($request);
        return response()->json(array('status'=>'ok','contract'=>$contract));
    }
    public function index(){
        $items = Config::all();
        $config = [];
        foreach($items as $item){
            $config[$item['name']] = $item['value'];
        }
        return response()->json($config);
    }
    public function dashboard(Request $request){
        $user = $request->user('api');
        $response = null;
        switch($user->type){
            case "super":case "admin":
                $year = $request->input('year');
                $invoices = Invoice::where('invoicing_date','like',$year.'-%')->get();
                $amounts = ['hours-sales'=>[],'company-fee'=>[],'member-fee'=>[],'deduction'=>[]]; 
                $monthes = [];
                foreach($invoices as $invoice){
                    $month = date('m',strtotime($invoice->invoicing_date));
                    $m = date('M',strtotime($invoice->invoicing_date));
                    $monthes[$month] = $m;
                    foreach($invoice->items as $item){
                        if($item->slug == 'company-fee'){
                            $slug = "company-fee";
                        }else if($item->slug == 'fee-'.$item->employee_id){
                            $slug = "member-fee";
                        }else if($item->slug == 'deduction-'.$item->employee_id){
                            $slug = "deduction";
                        }else{
                            $slug = "hours-sales";
                        }
                        if(isset($amounts[$slug][$month])){
                            $amounts[$slug][$month] += $item->total;
                        }else{
                            $amounts[$slug][$month] = $item->total;
                        }
                    }
                }
                $labels = [];
                foreach($monthes as $month=>$m){
                    $labels[] = $m;
                }
                $invoiced = [];
                foreach($monthes as $month=>$m){
                    $invoiced[] = $amounts['hours-sales'][$month] + $amounts['company-fee'][$month];
                }
                $fee = [];
                foreach($monthes as $month=>$m){
                    $pay = $amounts['member-fee'][$month]+$amounts['company-fee'][$month];
                    $fee[] = $pay;
                }
                        
                $invoices=['labels'=>$labels,
                    'invoices'=>$invoiced,
                    'fees'=>$fee
                ];
                $labels = [];
                $data = [];
                $departmentItems = Department::where('status',1)->get();
                foreach($departmentItems as $department){
                    $count = Contract::whereHas('activeContract')->where('department_id','=',$department->id)->count();
                    if($count>0){
                        $labels[] = $department->name;
                        $data[] = $count;
                    }
                }
                $departments=['labels'=>$labels,'data'=>$data];
                $males = Employee::where('gender','Male')->whereIn('status',['approved','hired'])->count();
                $females = Employee::where('gender','Female')->whereIn('status',['approved','hired'])->count();
                $response = ['employees'=>[$males,$females],'departments'=>$departments,'invoices'=>$invoices];
            break;
            case "company":
                $requests=Timeoff::findRecent($user->companies[0]->id,5);
                $applicants=Proposal::findRecent($user->companies[0]->id,5);
                $invoices=Invoice::findRecent($user->companies[0]->id,5);
                $response = ['requests'=>$requests,'applicants'=>$applicants,'invoices'=>$invoices];
            break;
            case "member":case "employee":
                $requests=Timeoff::findRecentByMember($user->member->id,5);
                if($user->member->activeContract){
                    $departmentId = $user->member->activeContract->contract->department_id;
                    $hours = $user->member->activeContract->contract->company->getHours($departmentId);
                }else{
                    $hours = null;
                }
                $response = ['requests'=>$requests,'hours'=>$hours];
            break;
        }
        return response()->json($response);
    }
}