<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TimeoffRequestToCompany;


class Timeoff extends Model
{
    protected $table = 'timeoffs';
    protected $fillable = ['employee_id','company_id','start_date','end_date','reason','policy','status'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $statuses;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'start_date'=>'required',
            'end_date'=>'required',
            'reason'=>'required|max:255',
            'policy'=>'required',
        );
    }
    private static $searchableColumns = ['company_id','employee_id','reason','policy'];
    public function company()
    {
        return $this->belongsTo('App\Company');
    }    
    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
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
        $where = Timeoff::whereIn('status',$this->statuses)
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
        foreach($items as $index=> $timeoff){
            $timeoff->employee;
            $timeoff->company;
            $from = Carbon::parse($timeoff->start_date);
            $to = Carbon::parse($timeoff->end_date);
            $days = $to->diff($from)->days;            
            $items[$index]['short_reason'] = Str::limit($timeoff->reason, $limit = 50, $end = '...');
            $items[$index]['days'] = $days+1;
        }        
        return $response;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            $this->{$property} = $request->input($property);
        }
        if($request->exists('status')){
            if($request->input('status')=='all'){
                $this->statuses = ['Pending', 'Approved','Rejected'];
            }else{
                $this->statuses = [$request->input('status')];
            }
        }else{
            $this->statuses = ['Pending'];
        }
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
    public static function findRecent($companyId,$limit=10){
        $items = Timeoff::where('company_id',$companyId)->skip(0)
            ->orderBy('created_at', 'DESC')
            ->take($limit)
            ->get();
        foreach($items as $index=> $timeoff){
            $timeoff->employee;
            $timeoff->company;
            $from = Carbon::parse($timeoff->start_date);
            $to = Carbon::parse($timeoff->end_date);
            $days = $to->diff($from)->days;            
            $items[$index]['short_reason'] = Str::limit($timeoff->reason, $limit = 50, $end = '...');
            $items[$index]['days'] = $days+1;
        }        
        return $items;
    }
    public static function findRecentByMember($employeeId,$limit=10){
        $items = Timeoff::where('employee_id',$employeeId)->skip(0)
            ->orderBy('created_at', 'DESC')
            ->take($limit)
            ->get();
        foreach($items as $index=> $timeoff){
            $from = Carbon::parse($timeoff->start_date);
            $to = Carbon::parse($timeoff->end_date);
            $days = $to->diff($from)->days;            
            $items[$index]['short_reason'] = Str::limit($timeoff->reason, $limit = 50, $end = '...');
            $items[$index]['days'] = $days+1;
        }        
        return $items;
    }
    public function sendMail(){
        $from = Carbon::parse($this->start_date);
        $to = Carbon::parse($this->end_date);
        $days = $to->diff($from)->days+1;      
        foreach($this->company->users as $user){      
            Mail::to($user->email)->send(new TimeoffRequestToCompany($this->start_date,$this->end_date, $days,$this->employee->first_name." ".$this->employee->last_name, $this->policy, $this->reason));
        }
    }
}
