<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Rules\UniqueEmail;
use Illuminate\Support\Facades\DB;
use App\CommissionGroup;
class UserController extends Controller
{

    public function store(Request $request){
        $validEmail = new UniqueEmail;
        $validator = Validator::make($request->all(), array('email'=>['required','max:255', $validEmail]));
        if ($validator->fails()) {
            return response()->json(array('status'=>'failed','errors'=>$validator->errors()));
        }
        if( $request->new_password != $request->confirm_password ){
            return response()->json(array('status'=>'failed','errors'=>['password'=>'password_unmatched']));
        }
        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->new_password);
        $user->type="admin";
        if($request->input("type") == "super")$user->type="admin";
        $user->save();
        $user->saveMenus($request->input('menus'));
        return response()->json(array('status'=>'ok','user'=>$user));
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function findByToken(Request $request)
    {
        $user = $request->user('api');
        if($user){
            $user->extend();
            return response()->json($user);
        }else{
            return null;
        }
    }

    public function update($id,Request $request){
        $user = User::find($id);
        $validEmail = new UniqueEmail(null,$user->id);
        $validator = Validator::make($request->all(), array('email'=>['required','max:255', $validEmail]));
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
        if($request->exists('name')){
            $user->name = $request->input('name');
        }
        if($request->exists('email')){
            $user->email = $request->input('email');
        }
        if($request->exists('active')){
            $user->active = $request->input('active');
        }
        if($password)$user->password = Hash::make($request->new_password);
        $currentUser = $request->user('api');
        if($currentUser->id != $user->id){
            $user->type="admin";
            if($request->input("type") == "super")$user->type="super";
        }
        $user->save();
        $user->saveMenus($request->input('menus'));
        $user->extend();
        return response()->json(array('status'=>'ok','user'=>$user));
    }
    public function destroy($id){
        $user = User::find($id);
        if($user){
            $destroy=User::destroy($id);
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
    public function findUsers(Request $request){
        $user = new User;
        $user->assignSearch($request);
        $result = $user->search();
        return response()->json($result);
    }
    public function show($id){
        $user = User::find($id);
        $user->menus;
        return $user;
    }
    public function list(){
        $user = User::find(3);
        $member_id = $user->member->id;
        $commissionGroup = CommissionGroup::firstOrCreate(array('member_id'=>$member_id,'invoice_id'=>null));
        return response()->json($commissionGroup);
    }
}