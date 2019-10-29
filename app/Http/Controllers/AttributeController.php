<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Attribute;

class AttributeController extends Controller
{


  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function index(Request $request)
    {
        $response = Attribute::all();
        foreach($response as $index=>$attribute){
            $response[$index]->status = (int)$attribute->status;
        }
        return response()->json($response);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), Attribute::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $attribute = new Attribute;
        $attribute->assign($request);
        $attribute->save();
        return response()->json(array('status'=>'ok','attribute'=>$attribute));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), Attribute::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $attribute = Attribute::find($id);
        $attribute->assign($request);
        $attribute->save();
        return response()->json(array('status'=>'ok','attribute'=>$attribute));
    }
    public function updateStatus($id){
        $attribute = Attribute::find($id);
        $attribute->status = !$attribute->status;
        $attribute->save();
        return response()->json($attribute);
    }
    public function show($id){
        $attribute = Attribute::find($id);
        return response()->json($attribute);
    }    
    
}