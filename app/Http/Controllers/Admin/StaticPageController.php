<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticContent;
use App\Models\StaticMenu;
use App\Models\StaticMenuType;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StaticPageController extends Controller
{

    public function staticContent(){

        $data = StaticContent::search()->latest()->paginate(10);

        return response()->json([
           'success' => true,
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
                'success' => false,
                'message' => $validator->errors()->all()
            ],422);
        }

        $data = new StaticContent();
        $data->title = $request->title;
        $data->description = $request->description;
        $data->save();

        return response()->json([
            'success' => true,
            'message' => 'Menu content created successfully.'
        ],200);
    }



    public function staticContentDetail($id){
        $data = StaticContent::find($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ],is_null($data) ? 204 : 200);
    }


    public function staticContentUpdate(Request $request,$id){

        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:500|min:3',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
            ],422);
        }

        $data = StaticContent::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Menu content not found.'
            ],404);
        }

        if ($data->is_updatable == 0) {

            return response()->json([
                'success' => false,
                'message' => 'You can not update this content.'
            ],404);
        }

        $data->title = $request->title;
        $data->description = $request->description;
        $data->save();

        return response()->json([
            'success' => true,
            'message' => 'Menu content updated successfully.'
        ]);
    }


    public function staticContentDelete($id){

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


    public function staticMenu(){

        $data = StaticMenu::with('staticContent','staticMenuType')->latest()->paginate(10);

        return response()->json([
           'status' => true,
           'data'   => $data
        ], $data->isEmpty() ? 204 : 200);
    }


    public function staticMenuStore(Request $request){

        $validator = Validator::make($request->all(), [
            'menu_name'           => 'required|string|max:200|min:3',
            'parent_menu_id'      => 'nullable|exists:static_menus,id',
            'static_contents_id'  => 'required|exists:static_contents,id',
            'static_menu_type_id' => 'required|exists:static_menu_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
            ],422);
        }


        $data = new StaticMenu();
        $data->menu_name = $request->menu_name;
        $data->parent_menu_id = $request->parent_menu_id;
        $data->static_contents_id = $request->static_contents_id;
        $data->static_menu_type_id = $request->static_menu_type_id;
        $data->save();

        return response()->json([
            'status' => true,
        ], 201);
    }


    public function staticMenuDetail($id){
        $data = StaticMenu::with('staticContent')->find($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }


    public function staticMenuUpdate(Request $request,$id){

        $validator = Validator::make($request->all(), [
            'menu_name'           => 'required|string|max:200|min:3',
            'parent_menu_id'      => 'nullable|exists:static_menus,id',
            'static_contents_id'  => 'required|exists:static_contents,id',
            'static_menu_type_id' => 'required|exists:static_menu_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
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
            ],404);
        }

        $data->menu_name           = $request->menu_name;
        $data->parent_menu_id      = $request->parent_menu_id ?? null;
        $data->static_contents_id  = $request->static_contents_id;
        $data->static_menu_type_id = $request->static_menu_type_id;
        $data->save();

        return response()->json([
            'status' => true,
        ]);
    }


    public function staticMenuDelete($id){

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
                'errors' => ['S']
            ]);
        }

    }


    public function staticMenuTypes()
    {
        $data = StaticMenuType::latest()->get();

        return response()->json([
           'status' => true,
           'data'    => $data,
        ],count($data)==0 ? 204 : 200);
    }


    public function staticMenuStatusChange(Request $request,$id){

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
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
            'message' => 'Menu status updated successfully.'
        ],200);
        
    }

}
