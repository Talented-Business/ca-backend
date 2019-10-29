<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Menu;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    private $pageSize;
    private $pageNumber;
    private $types;
    private static $searchableColumns = ['name','email'];
    public function member()
    {
        return $this->hasone('App\Employee','user_id');
    }    
    public function companies()
    {
        return $this->belongsToMany('App\Company','company_users','user_id','company_id');
    }    
    public function menus(){
        return $this->hasMany('App\Menu','admin_id','id');
    }
    public function generatePassword(){
        $password = Str::random(8);
        $this->password = Hash::make($password);
        return $password;
    }
    public function getType(){
        if($this->isHired())return "employee";
        return $this->type;
    }
    public function isHired(){
        if($this->member){
            $activeContract = $this->member->activeContract;
            if($activeContract)return true;
        }
        return false;
    }
    public function getRoles(){
        $roles = DB::table('roles')->where('name','=',$this->type)->get();
        return array((int)$roles[0]->id);
    }
    public function extend(){
        if($this->isHired())$this->type="employee";
        $this['roles'] = $this->getRoles();
        $companies = $this->companies;
        if(isset($companies[0])){
            $this['company'] = $companies[0];
            $this['company']->departments;
            foreach($this['company']->departments as $index=>$department){
                $this['company']->departments[$index]['hours'] = $this['company']->getHours($department->id);
            }
        }
        $this->active = (int)$this->active;
        if($this->type=="admin")$this->menus;
    }
    public function search(){
        $where = User::whereIn('type',$this->types)
        ->where(function($query){
            $index = 0;
            foreach(self::$searchableColumns as $property){
                if($this->{$property}!=null){
                    if($index == 0)$query->Where($property,'like','%'.$this->{$property}.'%');
                    else $query->orWhere($property,'like','%'.$this->{$property}.'%');
                    $index++;
                }
            }
        });
        //return $where->toSql(); 
        //return $where->getBindings();
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $user){
            $user->active = (int)$user->active;
            $user->menus;
        }
        return $response;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
        $this->types=['super','admin'];
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
    
    public function saveMenus($menus){
        $this->menus()->delete();
        if(is_array($menus)){
            foreach($menus as $menu){
                Menu::create(['admin_id'=>$this->id,'status'=>$menu]);
            }
        }
    }
    public static function superAdminEmail(){
        $superAdmins = User::where('type','super')->get();
        if(isset($superAdmins[0])){
            $emails = [];
            foreach($superAdmins as $superAdmin){
                $emails[] = $superAdmin->email;
            }
            return $emails;
        }
        return null;
    }
}
