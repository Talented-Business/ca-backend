<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Commission;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Commission::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $commission = new Commission;
        $commission->assign($request);
        if($commission->group_id)$commission->save();
        return response()->json(array('status'=>'ok','commission'=>$commission));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), Commission::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $commission = Commission::find($id);
        $commission->assign($request);
        $commission->save();
        return response()->json(array('status'=>'ok','commission'=>$commission));
    }
    public function show($id){
        $commission = Commission::find($id);
        
        return response()->json($commission);
    }
}