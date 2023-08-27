<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $table = 'site_general_settings';

    protected $guarded = ['id'];

    protected $hidden = ['facebook_page_id','created_at','updated_at'];

    protected $fillable = [
        'dashboard_language_id','currency_id','site_name','site_favicon','site_logo','site_logo_dark','site_email','site_phone','site_address',
        'site_facebook','site_linkedin','site_twitter','site_instagram','site_youtube','site_google',
        'site_pinterest','delivery_status','theme_color',
        'text_color','badge_background_color','badge_text_color','button_color','button_text_color',
        'price_color','discount_price_color','about','facebook_page_id'
    ];

    public function language()
    {
        return $this->belongsTo(DashboardLanguage::class, 'dashboard_language_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
