<?php

namespace App\Http\Services;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mews\Purifier\Facades\Purifier;

class GeneralSettingService
{
    protected $setting;

    public function __construct(GeneralSetting $setting)
    {
        $this->setting = $setting->with('language','currency')->find(1);
    }

    public function getSetting()
    {
        return $this->setting;
    }

    public function updateSetting(Request $request)
    {
//        $this->setting->dashboard_language_id = $request->dashboard_language_id ?? $this->setting->dashboard_language_id;
        $this->setting->currency_id = $request->currency_id ?? $this->setting->currency_id;
        $this->setting->name = $request->name ?? $this->setting->name;
        $this->setting->email = $request->email ?? $this->setting->email;
        $this->setting->phone = $request->phone ?? $this->setting->phone;
        $this->setting->address = $request->address ?? $this->setting->address;
        $this->setting->facebook = $request->facebook ?? $this->setting->facebook;
        $this->setting->twitter = $request->twitter ?? $this->setting->twitter;
        $this->setting->linkedin = $request->linkedin ?? $this->setting->linkedin;
        $this->setting->instagram = $request->instagram ?? $this->setting->instagram;
        $this->setting->youtube = $request->youtube ?? $this->setting->youtube;
        $this->setting->google = $request->google ?? $this->setting->google;
        $this->setting->pinterest = $request->pinterest ?? $this->setting->pinterest;
        $this->setting->about = $request->about ?? $this->setting->about;
        $this->setting->theme_color = $request->theme_color ?? $this->setting->theme_color;
        $this->setting->text_color = $request->text_color ?? $this->setting->text_color;
        $this->setting->badge_background_color = $request->badge_background_color ?? $this->setting->badge_background_color;
        $this->setting->badge_text_color = $request->badge_text_color ?? $this->setting->badge_text_color;
        $this->setting->button_color = $request->button_color ?? $this->setting->button_color;
        $this->setting->button_text_color = $request->button_text_color ?? $this->setting->button_text_color;
        $this->setting->price_color = $request->price_color ?? $this->setting->price_color;
        $this->setting->discount_price_color = $request->discount_price_color ?? $this->setting->discount_price_color;
        $this->setting->save();

        if ($request->hasFile('logo'))
        {
            deleteFile($this->setting->logo);
            saveImage($request->file('logo'), '/uploads/images/general-setting/', $this->setting, 'logo');
        }

        if ($request->hasFile('dark_logo'))
        {
            deleteFile($this->setting->dark_logo);
            saveImage($request->file('dark_logo'), '/uploads/images/general-setting/', $this->setting, 'dark_logo');
        }

        if ($request->hasFile('favicon'))
        {
            deleteFile($this->setting->site_favicon);
            saveImage($request->file('favicon'), '/uploads/images/general-setting/', $this->setting, 'favicon');
        }
    }



    public function getDeliveryStatus()
    {
        return $this->setting->delivery_status;
    }

    public function updateDeliveryStatus(Request $request): void
    {
        $this->setting->delivery_status = $request->delivery_status;
        $this->setting->save();
    }
}
