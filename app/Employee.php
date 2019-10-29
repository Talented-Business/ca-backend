<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\ActiveContract;
use App\Photo;

class Employee extends Model
{
    protected $table = 'employees';
    protected $fillable = ['first_name','last_name','id_number','gender','nationality','home_phone_number','mobile_phone_number','personal_email',
    'marital','skype_id','referal_name','country','state','home_address','deport_america','check_america','check_background','english_level',
    'available_works','have_computer','have_monitor','have_headset','have_ethernet','primary_contact','secondary_contact','visit','approve_date'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $statuses;
    private $pageNumber;
    private $_photos;
    private $_documents;
    public static function validateRules($id=null,$validEmail=null){
        $id_number_rule = '';
        if($id != null){
            $id_number_rule = ",$id"; 
        }
        return [
            'first_name'=>'required|max:255',
            'last_name'=>'required|max:255',
            'id_number'=>'required|max:255|unique:employees,id_number'.$id_number_rule,
            'gender'=>'required',
            'birthday'=>'required|max:255',
            'nationality'=>'required|max:255',
            'home_phone_number'=>'required|max:255',
            'mobile_phone_number'=>'required|max:255',
            'personal_email'=>['required','max:255', $validEmail],//|unique:users,email'.$email_rule,
            'marital'=>'required',
            'skype_id'=>'required|max:255',
            'referal_name'=>'max:255',
            'country'=>'required|max:255',
            'state'=>'required|max:255',
            'home_address'=>'required|max:255',
            'deport_america'=>'required',
            'check_america'=>'required',
            'check_background'=>'required',
            'english_level'=>'required',
            'available_works'=>'required',
            'have_computer'=>'required',
            'have_monitor'=>'required',
            'have_headset'=>'required',
            'have_ethernet'=>'required',
            'primary_contact'=>'max:255',
            'secondary_contact'=>'max:255',
        ];
    }
    public static function validateRulesForUser($validEmail=null){
        return [
            'home_phone_number'=>'required|max:255',
            'mobile_phone_number'=>'required|max:255',
            'email'=>['required','max:255', $validEmail],
        ];
    }
    public static function validateRulesForBank(){
        return [
            'bank_name'=>'required|max:255',
            'bank_account_name'=>'required|max:255',
            'bank_account_number'=>'required|max:255',
            'bank_account_type'=>'required|max:255',
        ];
    }
    private static $searchableColumns = ['first_name','last_name','id_number','personal_email','home_phone_number','mobile_phone_number','created_at'];
    public function skills()
    {
        return $this->belongsToMany('App\Attribute','member_attributes','member_id','attribute_id');
    }    
    public function photos(){
        return $this->hasMany('App\Photo','member_id');
    }
    public function documents(){
        return $this->hasMany('App\Document','member_id');
    }
    public function user()
    {
        return $this->belongsto('App\User');
    }    
    public function activeContract(){
        return $this->hasOne('App\ActiveContract');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
        $birthday=$request->input('birthday');
        $birthday = date("Y-m-d",strtotime($birthday));
        $this->birthday = $birthday;
        $hasErrors = false;
        $errors = [];
        $this->_documents = [];
        if($request->hasFile('passport_path')){
            if($request->file('passport_path')->isValid()){ 
                $passportPath = $request->passport_path->store('documents');
                $passport = ['name'=>'Copy of ID or Passport','slug'=>'passport','path'=>$passportPath];
                $this->_documents[] = $passport;
            }else{
                $hasErrors = true;
                $errors['passport_path'] = $request->file('passport_path')->getErrorMessage();
            }
        }
        if($request->hasFile('reference_path')){
            if($request->file('reference_path')->isValid()){ 
                $referencePath = $request->reference_path->store('documents');
                $reference = ['name'=>'3 reference letters','slug'=>'reference','path'=>$referencePath];
                $this->_documents[] = $reference;
            }else{
                $hasErrors = true;
                $errors['reference_path'] = $request->file('reference_path')->getErrorMessage();
            }
        }
        if($request->hasFile('cv_path')){
            if($request->file('cv_path')->isValid()){ 
                $cvPath = $request->cv_path->store('documents');
                $this->cv_path = $cvPath;
                $this->cv_date = date("Y-m-d");
                $cv = ['name'=>'CV','slug'=>'cv','path'=>$cvPath];
                $this->_documents[] = $cv;
            }else{
                $hasErrors = true;
                $errors['cv_path'] = $request->file('cv_path')->getErrorMessage();
            }
        }
        if($request->hasFile('police_path')){
            if($request->file('police_path')->isValid()){ 
                $policePath = $request->police_path->store('documents');
                $police = ['name'=>'Police Record','slug'=>'police','path'=>$policePath];
                $this->_documents[] = $police;
            }else{
                $hasErrors = true;
                $errors['police_path'] = $request->file('police_path')->getErrorMessage();
            }
        }
        if($request->hasFile('photo'))
        {
            $photos = $request->file('photo');
            $this->_photos = [];
            foreach ($photos as $index=>$photo) {
                if($photo->isValid()){ 
                    $this->_photos[] = $photo->store('photos');        
                }else{
                    $hasErrors = true;
                    $errors['photo'] = $photo->getErrorMessage();
                }
            }
        }        
        if($hasErrors) return $errors;
        return true;
    }
    public function savePhotos(){
        if(!empty($this->_photos)){
            foreach($this->_photos as $item){
                $photo = new Photo;
                $photo->member_id = $this->id;
                $photo->path = $item;
                $photo->save();
            }
        }
    }
    public function saveDocuments(){
        if(!empty($this->_documents)){
            foreach($this->_documents as $item){
                $document = new Document;
                $document->member_id = $this->id;
                $document->name = $item['name'];
                $document->slug = $item['slug'];
                $document->path = $item['path'];
                $document->save();
            }
        }
    }
    public function search(){
        $where = Employee::whereIn('status',$this->statuses)
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
        if($this->fromDate){
            $where->Where('created_at','>=',$this->fromDate);
        }
        if($this->toDate){
            $where->Where('created_at','<=',$this->toDate);
        }
        //return $where->toSql(); 
        //return $where->getBindings();
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $employee){
            $employee->skills;
            $employee->photos;
            $employee->documents;
            $employee->convertString();
            $items[$index]['created_date'] = date("F d Y H:i",strtotime($employee->created_at));
        }
        return $response;
    }
    public function convertString(){
        $this->deport_america = (int) $this->deport_america;
        $this->check_america = (int) $this->check_america;
        $this->check_background = (int) $this->check_background;
        $this->have_computer = (int) $this->have_computer;
        $this->have_monitor = (int) $this->have_monitor;
        $this->have_headset = (int) $this->have_headset;
        $this->have_ethernet = (int) $this->have_ethernet;
        $this->visit = (int) $this->visit;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
        if($request->exists('fromDate')){
            $this->fromDate = date("Y-m-d",strtotime($request->input('fromDate')));
        }
        if($request->exists('toDate')){
            $this->toDate = date("Y-m-d",strtotime($request->input('toDate')));
        }
        $this->statuses = [$request->input('status')];
        if($request->input('type')=='true'){
            if($request->exists('status')){
                if($request->input('status')=='All'){
                    $this->statuses = ['approved','hired','disabled'];
                }
            }
        }
        if($request->input('type')=='false'){
            if($request->exists('status')){
                if($request->input('status')=='All'){
                    $this->statuses = ['Pending','Rejected'];
                }
            }
        }
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
    public function companySearch($request){
        $this->company_id = $request->input('company_id');
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
    public function searchCurrentEmployees(){
        $contractTable = (new ActiveContract)->getTable();
        $items = DB::table($contractTable)
            ->select('employee_id')
            ->where('company_id',$this->company_id)->get();
        $ids = [];    
        if(isset($items[0])){
            foreach($items as $item){
                $ids[] = $item->employee_id;
            }
        }else{
            return [];
        }
        $where = Employee::whereIn('id',$ids);
        //return $where->toSql(); 
        //return $where->getBindings();
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $employee){
            $employee->skills;
            $employee->convertString();
        }
        return $response;
    }
    public function unhired(){
        $where = Employee::where('status','=','approved');
        return $where->get();
    }
    public function confirmed(){
        $where = Employee::whereIn('status',['approved','hired']);
        return $where->get();
    }    
}
