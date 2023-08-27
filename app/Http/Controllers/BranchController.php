<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class BranchController extends Controller
{
    public function getAll()
    {
        $data = Cache::remember('branches', 24*60*60, function () {
            return Branch::get();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255|unique:shop_branches,name',
            'address'           => 'required|string|max:300',
            'latitude'          => 'nullable|string|max:20',
            'longitude'         => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        Branch::create([
            'name'          => $request->name,
            'address'       => $request->address,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude
        ]);

        Cache::delete('branches');

        return response()->json(['status' => true], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255|unique:shop_branches,name,'.$id,
            'address'           => 'required|string|max:300',
            'latitude'          => 'nullable|string|max:20',
            'longitude'         => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        Branch::findOrFail($id)->update([
            'name'          => $request->name,
            'address'       => $request->address,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude
        ]);

        Cache::delete('branches');

        return response()->json(['status' => true]);
    }

    public function delete($id)
    {
        $branch = Branch::findOrFail($id);

        try {
            $branch->delete();

            Cache::delete('branches');

            return response()->json(['status' => true]);
        } catch(QueryException $ex) {
            return response()->json([
                'status' => false,
                'errors' => ['Selected branch can not be deleted.']
            ], 400);
        }
    }
}
