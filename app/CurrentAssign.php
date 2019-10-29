<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use App\User;
use Illuminate\Support\Arr;

class CurrentAssign extends Model
{
    protected $table = 'asset_assigner';
    protected $fillable = ['asset_assign_id','asset_id'];
    private $pageSize;
    private $fromDate;
    private $toDate;
    private $pageNumber;
    public $timestamps = false;
    protected $primaryKey = ['asset_assign_id', 'asset_id'];
    public $incrementing = false;

    public static function validateRules($id=null){
        return array(
            'asset_assign_id'=>'required',
            'asset_id'=>'required',
        );
    }
    private static $searchableColumns = ['asset_assign_id', 'asset_id'];
    public function asset()
    {
        return $this->belongsTo('App\Asset');
    }    
    public function assetAssign()
    {
        return $this->belongsTo('App\AssetAssign');
    }    
    static public function  findAssign($assetId){
        $currentAssign = CurrentAssign::where('asset_id',$assetId)->first();
        return $currentAssign;
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
