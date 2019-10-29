<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

class AssetAssign extends Model
{
    protected $table = 'asset_assigns';
    protected $fillable = ['employee_id','start_date','end_date','asset_id','comment'];
    private $pageSize;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'employee_id'=>'required',
            'asset_id'=>'required',
            'start_date'=>'required',
            'end_date'=>'required',
            'comment'=>'required|max:255',
        );
    }
    private static $searchableColumns = ['asset_id','employee_id'];
    public function asset()
    {
        return $this->belongsTo('App\Asset');
    }    
    public function currentAssign(){
        return $this->belongsTo('App\CurrentAssign','asset_assign_id');
    }
    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            $this->{$property} = $request->input($property);
        }
        if($request->exists('start_date')){
            $start_date=$request->input('start_date');
            $start_date = date("Y-m-d",strtotime($start_date));
            $this->start_date = $start_date;
        }
        if($request->exists('end_date')){
            $end_date=$request->input('end_date');
            $end_date = date("Y-m-d",strtotime($end_date));
            $this->end_date = $end_date;
        }
    }
    public function search(){
        if($this->asset_id)$where = AssetAssign::where('asset_id',$this->asset_id);
        if($this->employee_id)$where = AssetAssign::where('employee_id',$this->employee_id);
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $assign){
            $currentAssign = CurrentAssign::where('asset_id',$assign->asset_id)->where('asset_assign_id',$assign->id)->first();
            $items[$index]['currentAssign'] = $currentAssign?true:false;
            $assign->employee;
            $assign->asset;
        }
        return $response;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
}
