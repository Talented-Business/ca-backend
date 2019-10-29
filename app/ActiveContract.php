<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

class ActiveContract extends Model
{
    protected $table = 'active_contracts';
    protected $fillable = ['employee_id','company_id','contract_id'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'employee_id'=>'required|unique',
            'company_id'=>'required',
            'contract_id'=>'unique',
        );
    }
    private static $searchableColumns = ['name'];
    public function contract()
    {
        return $this->belongsTo('App\Contract');
    }    
    public function employee()
    {
        //return $this->belongsToMany('App\Employee','company_employees','company_id','user_id');
    }    
    public function getStartDateAttribute(){
        return $this->contract->start_date;
    }
    public function getPositionAttribute(){
        return $this->contract->position;
    }
    public function getDepartmentIdAttribute(){
        return $this->contract->department_id;
    }
    public function getWorkLocationAttribute(){
        return $this->contract->work_location;
    }
    public function getEmploymentTypeAttribute(){
        return $this->contract->employment_type;
    }
    public function getEmploymentStatusAttribute(){
        return $this->contract->employment_status;
    }
    public function getManagerAttribute(){
        return $this->contract->manager;
    }
    public function getWorksnapIdAttribute(){
        return $this->contract->worksnap_id;
    }
    public function getPayDaysAttribute(){
        return $this->contract->pay_days;
    }
    public function getDeductionItemAttribute(){
        return $this->contract->deduction_item;
    }
    public function getCompensationAttribute(){
        return $this->contract->compensation;
    }
    public function getHourlyRateAttribute(){
        return $this->contract->hourly_rate;
    }
    public function getHoursPerDayPeriodAttribute(){
        return $this->contract->hours_per_day_period;
    }
    public function assign($request){
        foreach($this->fillable as $property){
            $this->{$property} = $request->input($property);
        }
    }
    public function search(){
        $where = Contract::where('status','=',$this->status)
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
