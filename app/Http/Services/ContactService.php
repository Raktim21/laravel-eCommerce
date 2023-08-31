<?php

namespace App\Http\Services;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactService
{
    protected $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function getAll()
    {
        return $this->contact->clone()
        ->with(['user' => function($q) {
            $q->select('id','name');
        }])->latest()->paginate(20);
    }

    public function store(Request $request): void
    {
        $this->contact->clone()->create([
            'user_id'           => auth()->guard('user-api')->check() ? auth()->guard('user-api')->user()->id : null,
            'guest_session_id'  => !auth()->guard('user-api')->check() ? uniqid('GUEST-') : null,
            'first_name'        => $request->first_name,
            'last_name'         => $request->last_name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'message'           => $request->message,
            'ip_address'        => $request->ip(),
        ]);
    }

    public function delete($id): void
    {
        $this->contact->clone()->findOrFail($id)->delete();
    }

    public function multipleDeletes(Request $request): void
    {
        $this->contact->clone()->whereIn('id',$request->ids)->delete();
    }
}
