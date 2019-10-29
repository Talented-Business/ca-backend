<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Timeoff;
use App\User;


class TimeoffController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Timeoff::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $user = $request->user('api');
        if($user->member && $user->member->activeContract){
            $employee_id = $user->member->id;
            $company_id = $user->member->activeContract->company_id;
        }else{
            return response()->json(array('status'=>'failed','errors'=>'non employee'));
        }
        $timeoff = new Timeoff;
        $timeoff->assign($request);        
        $timeoff->employee_id = $employee_id;
        $timeoff->company_id = $company_id;
        $timeoff->save();
        $timeoff->sendMail();
        return response()->json(array('status'=>'ok','timeoff'=>$timeoff));
    }
    public function update($id,Request $request)
    {
        /*$validator = Validator::make($request->all(), Timeoff::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }*/
        $timeoff = Timeoff::find($id);
        $timeoff->assign($request);
        $timeoff->save();
        $timeoff->sendMail();
        return response()->json(array('status'=>'ok','timeoff'=>$timeoff));
    }
    public function show($id){
        $timeoff = Timeoff::find($id);
        return response()->json($timeoff);
    }
    public function destroy($id){
        $timeoff = Timeoff::find($id);
        if($timeoff){
            $destroy=Timeoff::destroy($id);
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
        $timeoff = new Timeoff;
        $timeoff->assignSearch($request);
        return response()->json($timeoff->search());
    }
}