<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryHasImage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class, 'gallery_id');
    }
}
