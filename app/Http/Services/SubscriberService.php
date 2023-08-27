<?php

namespace App\Http\Services;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberService
{

    protected $subscriber;

    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function getAll()
    {
        return $this->subscriber->clone()->latest()->paginate(20);
    }

    public function store(Request $request)
    {
        $this->subscriber->clone()->create([
            (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone') => $request->email,
        ]);
    }

    public function delete($id)
    {
        $this->subscriber->clone()->findOrFail($id)->delete();
    }

    public function multipleDeletes(Request $request)
    {
        $this->subscriber->clone()->whereIn('id',$request->ids)->delete();
    }

}
