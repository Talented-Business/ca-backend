<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

class Job extends Model
{
    protected $table = 'jobs';
    protected $fillable = ['title','position','description'];
    private $pageSize;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'title'=>'required|max:255',
            'position'=>'required|max:255',
            'description'=>'required',
        );
    }
    private static $searchableColumns = ['title','position','status'];
    public function skills()
    {
        return $this->belongsToMany('App\Attribute','job_attributes','job_id','attribute_id');
    }    
    public function applicants()
    {
        return $this->belongsToMany('App\Employee','proposals','job_id','employee_id');
    }    
    public function proposals()
    {
        return $this->hasMany('App\Proposal');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            $this->{$property} = $request->input($property);
        }
    }
    public function search($user_id){
        $where = Job::where(function($query){
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
        foreach($items as $index=> $job){
            $job->skills;
            $date = explode(' ',$job->created_at);
            $items[$index]['created_date'] = $date[0];
            $items[$index]['applicants_count'] = count($job->applicants);
            $proposals = $job->proposals()->where('user_id',$user_id)->get();
            $items[$index]['has_proposal'] = count($proposals)>0?1:0;
            $job->status = (int)$job->status;
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
