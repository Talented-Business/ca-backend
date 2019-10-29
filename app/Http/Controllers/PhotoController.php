<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Photo;

class PhotoController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Photo::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $photo = new Photo;
        $photo->member_id = $request->input('member_id');
        if($request->hasFile('path')&&$request->file('path')->isValid()){ 
            $photoPath = $request->path->store('photos');
            $photo->path = $photoPath;
        }else{
            return response()->json(array('status'=>'failed','errors'=>"photo is invalid"));
        }
        $photo->save();
        return response()->json(array('status'=>'ok','photo'=>$photo));
    }
    public function update($id,Request $request)
    {
        $photo = Photo::find($id);
        $photo->save();
        return response()->json(array('status'=>'ok','photo'=>$photo));
    }
    public function destroy($id){
        $photo = Photo::find($id);
        $destroy = null;
        if($photo){
            $destroy=Photo::destroy($id);
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
    public function show($id){
        $photo = Photo::find($id);
        return response()->json($photo);
    }
    public function index(Request $request){
        $photo = new Photo;
        return response()->json($photo);
    }
}