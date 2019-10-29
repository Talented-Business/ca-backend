<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class Contract extends Model
{
    protected $table = 'contracts';
    protected $fillable = ['employee_id','company_id','title','start_date','position','department_id','work_location','employment_type',
    'employment_status','manager','worksnap_id','pay_days','deduction_item','compensation','hourly_rate',
    'hours_per_day_period'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'employee_id'=>'required',
            'company_id'=>'required',
            'start_date'=>'required',
            'position'=>'required|max:255',
            'department_id'=>'required|max:255',
            'work_location'=>'required|max:255',
            'employment_type'=>'required|max:255',
            'employment_status'=>'required',
            'manager'=>'required|max:255',
            'worksnap_id'=>'max:255',
            'pay_days'=>'required|max:255',
            'deduction_item'=>'required|max:255',
            'compensation'=>'required|max:255',
            'hourly_rate'=>'required',
            'hours_per_day_period'=>'required',
        );
    }
    private static $searchableColumns = ['company_id','employee_id'];
    public function department()
    {
        return $this->belongsTo('App\Department');
    }    
    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }    
    public function company()
    {
        return $this->belongsTo('App\Company');
    }    
    public function activeContract()
    {
        return $this->hasOne('App\ActiveContract');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            $this->{$property} = $request->input($property);
        }
        $start_date=$request->input('start_date');
        $start_date = date("Y-m-d",strtotime($start_date));
        $this->start_date = $start_date;
    }
    public function search(){
        /*$where = Contract::where('status','=',$this->status)
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
        */
        if($this->status){
            $where = ActiveContract::with('contract')->where('company_id','=',$this->company_id);
        }
        else {
            if($this->company_id)$where = Contract::doesntHave('activeContract')->where('company_id','=',$this->company_id);
            if($this->employee_id)$where = Contract::where('employee_id','=',$this->employee_id);
        }
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $contract){
            if($this->status){
                $items[$index]['title'] = $contract->contract->title;
                $items[$index]['start_date'] = $contract->contract->start_date;
                $items[$index]['end_date'] = $contract->contract->end_date;
                $items[$index]['position'] = $contract->contract->position;
                $items[$index]['department_id'] = $contract->department_id;
                $items[$index]['work_location'] = $contract->contract->work_location;
                $items[$index]['employment_type'] = $contract->contract->employment_type;
                $items[$index]['employment_status'] = $contract->contract->employment_status;
                $items[$index]['manager'] = $contract->contract->manager;
                $items[$index]['worksnap_id'] = $contract->contract->worksnap_id;
                $items[$index]['pay_days'] = $contract->contract->pay_days;
                $items[$index]['deduction_item'] = $contract->contract->deduction_item;
                $items[$index]['compensation'] = $contract->contract->compensation;
                $items[$index]['hourly_rate'] = $contract->contract->hourly_rate;
                $items[$index]['hours_per_day_period'] = $contract->contract->hours_per_day_period;
                $items[$index]['employee'] = $contract->contract->employee;
                $items[$index]['company'] = $contract->contract->company;
                $items[$index]['department'] = $contract->contract->department;
                $items[$index]['status'] = true;
            }else{
                $items[$index]['employee'] = $contract->employee;
                $items[$index]['company'] = $contract->company;
                $items[$index]['department'] = $contract->department;
                if($contract->activeContract) $items[$index]['status'] = true;
                else  $items[$index]['status'] = false;
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
        if($request->exists('status')){
            $this->status = $request->input('status');
        }
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
}
