<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Proposal;

class ProposalController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), Proposal::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $proposal = new Proposal;
        $proposal->job_id = $request->input('job_id');
        $user = $request->user('api');
        $proposal->user_id = $user->id;
        $proposal->employee_id = $user->member->id;
        $proposal->save();
        return response()->json(array('status'=>'ok','proposal'=>$proposal));
    }
    public function update($id,Request $request)
    {
        $proposal = Proposal::find($id);
        $proposal->status = $request->input('status');
        if($proposal->status == 'inreview' && $request->exists('company_id')){
            $proposal->company_id = $request->input('company_id');
        }
        $proposal->save();
        return response()->json(array('status'=>'ok','proposal'=>$proposal));
    }
    public function updateStatus($id,Request $request){
        $proposal = Proposal::find($id);
        $proposal->status = $request->input('status');
        $proposal->save();
        return response()->json($proposal);
    }
    public function show($id){
        $proposal = Proposal::find($id);
        $proposal->skills;
        return response()->json($proposal);
    }
    public function index(Request $request){
        $proposal = new Proposal;
        $proposal->assignSearch($request);
        return response()->json($proposal->search());
    }
}