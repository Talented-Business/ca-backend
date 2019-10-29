<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Company;
use App\User;
use App\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class CompanyController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), Company::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $company = new Company;
        $company->assign($request);
        $company->save();
        return response()->json(array('status'=>'ok','company'=>$company));
    }
    public function update(Request $request)
    {
        $id = $request->input('id');
        $validator = Validator::make($request->all(), Company::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $company = Company::find($id);
        $company->assign($request);
        $company->save();
        return response()->json(array('status'=>'ok','company'=>$company));
    }
    public function worksnap($id)
    {
        $config = new Config;
        $worksnapKey = $config->findByName('worksnap_api_key');
        $apiUrl = "https://api.worksnaps.com/api/";
        $client = new \GuzzleHttp\Client();
        try{
            $res = $client->request('GET', $apiUrl.'projects/'.$id.'.xml', ['auth' =>  [$worksnapKey, '']]);
            $user = $res->getBody()->getContents();  
            $user = new SimpleXMLElement($user);
            return response()->json(array('status'=>'ok','user'=>$user));
        } catch(\GuzzleHttp\Exception\ClientException $e){
            return response()->json(array('status'=>'failed'));
        }
    }
    public function updateStatus($id){
        $company = Company::find($id);
        $company->status = !$company->status;
        $company->save();
        return response()->json($company);
    }
    public function deleteHours($id,Request $request){
        /*$validator = Validator::make($request->all(), Photo::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }*/
        $department_id = $request->input('department_id');
        DB::table('company_departments')
            ->where('company_id',$id)
            ->where('department_id',$department_id)
            ->update(['hours'=>null]);
        $user = $request->user('api');
        $user->extend();    
        return response()->json(array('status'=>'ok','user'=>$user));
    }
    public function uploadHours($id,Request $request){
        /*$validator = Validator::make($request->all(), Photo::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }*/
        $department_id = $request->input('department_id');
        if($request->hasFile('hours')&&$request->file('hours')->isValid()){ 
            $hours = $request->hours->store('hours');
            DB::table('company_departments')
                ->where('company_id',$id)
                ->where('department_id',$department_id)
                ->update(['hours'=>$hours]);
        }else{
            return response()->json(array('status'=>'failed','errors'=>$request->file('hours')->getErrorMessage()));
        }
        $user = $request->user('api');
        $user->extend();    
        return response()->json(array('status'=>'ok','user'=>$user));
    }
    public function show($id){
        $company = Company::find($id);
        
        return response()->json($company);
    }
    public function index(Request $request){
        $company = new Company;
        $company->assignSearch($request);
        return response()->json($company->search());
    }
    public function list(Request $request){
        $companies = Company::where('status',1)->get();
        return response()->json($companies);
    }
}