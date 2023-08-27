<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactBulkDeleteRequest;
use App\Http\Services\ContactService;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{

    protected $service;

    public function __construct(ContactService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $data = $this->service->getAll();

        return response()->json([
            'status' => true,
            'data'   => $data
        ], $data->isEmpty() ? 204 : 200);

    }


    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'status' => true,
        ]);
    }


    public function bulkDelete(ContactBulkDeleteRequest $request)
    {
        $this->service->multipleDeletes($request);

        return response()->json([
            'status' => true,
        ]);
    }
}
