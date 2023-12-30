<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Models\SeoSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SeoSettingController extends Controller
{
    protected $seo;

    public function __construct(){
        $this->seo = SeoSetting::first();
    }

    public function index() {

        return response()->json([
           'status' => true,
           'data'   => $this->seo,
        ]);
    }


    public function update(Request $request) {
        $validate = Validator::make($request->all(), [
            'meta_title'       => 'required|string|max:255',
            'meta_description' => 'sometimes|string|max:65000',
            'meta_keywords'    => 'sometimes|string|max:65000',
            'meta_robots'      => 'sometimes|string|max:65000',
            'meta_author'      => 'sometimes|string|max:255',
            'google_analytics' => 'sometimes|string|max:65000',
        ]);


        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all(),
            ],422);
        }


        $this->seo->updateOrCreate([
            'id' => 1,
        ],[
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords'    => $request->meta_keywords,
            'meta_robots'      => $request->meta_robots,
            'meta_author'      => $request->meta_author,
            'google_analytics' => $request->google_analytics,
        ]);

        return response()->json([
            'status' => true,
        ]);

    }


}
