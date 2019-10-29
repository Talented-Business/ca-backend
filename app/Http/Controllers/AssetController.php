<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Asset;
use App\User;

class AssetController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Asset::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $asset = new Asset;
        $asset->assign($request);
        $asset->save();
        return response()->json(array('status'=>'ok','asset'=>$asset));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), Asset::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $asset = Asset::find($id);
        $asset->assign($request);
        $asset->save();
        return response()->json(array('status'=>'ok','asset'=>$asset));
    }
    public function updateStatus($id){
        $asset = Asset::find($id);
        $asset->status = !$asset->status;
        $asset->save();
        return response()->json($asset);
    }
    public function show($id){
        $asset = Asset::find($id);
        return response()->json($asset);
    }
    public function destroy($id){
        $asset = Asset::find($id);
        if($asset){
            $destroy=Asset::destroy($id);
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
        $asset = new Asset;
        $asset->assignSearch($request);
        $user = $request->user('api');
        return response()->json($asset->search($user->id));
    }
}