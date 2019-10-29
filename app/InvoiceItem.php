<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use SimpleXMLElement;
use App\Config;
use App\Commission;
use App\CommissionGroup;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';
    protected $fillable = ['invoice_id','employee_id','task','slug','description','rate','amount','total','pay'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $pageNumber;
    static public $employeeIds;
    public static function validateRules(){
        return array(
            'invoice_id'=>'',
            'employee_id'=>'required',
            'task'=>'required',
            'description'=>'required',
            'amount'=>'required',
        );
    }
    private static $searchableColumns = ['task'];
    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }    
    public function invoice()
    {
        return $this->belongsTo('App\Invoice');
    }    
    public function assign($request){
        foreach($this->fillable as $property){
            if(isset($request[$property])){
                $this->{$property} = $request[$property];
            }
        }
    }
    public function search(){
        $where = Invoice::whereRaw('1')
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
        foreach($items as $index=> $item){
            //$invoice->company;
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
    public static function validateWorksnaps(){
        $config = new Config;
        $worksnapKey = $config->findByName('worksnap_api_key');
        $apiUrl = "https://api.worksnaps.com/api/";
        $client = new \GuzzleHttp\Client();
        try{
            $res = $client->request('GET', $apiUrl.'projects.xml', ['auth' =>  [$worksnapKey, '']]);
            $timeEntries = $res->getBody()->getContents();  
            return true;
        } catch(\GuzzleHttp\Exception\ClientException $e){
            return false;
        }
    }
    public static function getWorkingHours($worksnapIds,$fromDate,$toDate){
        $workingHours = [];
        foreach($worksnapIds as $worksnapId){
            $workingHours[$worksnapId] = 0;
        }
        $fromTime = strtotime($fromDate)+4*3600;
        $toTime = strtotime($toDate)+4*3600;
        $client = new \GuzzleHttp\Client();
        $config = new Config;
        $worksnapKey = $config->findByName('worksnap_api_key');
        $apiUrl = "https://api.worksnaps.com/api/";
        //$res = $client->request('GET', $apiUrl.'projects.xml', ['auth' =>  [$worksnapKey, '']]);
        //$content = $res->getBody()->getContents();
        $projects = $worksnapIds;//new SimpleXMLElement($content);
        foreach($projects as $projectId){
            $userUrl = $apiUrl.'projects/'.$projectId.'/user_assignments.xml';
            $res = $client->request('GET', $userUrl, ['auth' =>  [$worksnapKey, '']]);
            $userEntries = $res->getBody()->getContents();  
            $users = new SimpleXMLElement($userEntries);
            $userIds = [];
            foreach($users as $user){
                $userId = $user->user_id->__toString();
                if(in_array($userId,$worksnapIds) || true)$userIds[] = $user->user_id->__toString();
            }
            //if(count($userIds)==0)continue;
            $ids = implode(";",$userIds);
            $timeUrl = $apiUrl.'projects/'.$projectId.'/time_entries.xml?user_ids='.$ids.'&from_timestamp='.$fromTime.'&to_timestamp='.$toTime;
            try{
                $res = $client->request('GET', $timeUrl, ['auth' =>  [$worksnapKey, '']]);
                $timeEntries = $res->getBody()->getContents();  
                $timeLogs = new SimpleXMLElement($timeEntries);
                foreach($timeLogs as $entry){
                    $userId = $entry->user_id->__toString();
                    $durationInMinutes = $entry->duration_in_minutes->__toString();
                    $workingHours[$projectId] += (int)$durationInMinutes;
                }
            } catch(\GuzzleHttp\Exception\ClientException $e){
                //var_dump($e);
                //die;
            }
        }
        return $workingHours;
    }
    public static function getSales($employeeIds,$fromDate,$toDate){
        self::$employeeIds = $employeeIds;
        //from Commission
        $where = Commission::with('group')->where('created_at','>=',$fromDate)
            ->where('created_at','<=',$toDate)
            ->where(function($query){
                $query->whereHas('group', function($q){
                    $q->whereIn('member_id',self::$employeeIds);
                    $q->whereNull('invoice_id');
                });
        });
        $items = $where->get();
        $sales = [];
        foreach( $items as $item ){
            if(isset($sales[$item->group->member_id])) $sales[$item->group->member_id] += $item->fee * $item->quantity;
            else  $sales[$item->group->member_id] = $item->fee * $item->quantity;
        }
        return $sales;
    }
    public function unAssignCommission(){
        $invoice_id = $this->invoice_id;
        $start_date = $this->invoice->start_date.' 00:00:00';
        $end_date = $this->invoice->end_date.' 23:59:59';
        $employee_id = $this->employee_id;
        $result1 = CommissionGroup::where('member_id',$employee_id)->where('invoice_id',$invoice_id)->get();
        $result2 = CommissionGroup::where('member_id',$employee_id)->where('invoice_id',null)->get();
        if(isset($result2[0])){
            $commissionGroupNull = $result2[0];
            $commissions = $commissionGroupNull->items;
        }
        if(isset($result1[0])){
            $commissionGroupWith = $result1[0];
            $commissions1 = $commissionGroupWith->items;
            if(!empty($commissions1)){
                if(!empty($commissions)){
                    foreach( $commissions1 as $commission){
                        $commission->group_id = $commissionGroupNull->id;
                        $commission->save();
                    }
                    $commissionGroupWith->delete();
                }else{
                    $commissionGroupWith->invoice_id = null;
                    $commissionGroupWith->save();
                }
            }
        }
    }
    public function assignCommission($debug=false){
        //get current invoice item's comission group's commissions + invoice null commission group's commissions
        $invoice_id = $this->invoice_id;
        $start_date = $this->invoice->start_date.' 00:00:00';
        $end_date = $this->invoice->end_date.' 23:59:59';
        $employee_id = $this->employee_id;
        $result1 = CommissionGroup::where('member_id',$employee_id)->where('invoice_id',$invoice_id)->get();
        $result2 = CommissionGroup::where('member_id',$employee_id)->where('invoice_id',null)->get();
        if(isset($result2[0])){
            $commissionGroupNull = $result2[0];
            $commissions = $commissionGroupNull->items;
        }
        if(isset($result1[0])){
            $commissionGroupWith = $result1[0];
            $commissions1 = $commissionGroupWith->items;
            if(isset($commissions1[0])){
                if(!empty($commissions)){
                    $commissions = $commissions->merge($commissions1);
                }else{
                    $commissions = $commissions1;
                }
            }
        }
        if(isset($commissions)){
            //find unmatch commission and match commission
            $unmatchCommissions = [];
            $matchCommissions = [];
            foreach($commissions as $commission){
                if($commission->created_at>=$start_date && $commission->created_at<=$end_date){
                    $matchCommissions[] = $commission;
                }else{
                    $unmatchCommissions[] = $commission;
                }
            }
            if($debug&&false){
                var_dump(count($unmatchCommissions));
                var_dump(count($matchCommissions));
                var_dump($end_date);
            }
            //if there are remain then create or find new commission group with null invoice
            if(count($matchCommissions)>0){
                if(isset($commissionGroupWith)==false){
                    $commissionGroupWith = $commissionGroupNull;
                    $commissionGroupWith->invoice_id = $invoice_id;
                    $commissionGroupWith->save();
                    unset($commissionGroupNull);
                }
                foreach($matchCommissions as $commission){
                    if($commission->group_id != $commissionGroupWith->id){
                        $commission->group_id = $commissionGroupWith->id;
                        $commission->save();
                    }
                }
            }
            if(count($unmatchCommissions)>0){
                if(isset($commissionGroupNull)==false){
                    $commissionGroupNull = CommissionGroup::create(['member_id'=>$employee_id]);
                }
                foreach($unmatchCommissions as $commission){
                    if($commission->group_id != $commissionGroupNull->id){
                        $commission->group_id = $commissionGroupNull->id;
                        $commission->save();
                    }
                }
            }
        }
    }
}
