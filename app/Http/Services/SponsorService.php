<?php

namespace App\Http\Services;

use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SponsorService
{
    protected $sponsor;

    public function __construct(Sponsor $sponsor)
    {
        $this->sponsor = $sponsor;
    }

    public function getAll()
    {
        return $this->sponsor->clone()->latest()->get();
    }

    public function store(Request $request)
    {
        $sponsor = $this->sponsor->clone()->create([
            'name'          => $request->name,
            'url'           => $request->url,
            'image'         => ''
        ]);

        saveImage($request->file('image'), '/uploads/images/sponsors/', $sponsor, 'image');

        Cache::forget('sponsors');
    }

    public function update(Request $request, $id)
    {
        $sponsor = $this->sponsor->clone()->findOrfail($id);

        $sponsor->update([
            'name' => $request->name,
            'url'  => $request->url
        ]);

        if ($request->hasFile('image'))
        {
            deleteFile($sponsor->image);

            saveImage($request->file('image'), '/uploads/images/sponsors/', $sponsor, 'image');
        }

        Cache::forget('sponsors');
    }

    public function delete($id)
    {
        $sponsor = $this->sponsor->clone()->findOrfail($id);
        deleteFile($sponsor->image);
        $sponsor->delete();

        Cache::forget('sponsors');
    }

    public function multipleDelete(Request $request)
    {
        $rows = $this->sponsor->clone()->whereIn('id',$request->ids)->get();

        foreach($rows as $row)
        {
            deleteFile($row->image);
        }

        $this->sponsor->clone()->whereIn('id',$request->ids)->delete();
        Cache::forget('sponsors');
    }
}
