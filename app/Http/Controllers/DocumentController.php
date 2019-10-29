<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Document;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Document::validateRules());
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        $document = new Document;
        $document->member_id = $request->input('member_id');
        if($request->hasFile('path')&&$request->file('path')->isValid()){ 
            $documentPath = $request->path->store('documents');
            $document->path = $documentPath;
            $document->name = $request->input('name');
        }else{
            return response()->json(array('status'=>'failed','errors'=>$request->file('path')->getErrorMessage()));
        }
        $document->save();
        return response()->json(array('status'=>'ok','documents'=>$document->member->documents));
    }
    public function update($id,Request $request)
    {
        $document = Document::find($id);
        if($request->hasFile('path')&&$request->file('path')->isValid()){ 
            $documentPath = $request->path->store('documents');
            $document->path = $documentPath;
            $document->name = $request->input('name');
        }else{
            return response()->json(array('status'=>'failed','errors'=>$request->file('path')->getErrorMessage()));
        }
        $document->save();
        return response()->json(array('status'=>'ok','documents'=>$document->member->documents));
    }
    public function destroy($id){
        $document = Document::find($id);
        $employee = $document->member;
        $destroy = null;
        if($document){
            $destroy=Document::destroy($id);
        }
        if ($destroy){
            $data=[
                'status'=>'1',
                'msg'=>'success',
                'documents'=>$employee->documents,
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
        $document = Document::find($id);
        return response()->json($document);
    }
    public function index(Request $request){
        $document = new Document;
        return response()->json($document);
    }
}