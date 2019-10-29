<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\CommissionGroup;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CommissionGroupController extends Controller
{
    public function show($id){
        $commissionGroup = CommissionGroup::find($id);
        
        return response()->json($commissionGroup);
    }
    public function index(Request $request){
        $commissionGroup = new CommissionGroup;
        $commissionGroup->assignSearch($request);
        $user = $request->user('api');
        if($user->type=="member" || $user->type=="employee")$commissionGroup->member_id = $user->member->id;
        if( ($user->type=="super" || $user->type=="admin")&&$request->exists("employee_id") )$commissionGroup->member_id = $request->input('employee_id');
        return response()->json($commissionGroup->search());
    }
}