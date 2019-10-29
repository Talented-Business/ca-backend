<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

class Menu extends Model
{
    protected $table = 'admin_menus';
    protected $fillable = ['status','admin_id'];
    private $pageSize;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'status'=>'required|max:10240',
            'admin_id'=>'required',
        );
    }
    private static $searchableColumns = ['status','admin_id'];
    public function admin()
    {
        return $this->belongsTo('App\User');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            $this->{$property} = $request->input($property);
        }
    }
}
