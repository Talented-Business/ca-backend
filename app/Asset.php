<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\CurrentAssign;
use App\AssetAssign;

class Asset extends Model
{
    protected $table = 'assets';
    protected $fillable = ['name','imei','status'];
    private $pageSize;
    private $statuses;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'name'=>'required|max:255',
            'imei'=>'required|max:255',
        );
    }
    private static $searchableColumns = ['name','imei'];
    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }    
    public function currentAssign()
    {
        $item =  CurrentAssign::where('asset_id',$this->id)->first();
        if($item){
            return AssetAssign::find($item->asset_assign_id);
        }
        return null;
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            if($request->exists($property))$this->{$property} = $request->input($property);
        }
    }
    public function search(){
        $where = Asset::whereIn('status',$this->statuses)
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
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $asset){
            $currentAssign = $asset->currentAssign();
            if($currentAssign){
                $items[$index]['employee']=$currentAssign->employee->first_name." ".$currentAssign->employee->last_name;
                $items[$index]['start_date']=$currentAssign->start_date;
                $items[$index]['end_date']=$currentAssign->end_date;
            }
        }        
        return $response;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            $this->{$property} = $request->input($property);
        }
        if($request->exists('status')){
            if($request->input('status')=='all'){
                $this->statuses = ['Pending', 'Assigned', 'Sold'];
            }else{
                $this->statuses = [$request->input('status')];
            }
        }else{
            $this->statuses = ['Pending'];
        }
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
}
