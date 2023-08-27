<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardLanguage extends Model
{
    use HasFactory;

    protected $table = 'dashboard_languages';
    protected $guarded = ['id','name'];
    protected $hidden = ['created_at', 'updated_at'];

    public function language()
    {
        return $this->hasOne(GeneralSetting::class, 'dashboard_language_id');
    }
}
