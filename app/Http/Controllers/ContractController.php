<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Contract;
use App\ActiveContract;
use App\User;
use App\Proposal;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Contract::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $contract = new Contract;
        $contract->assign($request);
        $contract->save();
        if($contract->id>0){
            ActiveContract::create(array('contract_id'=>$contract->id,'employee_id'=>$contract->employee_id,'company_id'=>$contract->company_id));
            $contract->employee->status = 'hired';
            $contract->employee->save();
            $companyUsers = DB::table('company_departments')->where('company_id',$contract->company_id)->where('department_id', $contract->department_id)->get();
            if(isset($companyUsers[0])==false)DB::table('company_departments')->insert(
                ['company_id' => $contract->company_id, 'department_id' => $contract->department_id]
            );
            if($request->exists('proposal_id')){
                $proposal = Proposal::find($request->input('proposal_id'));
                $proposal->status = 'hired';
                $proposal->save();
            }
        }    
        return response()->json(array('status'=>'ok','contract'=>$contract));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), Contract::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $contract = Contract::find($id);
        $contract->assign($request);
        $contract->save();
        return response()->json(array('status'=>'ok','contract'=>$contract));
    }
    public function updateStatus($id){
        $contract = Contract::find($id);
        $contract->end_date = date("Y-m-d");
        $contract->save();
        $contract->activeContract->delete();
        return response()->json($contract);
    }
    public function show($id){
        $contract = Contract::find($id);
        
        return response()->json($contract);
    }
    public function index(Request $request){
        $contract = new Contract;
        $contract->assignSearch($request);
        return response()->json($contract->search());
    }
}