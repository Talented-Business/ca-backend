<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

class Event extends Model
{
    protected $table = 'events';
    protected $fillable = ['title','description','status'];
    private $pageSize;
    private $pageNumber;
    public static function validateRules(){
        return array(
            'title'=>'required|max:255',
            'description'=>'required',
        );
    }
    private static $searchableColumns = ['title'];
    public function assign($request){
        foreach($this->fillable as $property){
            $this->{$property} = $request->input($property);
        }
    }
    public function search(){
        $where = Event::where(function($query){
            $index = 0;
            foreach(self::$searchableColumns as $property){
                if($this->{$property}!=null){
                    if($index == 0)$query->Where($property,'like','%'.$this->{$property}.'%');
                    else $query->orWhere($property,'like','%'.$this->{$property}.'%');
                    $index++;
                }
            }
        });
        if($this->status)$where->where('status',$this->status);
        $currentPage = $this->pageNumber+1;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });      
        $response = $where->orderBy('created_at', 'DESC')->paginate($this->pageSize);
        $items = $response->items();
        foreach($items as $index=> $event){
            $date = explode(' ',$event->created_at);
            $items[$index]['created_date'] = $date[0];
            $items[$index]['excerpt'] = $this->extractExcerpt($event->description);
        }
        return $response;
    }
    private function extractExcerpt($html){
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $text_to_strip = $dom->textContent;
        $length = mb_strlen($text_to_strip);
        $max = 150;
        if($length>$max){
            $stripped = mb_substr($text_to_strip,0,$max).'...';
        }else{
            $stripped = $text_to_strip;
        }
        return $stripped;
    }
    public function assignSearch($request){
        foreach(self::$searchableColumns as $property){
            if($request->exists($property)){
                $this->{$property} = $request->input($property);
            }
        }
        if($request->exists('status')){
            $this->status = $request->input('status');
        }
        $this->pageSize = $request->input('pageSize');
        $this->pageNumber = $request->input('pageNumber');
    }
}
