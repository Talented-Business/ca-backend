<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Department;

class DepartmentController extends Controller
{


  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function index(Request $request)
    {
        $response = Department::all();
        foreach($response as $index=>$department){
            $response[$index]->status = (int)$department->status;
        }
        return response()->json($response);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), Department::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $department = new Department;
        $department->assign($request);
        $department->save();
        return response()->json(array('status'=>'ok','department'=>$department));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), Department::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $department = Department::find($id);
        $department->assign($request);
        $department->save();
        return response()->json(array('status'=>'ok','department'=>$department));
    }
    public function updateStatus($id){
        $department = Department::find($id);
        $department->status = !$department->status;
        $department->save();
        return response()->json($department);
    }
    public function show($id){
        $department = Department::find($id);
        return response()->json($department);
    }    
}