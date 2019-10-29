<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Employee;
use App\User;
use Illuminate\Support\Facades\DB;

class UniqueEmail implements Rule
{
    private $employee_id;
    private $user_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($employee_id=null,$user_id=null)
    {
        if($employee_id){
            $this->employee_id = $employee_id;
            $employee = Employee::find($employee_id);
            $this->user_id = $employee->user->id;
        }else if($user_id){
            $this->user_id = $user_id;
            $user = User::find($user_id);
            if($user->member){
                $this->employee_id = $user->member->id;
            }
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $employeeSql = "SELECT 'employee' as type,personal_email as email,id as employee_id, user_id FROM `".(new Employee)->getTable()."` WHERE 1";        
        $userSql = "SELECT 'user' as type, email,null, id as user_id FROM `".(new User)->getTable()."` WHERE 1";
        $sql = "select * from ($employeeSql union $userSql) as user_employee where email = ?";
        if($this->employee_id){
            $sql = $sql." and (employee_id != ".$this->employee_id." or employee_id is null)";
        }
        if($this->user_id){
            $sql = $sql." and (user_id != ".$this->user_id." or user_id is null)";
        }
        $emails = DB::select( $sql, [$value]);
        if(count($emails)>0) return false;
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The email address is not unique.';
    }
}
