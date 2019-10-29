<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Employee;
use App\User;
use App\CurrentAssign;
use App\AssetAssign;
use App\Asset;
use App\Rules\UniqueEmail;
use App\Mail\RecruitCreate;
use App\Mail\RecruitApproved;
use App\Mail\RecruitRejected;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $valid = new UniqueEmail();
        $validator = Validator::make($request->all(), Employee::validateRules(null,$valid));
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $employee = new Employee;
        $response = $employee->assign($request);
        if($response===true){
            $employee->save();
            $employee->saveDocuments();
            $superAdminEmails = User::superAdminEmail();//multi
            if(!empty($superAdminEmails)){
                foreach($superAdminEmails as $email){
                    Mail::to($email)->send(new RecruitCreate($employee->first_name,$employee->last_name,$employee->home_address,$employee->personal_email,$employee->mobile_phone_number));
                }
            }
            return response()->json(array('status'=>'ok','employee'=>$employee));
        }else{
            return response()->json(array('status'=>'failed','errors'=>$response));
        }
    }
    public function update($id,Request $request)
    {
        $valid = new UniqueEmail($id);
        $validator = Validator::make($request->all(), Employee::validateRules($id,$valid));
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $employee = Employee::find($id);
        $response = $employee->assign($request);
        if($response === true){
            $employee->skills()->detach();
            $employee->skills()->attach($request->input('skills'));
            $employee->save();
            $employee->savePhotos();
            //$employee->skills;
            //$employee->photos;    
            return response()->json(array('status'=>'ok','employee'=>$employee));
        }else{
            return response()->json(array('status'=>'failed','errors'=>$response));
        }
    }
    public function updateDocument($id,Request $request)
    {
        $employee = Employee::find($id);
        $field = $request->input('field');
        if($request->hasFile('path')&&$request->file('path')->isValid()){ 
            $path = $request->path->store($field);
            $employee->{$field.'_path'} = $path;
            $employee->{$field.'_date'} = date("Y-m-d");
        }else{
            return response()->json(array('status'=>'failed','errors'=>$request->file('path')->getErrorMessage()));
        }
        $employee->save();
        return response()->json(array('status'=>'ok','employee'=>$employee));
    }
    public function deleteDocument($id,Request $request)
    {
        $employee = Employee::find($id);
        $field = $request->input('field');
        $employee->{$field.'_path'} = "";
        $employee->{$field.'_date'} = null;
        $employee->save();
        return response()->json(array('status'=>'ok'));
    }
    public function convert(Request $request)
    {
        $id = $request->input('id');
        $employee = Employee::find($id);
        $employee->skills()->detach();
        $employee->skills()->attach($request->input('skills'));
        $employee->status = "approved";
        if($employee->user===null){
            $password = Str::random(8);
            $user = User::create(['name'=>$employee->first_name.' '.$employee->last_name,'email'=>$employee->personal_email,'type'=>'member','password' => Hash::make($password)]);
            $employee->user_id = $user->id;
            $employee->approve_date = date('Y-m-d');
        }
        $employee->save();
        $website = URL::to('/');
        Mail::to($user->email)->send(new RecruitApproved($user->name,$password,$website,$user->email));
        return response()->json(array('status'=>'ok','employee'=>$request->input('skills')));
    }
    public function reject(Request $request)
    {
        $id = $request->input('id');
        $employee = Employee::find($id);
        $employee->status = "Rejected";
        $employee->save();
        Mail::to($employee->personal_email)->send(new RecruitRejected($employee->first_name.' '.$employee->last_name));
        return response()->json(array('status'=>'ok','employee'=>$employee));
    }
    public function updateUser(Request $request){
        $user = $request->user('api');
        $member = $user->member;
        $valid = new UniqueEmail($member->id);
        $validator = Validator::make($request->all(), Employee::validateRulesForUser($valid));
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $password = false;
        if($request->exists('current_password')){
            if (Hash::check($request->current_password, $user->password)==false) {
                return response()->json(array('status'=>'failed','errors'=>['password'=>'current_password_falied']));
            }
            if( $request->new_password != $request->confirm_password ){
                return response()->json(array('status'=>'failed','errors'=>['password'=>'password_unmatched']));
            }
            $password = true;
        }
        DB::beginTransaction();
        try{
            $member->personal_email = $request->input('email');
            $member->home_phone_number = $request->input('home_phone_number');
            $member->mobile_phone_number = $request->input('mobile_phone_number');
            $member->save();
            $user->email = $request->input('email');
            if($password)$user->password = Hash::make($request->new_password);
            $user->save();
            $user->member;
            DB::commit();
            return response()->json(array('status'=>'ok','user'=>$user));
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
        return response()->json(array('status'=>'failed'));
    }
    public function updateBank(Request $request){
        $user = $request->user('api');
        $member = $user->member;
        $validator = Validator::make($request->all(), Employee::validateRulesForBank());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $member->bank_name = $request->input('bank_name');
        $member->bank_account_name = $request->input('bank_account_name');
        $member->bank_account_number = $request->input('bank_account_number');
        $member->bank_account_type = $request->input('bank_account_type');
        $member->save();
        return response()->json(array('status'=>'ok','employee'=>$member));

    }
    public function show($id){
        $employee = Employee::find($id);
        $employee->skills;
        $employee->photos;
        $employee->documents;
        $employee->convertString();
        $employee['created_date'] = date("F d Y H:i",strtotime($employee->created_at));
        return response()->json($employee);
    }
    public function index(Request $request){
        $employee = new Employee;
        $user = $request->user('api');
        $result = array();
        switch($user->type){
            case "admin":case "super":
                $employee->assignSearch($request);
                $result = $employee->search();
                break;
            case "company":
                if($request->input('company_id')){
                    $employee->companySearch($request);
                    $result = $employee->searchCurrentEmployees();
                }
                break;
        }
        return response()->json($result);
    }
    public function unhired(){
        $employee = new Employee;
        return response()->json($employee->unhired());
    }
    public function confirmed(){
        $employee = new Employee;
        return response()->json($employee->confirmed());
    }
    public function disable($id){
        DB::beginTransaction();
        try{
            $employee = Employee::find($id);
            $employee->status = "disabled";
            $employee->save();
            $employee->user->active = 0;
            if($employee->activeContract){
                $employee->activeContract->contract->end_date = date("Y-m-d");
                $employee->activeContract->contract->save();
                $employee->activeContract->delete();
            }
            $employee->user->save();
            DB::commit();
            $status = "ok";
        }  catch (\Exception $e) {
            DB::rollback();
            $status = "failed";
        }
        return response()->json(['status'=>$status]);
    }
    public function restore($id){
        DB::beginTransaction();
        try{
            $employee = Employee::find($id);
            $employee->status = "approved";
            $employee->save();
            $employee->user->active = 1;
            $employee->user->save();
            DB::commit();
            $status = "ok";
        }  catch (\Exception $e) {
            DB::rollback();
            $status = "failed";
        }
        return response()->json(['status'=>$status]);
    }
    public function findRecruits(Request $request){
        $employee = new Employee;
        $employee->assignSearch($request);
        $response = $employee->search();
        return response()->json($response);
    }
    public function assets(Request $request){
        $employee = Employee::find(3);
        Mail::to($employee->personal_email)->send(new RecruitRejected($employee->first_name.' '.$employee->last_name));
    /*DB::enableQueryLog();
       $employee_id = 4;
       $assignTable = (new AssetAssign)->getTable();
       $currentTable =(new CurrentAssign)->getTable();
       $assetTable =(new Asset)->getTable();
       $assets = DB::table($assetTable)
           ->select($assetTable.".*")
           ->join($assignTable,$assetTable.'.id', '=', $assignTable.'.asset_id')
           ->join($currentTable,$assignTable.'.id', '=', $currentTable.'.asset_assign_id')
           ->where($assignTable.'.employee_id',$employee_id)->get();

        $query = DB::getQueryLog();
        $query = end($query);
        dd($query);*/
        //$result = DB::table('company_departments')->where('company_id',1)->where('department_id', 3)->get();
        //var_dump(isset($result[0]));
        //$table = (new Employee)->getTable();
        //var_dump($table);
//        CurrentAssign::create(array('asset_assign_id'=>$assign->id,'asset_id'=>$assign->asset_id));
    }
    public function mailTest(){
        $employee = Employee::find(2);
        //Mail::to("sui201837@gmail.com")->send(new RecruitApproved($employee->first_name,$employee->last_name,$employee->home_address,$employee->personal_email,$employee->mobile_phone_number));
        Mail::to("sui201837@gmail.com")->send(new RecruitRejected($employee->first_name.' '.$employee->last_name));
    }
}