<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\AssetAssign;
use App\Asset;
use App\CurrentAssign;
use Illuminate\Support\Facades\DB;

class AssetAssignController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), AssetAssign::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $assign = new AssetAssign;
        $assign->assign($request);
        $assign->save();
        CurrentAssign::create(array('asset_assign_id'=>$assign->id,'asset_id'=>$assign->asset_id));
        $assign->asset->status="Assigned";
        $assign->asset->save();
        return response()->json(array('status'=>'ok','assetAssign'=>$assign));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), AssetAssign::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $asset = AssetAssign::find($id);
        $asset->assign($request);
        $asset->save();
        return response()->json(array('status'=>'ok','assetAssign'=>$asset));
    }
    public function updateStatus($id){
        $asset = AssetAssign::find($id);
        $asset->status = !$asset->status;
        $asset->save();
        return response()->json($asset);
    }
    public function show($id){
        $asset = AssetAssign::find($id);
        return response()->json($asset);
    }
    public function destroy($id){
        $asset = AssetAssign::find($id);
        if($asset){
            $destroy=AssetAssign::destroy($id);
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
        $assetAssign = new AssetAssign;
        $assetAssign->assignSearch($request);
        return response()->json($assetAssign->search());
    }
    public function unassign($id){
        CurrentAssign::where('asset_id',$id)->delete();
        $asset = Asset::find($id);
        $asset->status="Pending";
        $asset->save();
    }
    public function logined(Request $request){
        $user = $request->user("api");
        $employee_id = $user->member->id;
        $assignTable = (new AssetAssign)->getTable();
        $currentTable =(new CurrentAssign)->getTable();
        $assetTable =(new Asset)->getTable();
        $assets = DB::table($assetTable)
            ->join($assignTable,$assetTable.'.id', '=', $assignTable.'.asset_id')
            ->join($currentTable,$assignTable.'.id', '=', $currentTable.'.asset_assign_id')
            ->where($assignTable.'.employee_id',$employee_id)->get();
        return response()->json($assets);
    }
}