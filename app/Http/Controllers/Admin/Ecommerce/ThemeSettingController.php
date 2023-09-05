<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\ThemeCustomizer;
use App\Models\ThemeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ThemeSettingController extends Controller
{
    protected $themeCustomizer;

    public function __construct(ThemeCustomizer $themeCustomizer)
    {
        $this->themeCustomizer = $themeCustomizer;
    }


    public function index()
    {
        $theme = Cache::remember('themeCustomizer', 60*60*24, function () {
            return $this->themeCustomizer->newQuery()->orderBy('ordering')->get();
        });

        return response()->json([
            'status' => true,
            'data' => $theme
        ]);
    }


    public function positionUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|in:up,down',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $theme   =  $this->themeCustomizer->findOrFail($id);

        $old_order = $theme->ordering;

        $theme_2 =  $this->themeCustomizer->newQuery()->where('ordering', $theme->ordering + ($request->position == 'up' ? -1 : 1))->first();

        if ($theme->is_static_position == 1 || $theme_2->is_static_position == 1) {
            return response()->json([
                'status' => false,
                'errors' => ['You can not customize this theme.'],
            ], 400);
        }

        $theme->update([
            'ordering' => $theme->ordering + ($request->position == 'up' ? -1 : 1)
        ]);

        $theme_2->update([
            'ordering' => $theme_2->ordering + ($request->position == 'up' ? 1 : -1)
        ]);

        ThemeLog::create([
            'theme_event_type_id' => 1,
            'affected_theme_customizer_id' => $id,
            'old_order' => $old_order,
        ]);

        Cache::clear();

        return response()->json([
            'status' => true,
        ]);
    }


    public function valueUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $data = $this->themeCustomizer->find($id);

        if ($data) {
            $old_value = $data->value;

            $data->update([
                'value' => $request->value
            ]);

            ThemeLog::create([
                'theme_event_type_id' => 2,
                'affected_theme_customizer_id' => $id,
                'old_value' => $old_value,
            ]);

            Cache::clear();

            return response()->json([
                'status' => true,
            ]);
        }

        return response()->json([
            'status' => false,
            'errors' => ['Theme customization not found.'],
        ], 404);

    }


    public function activeUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => true,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $theme = $this->themeCustomizer->findOrFail($id);

        if($theme->is_inactivable == 1)
        {
            $theme->update([
                'is_active' => $request->active
            ]);

            Cache::clear();

            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => ['You can not customize this theme.'],
            ], 400);
        }
    }



    public function undo()
    {
        $theme_log = ThemeLog::latest()->first();

        if (!$theme_log) {
            return response()->json([
                'status' => false,
                'errors' => ['Nothing to undo.'],
            ],400);
        }

        if ($theme_log->theme_event_type_id == 1) {

            $theme_1 = $this->themeCustomizer->find($theme_log->affected_theme_customizer_id);
            $old_position = $theme_1->ordering;

            $theme_1->ordering = $theme_log->old_order;
            $theme_1->save();

            $theme_2 = $this->themeCustomizer->newQuery()->where('ordering', $theme_log->old_order)->first();
            $theme_2->ordering = $old_position;
            $theme_2->save();
        } else {
            $theme = $this->themeCustomizer->find($theme_log->affected_theme_customizer_id);
            $theme->value = $theme_log->old_value;
            $theme->save();
        }

        $theme_log->delete();
        Cache::clear();

        return response()->json([
           'status' => true,
        ]);
    }
}

