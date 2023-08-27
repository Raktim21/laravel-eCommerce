<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    use HasFactory;
    
    protected $guarded = ['id','created_at','updated_at'];

    protected $hidden = ['created_at', 'updated_at'];

    public function scopeSearch($query){

        $name       = request()->name;
        $start_date = request()->start_date;
        $end_date   = request()->end_date;

        return  $query->when($name,function($query,$name){
                    return $query->where('name','like','%'.$name.'%');
                })->when($start_date,function($query,$start_date){
                    return $query->whereDate('start_date','>=',$start_date);
                })->when($end_date,function($query,$end_date){
                    return $query->whereDate('end_date','<=',$end_date);
                });
    }
}
