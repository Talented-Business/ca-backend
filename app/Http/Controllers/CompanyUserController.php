<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\CompanyUser;
use App\User;
use Illuminate\Support\Facades\Hash;

class CompanyUserController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), CompanyUser::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $user = CompanyUser::createUser($request);
        return response()->json(array('status'=>'ok','companyUser'=>$user));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), CompanyUser::validateRules($id));
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        try{
            $user = User::find($id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            if($request->input('password')!=""&&$request->input('password') == $request->input('password_confirm'))$user->password = Hash::make($request->input('password'));
            $user->save();
        }
        catch(PDOException $e){
            var_dump($e);
        }
        return response()->json(array('status'=>'ok','companyUser'=>$user));
    }
    public function updateStatus($id){
        $companyUser = new CompanyUser;
        $companyUser = CompanyUser::findUser($id);
        if($companyUser){
            $companyUser->status = !$companyUser->status;
            $companyUser->save();
        }
        return response()->json($companyUser);
    }
    public function show($id){
        $companyUser = User::find($id);
        return response()->json($companyUser);
    }
    public function index(Request $request){
        $companyUser = new CompanyUser;
        $companyUser->assignSearch($request);
        return response()->json($companyUser->search());
    }
}