<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use App\CommissionGroup;

class Commission extends Model
{
    protected $table = 'commissions';
    protected $fillable = ['group_id','name','fee','quantity'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'group_id'=>'',
            'name'=>'required|max:250',
            'fee'=>'required',
            'quantity'=>'required',
        );
    }
    private static $searchableColumns = ['member_id','group_id'];
    public function group()
    {
        return $this->belongsTo('App\CommissionGroup','group_id');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
        if($this->group_id == null){
            $user = $request->user("api");
            if($user->getType()=="employee"){
                $member_id = $user->member->id;
                $commissionGroup = CommissionGroup::firstOrCreate(array('member_id'=>$member_id,'invoice_id'=>null));
                $this->group_id = $commissionGroup->id;
            }
        }
    }
    public function search(){
        $where = Commission::where('status','=',$this->status)
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
        foreach($items as $index=> $company){
            $items[$index]->status = (int)$company->status;
            //$company->departments;
        }
        return $response;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            $this->{$property} = $request->input($property);
        }
        $this->status = $request->input('status');
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
}
