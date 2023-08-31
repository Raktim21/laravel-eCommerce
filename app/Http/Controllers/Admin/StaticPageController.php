<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticContent;
use App\Models\StaticMenu;
use App\Models\StaticMenuType;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Mews\Purifier\Facades\Purifier;

class StaticPageController extends Controller
{

    public function staticContent()
    {
        if(\request()->input('is_paginated'))
        {
            $data = Cache::remember('staticContents', 24 * 60 * 60 * 7, function () {
                return StaticContent::latest()->get();
            });
        }
        else {
            $data = Cache::remember('staticContentList'.request()->get('page', 1), 24 * 60 * 60 * 7, function () {
                return StaticContent::latest()->paginate(10);
            });
        }

        return response()->json([
           'status' => true,
           'data' => $data
        ]);
    }


    public function staticContentStore(Request $request){

        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:500|min:3',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ],422);
        }

        StaticContent::create([
            'title'         => $request->title,
            'description'   => Purifier::clean($request->description)
        ]);

        return response()->json([
            'status' => true,
        ],201);
    }



    public function staticContentDetail($id)
    {
        $data = Cache::remember('staticContent'.$id, 60*60*24*7, function () use ($id) {
            return StaticContent::find($id);
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ],is_null($data) ? 204 : 200);
    }


    public function staticContentUpdate(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:500|min:3',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ],422);
        }

        $data = StaticContent::find($id);

        if (!$data) {
            return response()->json([
                'status' => false,
                'errors' => ['Menu content not found.']
            ],404);
        }

        if ($data->is_updatable == 0) {

            return response()->json([
                'status' => false,
                'errors' => ['You can not update this content.']
            ],404);
        }

        $data->update([
            'title'         => $request->title,
            'description'   => Purifier::clean($request->description)
        ]);

        return response()->json([
            'status' => true,
        ]);
    }


    public function staticContentDelete($id)
    {
        $data = StaticContent::find($id);

        if (!$data) {
            return response()->json([
                'status' => false,
                'errors' => ['Menu content not found.']
            ],404);
        }

        if ($data->is_deletable == 0) {

            return response()->json([
                'status' => false,
                'errors' => ['You can not delete this content.']
            ],400);
        }

        try {
            $data->delete();

            return response()->json([
                'status' => true,
            ]);

        } catch(QueryException $ex)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Static content can not be deleted if menu exists']
            ], 400);
        }

    }


    public function staticMenu(): \Illuminate\Http\JsonResponse
    {
        if(request()->input('is_paginated'))
        {
            $data = Cache::remember('staticMenus', 24 * 60 * 60 * 7, function () {
                return StaticMenu::with('staticContent','staticMenuType')->latest()->get();
            });
        } else {
            $data = Cache::remember('staticMenuList'.request()->get('page', 1), 24*60*60*7, function () {
                return StaticMenu::with('staticContent','staticMenuType')->latest()->paginate(10);
            });
        }

        return response()->json([
           'status' => true,
           'data'   => $data
        ]);
    }


    public function staticMenuStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_name'           => 'required|string|max:200|min:3|unique:static_menus,menu_name',
            'parent_menu_id'      => 'nullable|exists:static_menus,id',
            'static_contents_id'  => 'required|exists:static_contents,id',
            'static_menu_type_id' => 'required|exists:static_menu_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ],422);
        }

        StaticMenu::create([
            'menu_name'             => $request->menu_name,
            'parent_menu_id'        => $request->parent_menu_id,
            'static_contents_id'    => $request->static_contents_id,
            'static_menu_type_id'   => $request->static_menu_type_id
        ]);

        return response()->json([
            'status' => true,
        ], 201);
    }


    public function staticMenuDetail($id)
    {
        $data = Cache::remember('staticMenuDetail'.$id, 24*60*60*7, function () use ($id) {
            return StaticMenu::with('staticContent')->find($id);
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }


    public function staticMenuUpdate(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'menu_name'           => 'required|string|max:200|min:3',
            'parent_menu_id'      => 'nullable|exists:static_menus,id',
            'static_contents_id'  => 'required|exists:static_contents,id',
            'static_menu_type_id' => 'required|exists:static_menu_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ],422);
        }

        $data = StaticMenu::find($id);

        if (!$data) {
            return response()->json([
                'status' => false,
                'errors' => ['Menu not found.']
            ],404);
        }

        if ($data->is_changeable == 0) {

            return response()->json([
                'status' => false,
                'errors' => ['You can not update this Menu.']
            ],400);
        }

        $data->update([
            'menu_name'             => $request->menu_name,
            'parent_menu_id'        => $request->parent_menu_id ?? null,
            'static_contents_id'    => $request->static_contents_id,
            'static_menu_type_id'   => $request->static_menu_type_id
        ]);

        return response()->json([
            'status' => true,
        ]);
    }


    public function staticMenuDelete($id)
    {
        $data = StaticMenu::find($id);

        if (!$data) {
            return response()->json([
                'status' => false,
                'errors' => ['Menu not found.']
            ],404);
        }

        if ($data->is_changeable == 0) {

            return response()->json([
                'status' => false,
                'errors' => ['You can not delete this Menu.']
            ],400);
        }

        try {
            $data->delete();

            return response()->json([
                'status' => true,
            ]);
        } catch (QueryException $ex)
        {
            return response()->json([
                'status' => false,
                'errors' => ['You can not delete this Menu.']
            ], 400);
        }

    }


    public function staticMenuTypes()
    {
        $data = Cache::rememberForever('staticMenuTypes', function() {
            return StaticMenuType::get();
        });

        return response()->json([
           'status' => true,
           'data'    => $data,
        ]);
    }


    public function staticMenuStatusChange(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ],422);
        }

        $data = StaticMenu::find($id);

        if (!$data) {
            return response()->json([
                'status' => false,
                'errors' => ['Menu not found.']
            ],404);
        }

        $data->status = $request->status;
        $data->save();

        return response()->json([
            'status' => true,
        ]);
    }

}
