<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

class CommissionGroup extends Model
{
    protected $table = 'commission_groups';
    protected $fillable = ['invoice_id','member_id'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'member_id'=>'',
        );
    }
    private static $searchableColumns = ['member_id','invoice_id'];
    public function member()
    {
        return $this->belongsTo('App\Employee','member_id');
    }    
    public function items()
    {
        return $this->hasMany('App\Commission','group_id');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
    }
    public function search(){
        $where = CommissionGroup::whereRaw('1');
        foreach(self::$searchableColumns as $property){
            if($this->{$property}!=null){
                $where->where($property,$this->{$property});
            }
        }
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $item){
            if($item->invoice_id){
                $items[$index]['status'] = "Paid";
            }else{
                $items[$index]['status'] = "Pending";
            }
            foreach( $item->items as $commission){
                $date = explode(' ',$commission->created_at);
                $commission['created_date'] = $date[0];
            }
        }
        return $response;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
        $this->status = $request->input('status');
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
}
