<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Job;
use App\User;

class JobController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), Job::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $job = new Job;
        $job->assign($request);
        $job->save();
        $job->skills()->attach($request->input('skills'));
        return response()->json(array('status'=>'ok','job'=>$job));
    }
    public function update($id,Request $request)
    {
        $validator = Validator::make($request->all(), Job::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $job = Job::find($id);
        $job->assign($request);
        $job->save();
        $job->skills()->detach();
        $job->skills()->attach($request->input('skills'));
        return response()->json(array('status'=>'ok','job'=>$job));
    }
    public function updateStatus($id){
        $job = Job::find($id);
        $job->status = !$job->status;
        $job->save();
        return response()->json($job);
    }
    public function show($id){
        $job = Job::find($id);
        $job->skills;
        $job->status = (int)$job->status;
        return response()->json($job);
    }
    public function index(Request $request){
        $job = new Job;
        $job->assignSearch($request);
        $user = $request->user('api');
        return response()->json($job->search($user->id));
    }
}