<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Arr;

class Config extends Model
{
    protected $table = 'config';
    protected $fillable = ['name','value'];
    protected $names = ['worksnap_api_key','company_fee','member_fee'];
    public $timestamps = false;
    protected $primaryKey = ['name'];
    public $incrementing = false;

    public static function validateRules($id=null){
        return array(
            'name'=>'required|max:255',
            'value'=>'max:255',
        );
    }
    private static $searchableColumns = ['name'];
    public function saveConfig(Request $request){
        foreach($this->names as $name){
            if($request->exists($name)){
                DB::table($this->table)->where('name', '=', $name)->delete();
                DB::table($this->table)->insert(
                    ['name' => $name,'value' => $request->input($name)]
                );
            }
        }        
    }
    public function findByName($name){
        $record = DB::table($this->table)->where('name','=',$name)->first();
        return $record->value;
    }
}
