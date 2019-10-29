<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

class Document extends Model
{
    protected $table = 'documents';
    protected $fillable = ['path','member_id'];
    private $pageSize;
    private $pageNumber;
    public static function validateRules(){
        return array(
            //'path'=>'required',
            'member_id'=>'required',
        );
    }
    private static $searchableColumns = ['title','member_id'];
    public function member()
    {
        return $this->belongsTo('App\Employee');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            $this->{$property} = $request->input($property);
        }
    }
}
