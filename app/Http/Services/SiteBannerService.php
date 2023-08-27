<?php

namespace App\Http\Services;

use App\Models\SiteBanners;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteBannerService
{

    protected $banner;

    public function __construct(SiteBanners $banner)
    {
        $this->banner = $banner;
    }

    public function get()
    {
        return $this->banner->clone()->first();
    }

    public function extraValidationChecker(Request $request): bool
    {
        if (!$request->flash_sale_image && !$request->new_arrival_image && !$request->discount_product_image &&
            !$request->popular_product_image && !$request->newsletter_image && !$request->featured_product_image &&
            !$request->all_product_side_image && !$request->featured_banner_image)
        {
            return true;
        }
        else{
            return false;
        }
    }

    public function store(Request $request): void
    {
        $site_banner = $this->banner->clone()->latest()->first();


        if ($site_banner == null) {

            $site_banner = new SiteBanners();
        }

        if ($request->hasFile('flash_sale_image')) {
            deleteFile($site_banner->flash_sale_image);
            saveImage($request->file('flash_sale_image'), '/uploads/images/site_banner/', $site_banner, 'flash_sale_image');
        }


        if ($request->hasFile('new_arrival_image')) {
            deleteFile($site_banner->new_arrival_image1);
            saveImage($request->file('new_arrival_image'), '/uploads/images/site_banner/', $site_banner, 'new_arrival_image1');

        }


        if ($request->hasFile('discount_product_image')) {
            deleteFile($site_banner->discount_product_image);
            saveImage($request->file('discount_product_image'), '/uploads/images/site_banner/', $site_banner, 'discount_product_image');
        }


        if ($request->hasFile('popular_product_image')) {
            deleteFile($site_banner->popular_product_image1);
            saveImage($request->file('popular_product_image'), '/uploads/images/site_banner/', $site_banner, 'popular_product_image1');
        }


        if ($request->hasFile('newsletter_image')) {
            deleteFile($site_banner->newsletter_image);
            saveImage($request->file('newsletter_image'), '/uploads/images/site_banner/', $site_banner, 'newsletter_image');
        }


        if ($request->hasFile('featured_banner_image')) {
            deleteFile($site_banner->all_product_banner_image);
            saveImage($request->file('featured_banner_image'), '/uploads/images/site_banner/', $site_banner, 'featured_banner_image');
        }


        if ($request->hasFile('all_product_side_image')) {
            deleteFile($site_banner->all_product_side_image);
            saveImage($request->file('all_product_side_image'), '/uploads/images/site_banner/', $site_banner, 'all_product_side_image');
        }


        if ($request->hasFile('featured_product_image')) {
            deleteFile($site_banner->featured_banner_image);
            saveImage($request->file('featured_product_image'), '/uploads/images/site_banner/', $site_banner, 'featured_product_image');
        }

        $site_banner->save();

        Cache::delete('siteBanners');
    }

}
