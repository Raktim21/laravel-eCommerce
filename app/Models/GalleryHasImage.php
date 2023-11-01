<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GalleryHasImage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class, 'gallery_id');
    }

    public static function boot()
    {
        parent::boot();


        static::created(function ($image) {
            forgetCaches('gallery'.$image->gallery_id);
        });

        static::updated(function ($image) {
            forgetCaches('gallery'.$image->gallery_id);
        });

        static::deleted(function ($image) {
            forgetCaches('gallery'.$image->gallery_id);

            deleteFile($image->image_url);
        });
    }
}
