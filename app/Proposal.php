<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use App\CompanyUser;
class Proposal extends Model
{
    protected $table = 'proposals';
    protected $fillable = ['job_id','user_id','employee_id','company_id','status'];
    private $pageSize;
    private $statuses;
    private $pageNumber;
    public $name;
    public static function validateRules(){
        return array(
            'job_id'=>'required',
        );
    }
    private static $searchableColumns = ['status','job_id','name','company_id'];
    public function job()
    {
        return $this->belongsTo('App\Job');
    }    
    public function user()
    {
        return $this->belongsTo('App\User');
    }    
    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }    
    public function company()
    {
        return $this->belongsTo('App\Company');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            $this->{$property} = $request->input($property);
        }
    }
    public function search(){
        $where = Proposal::whereIn('status',$this->statuses);
        if($this->job_id)$where->where('job_id',$this->job_id);
        if($this->company_id)$where->where('company_id',$this->company_id);
        if($this->name){
            $where->where(function($query){
                $query->whereHas('employee', function($q){
                    $q->where('first_name','like',"%$this->name%");
                    $q->orWhere('last_name','like',"%$this->name%");
                    $q->orWhere('id_number','like',"%$this->name%");
                });
                if($this->company_id==null){
                    $query->orWhereHas('company', function($q)
                    {
                        $q->where('name', 'like',"%$this->name%");
                    });
                }
            });
        }
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $proposal){
            $proposal->job;
            $items[$index]['first_name'] = $proposal->employee->first_name;
            $items[$index]['last_name'] = $proposal->employee->last_name;
            $items[$index]['id_number'] = $proposal->employee->id_number;
            $date = explode(' ',$proposal->created_at);
            $items[$index]['applied_date'] = $date[0];
            $items[$index]['age'] = $this->getAge($proposal->employee->birthday);
            if($proposal->company){
                $items[$index]['company_name'] = $proposal->company->name;
            }else{
                $items[$index]['company_name'] = '';
            }
        }
        return $response;
    }
    private function getAge($bithdayDate){
        return date_diff(date_create($bithdayDate), date_create('now'))->y;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            $this->{$property} = $request->input($property);
        }
        if($request->exists('status')){
            if($request->input('status')=='all'){
                $this->statuses = ['pending', 'archived', 'inreview','approved','declined','hired'];
            }else{
                $this->statuses = [$request->input('status')];
            }
        }else{
            $this->statuses = ['pending'];
        }
        $user = $request->user('api');
        if($user&&$user->type=='company'){
            $companyUser = CompanyUser::findUser($user->id);
            if($companyUser){
                $this->company_id = $companyUser->company_id;
                if($request->exists('status')){
                    if($request->input('status')=='all'){
                        $this->statuses = ['inreview','approved','declined'];
                    }
                }
            }
        };
        $this->status = $request->input('status');
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
    public static function findRecent($companyId,$limit=10){
        $items = Proposal::where('company_id',$companyId)
            ->whereIn('status',['inreview'])
            ->skip(0)
            ->take($limit)
            ->orderBy('created_at', 'DESC')
            ->get();
        foreach($items as $index=> $proposal){
            $proposal->company;
            $proposal->employee;
            $items[$index]['age'] = $proposal->getAge($proposal->employee->birthday);
            $items[$index]['position'] = $proposal->job->position;
        }        
        return $items;
    }
}
