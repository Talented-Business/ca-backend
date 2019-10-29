<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Event;
use App\User;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Event::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $event = new Event;
        $event->assign($request);
        $event->save();
        return response()->json(array('status'=>'ok','event'=>$event));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), Event::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $event = Event::find($id);
        $event->assign($request);
        $event->save();
        return response()->json(array('status'=>'ok','event'=>$event));
    }
    public function destroy($id){
        $asset = Event::find($id);
        if($asset){
            $destroy=Event::destroy($id);
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
        $event = Event::find($id);
        return response()->json($event);
    }
    public function index(Request $request){
        $event = new Event;
        $event->assignSearch($request);
        return response()->json($event->search());
    }
}