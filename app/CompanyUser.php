<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Company;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Mail\WeclomeToCompanyUser;

class CompanyUser extends Model
{
    protected $table = 'company_users';
    protected $fillable = ['company_id','user_id'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $pageNumber;
    public $timestamps = false;
    protected $primaryKey = ['company_id', 'user_id'];
    public $incrementing = false;

    public static function validateRules($id=null){
        $email_rule = '';
        if($id != null){
            $email_rule = ",$id"; 
        }
        return array(
            'company_id'=>'required',
            'name'=>'required|max:255',
            'email'=>'required|max:255|email|unique:users,email'.$email_rule,
        );
    }
    private static $searchableColumns = ['company_id'];
    public function user()
    {
        return $this->belongsTo('App\User');
    }    
    static public function createUser($request){
        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->type = 'company';
        $password = $user->generatePassword();
        $user->save();
        if($user->id>0){
            $companyUser = new CompanyUser;
            $companyUser->user_id = $user->id;
            $companyUser->company_id = $request->input('company_id');
            $companyUser->save();
        }    
        $company = Company::find($request->input('company_id'));
        Mail::to($user->email)->send(new WeclomeToCompanyUser($company->name,$user->name,$password));
        return $user;
    }
    static public function  findUser($userId){
        $companyUser = CompanyUser::where('user_id',$userId)->get();
        if(isset($companyUser[0]))return $companyUser[0];
        return null;
    }
    public function search(){
        $where = User::join('company_users','company_users.user_id', '=', 'id')
            ->where('company_users.company_id','=',$this->company_id);
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $user){
            $companyUser = CompanyUser::where('company_id','=',$this->company_id)->where('user_id','=',$user->id)->get();
            if(isset($companyUser[0])) $items[$index]['status'] = (int)$companyUser[0]->status;else $items[$index]['status'] = 0;
            $items[$index]['company_id'] = $this->company_id;
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
    public function save(array $options = [])
    {
        if( ! is_array($this->getKeyName()))
        {
            return parent::save($options);
        }

        // Fire Event for others to hook
        if($this->fireModelEvent('saving') === false) return false;

        // Prepare query for inserting or updating
        $query = $this->newQueryWithoutScopes();

        // Perform Update
        if ($this->exists)
        {
            if (count($this->getDirty()) > 0)
            {
                // Fire Event for others to hook
                if ($this->fireModelEvent('updating') === false)
                {
                    return false;
                }

                // Touch the timestamps
                if ($this->timestamps)
                {
                    $this->updateTimestamps();
                }

                //
                // START FIX
                //


                // Convert primary key into an array if it's a single value
                $primary = (count($this->getKeyName()) > 1) ? $this->getKeyName() : [$this->getKeyName()];

                // Fetch the primary key(s) values before any changes
                $unique = array_intersect_key($this->original, array_flip($primary));

                // Fetch the primary key(s) values after any changes
                $unique = !empty($unique) ? $unique : array_intersect_key($this->getAttributes(), array_flip($primary));

                // Fetch the element of the array if the array contains only a single element
                //$unique = (count($unique) <> 1) ? $unique : reset($unique);

                // Apply SQL logic
                $query->where($unique);

                //
                // END FIX
                //

                // Update the records
                $query->update($this->getDirty());

                // Fire an event for hooking into
                $this->fireModelEvent('updated', false);
            }
        }
        // Insert
        else
        {
            // Fire an event for hooking into
            if ($this->fireModelEvent('creating') === false) return false;

            // Touch the timestamps
            if($this->timestamps)
            {
                $this->updateTimestamps();
            }

            // Retrieve the attributes
            $attributes = $this->attributes;

            if ($this->incrementing && !is_array($this->getKeyName()))
            {
                $this->insertAndSetId($query, $attributes);
            }
            else
            {
                $query->insert($attributes);
            }

            // Set exists to true in case someone tries to update it during an event
            $this->exists = true;

            // Fire an event for hooking into
            $this->fireModelEvent('created', false);
        }

        // Fires an event
        $this->fireModelEvent('saved', false);

        // Sync
        $this->original = $this->attributes;

        // Touches all relations
        if (Arr::get($options, 'touch', true)) $this->touchOwners();

        return true;
    }    
}
