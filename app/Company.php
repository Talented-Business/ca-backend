<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    protected $table = 'companies';
    protected $fillable = ['name','website','state_incoporation','entity_type','industry','size','description','headquaters_addresses',
    'legal_address','billing_address','document_agreement','document_signed_by','document_signature_date','bank_name','bank_account_name',
    'bank_account_number','admin_first_name','admin_last_name','admin_email','admin_phone_number','admin_level'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'name'=>'required|max:255',
            'website'=>'required|max:255',
            'state_incoporation'=>'required|max:255',
            'entity_type'=>'required',
            'industry'=>'required|max:255',
            'size'=>'required|max:255',
            'headquaters_addresses'=>'required|max:255',
            'legal_address'=>'required|max:255',
            'billing_address'=>'required',
            'document_agreement'=>'required|max:255',
            'document_signed_by'=>'max:255',
            'document_signature_date'=>'required|max:255',
            'bank_name'=>'required|max:255',
            'bank_account_name'=>'required|max:255',
            'bank_account_number'=>'required',
            'admin_first_name'=>'max:255',
            'admin_last_name'=>'max:255',
            'admin_email'=>'max:255',
            'admin_phone_number'=>'max:255',
            'admin_level'=>'max:255',
        );
    }
    private static $searchableColumns = ['name'];
    public function departments()
    {
        return $this->belongsToMany('App\Department','company_departments','company_id','department_id');
    }    
    public function users()
    {
        return $this->belongsToMany('App\User','company_users','company_id','user_id');
    }    
    public function activeContracts(){
        return $this->hasMany('App\ActiveContract');
    }
    public function assign($request){
        foreach($this->fillable as $property){
            $this->{$property} = $request->input($property);
        }
        $document_signature_date=$request->input('document_signature_date');
        $document_signature_date = date("Y-m-d",strtotime($document_signature_date));
        $this->document_signature_date = $document_signature_date;
    }
    public function search(){
        $where = Company::where('status','=',$this->status)
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
    public function getHours($departmentId){
        $items = DB::table('company_departments')->select("hours")
            ->where('company_id', '=', $this->id)
            ->where('department_id', '=', $departmentId)
            ->get();
        if(isset($items[0]))return $items[0]->hours;
        else return null;
    }
}
