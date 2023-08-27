<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\HomepageRequest;
use App\Http\Requests\ProductBulkDeleteRequest;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }


    public function index(HomepageRequest $request)
    {
        $data = $this->service->getAll($request, 1);

        return response()->json([
            'status'   => true,
            'data'     => $data
        ], $data->isEmpty() ? 204 : 200);
    }


    public function store(ProductStoreRequest $request)
    {
        if($request->has('attribute_list'))
        {
            $msg = $this->validateAttributes($request->attribute_list);

            if($msg !== 'done') {
                return response()->json([
                    'status'  => false,
                    'errors' => [$msg],
                ],422);
            }
        }
        $product_id = $this->service->store($request);

        if($product_id != 0) {
            return response()->json([
                'status'        => true,
                'data'          => array(
                    'product_id'    => $product_id
                )
            ],201);
        } else {
            return response()->json([
                'status'        => false,
                'errors'        => $product_id
            ],500);
        }
    }


    private function validateAttributes($attribute_list): string
    {
        $data = json_decode($attribute_list, true);

        if(!is_array($data)) {
            return 'The attribute field must be an array.';
        }
        if(count($data) > 3) {
            return 'Upto 3 attributes can be added.';
        }

        $validator = Validator::make($data, [
            '*.name'        => 'required|string|distinct',
            '*.values'      => 'required|array|min:1',
        ], [
            '*.values.required'   => 'The attribute value field is required.',
            '*.values.array'      => 'The attribute value field must be an array.',
            '*.name.distinct'     => 'Two attribute names must not be similar.',
            '*.variants.min'      => 'The attribute value field must have at least 1 value.'
        ]);

        if($validator->fails()) {
            return $validator->errors()->first();
        } else {
            return 'done';
        }
    }



    public function detail($id)
    {
        $data = $this->service->get($id);

        return response()->json([
            'status' => true,
            'data'   => $data
        ], is_null($data) ? 204 : 200);
    }


    public function update(ProductUpdateRequest $request, $id)
    {
        $this->service->update($request, $id);

        return response()->json([
            'status' => true,
        ]);
    }


    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'status' => true,
        ]);
    }


    public function productBulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'        => 'required|array',
            'ids.*'      => 'required|exists:products,id',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status'    => false,
                'errors'    => $validator->errors()->all()
            ]);
        }


        if($this->service->multipleDelete($request))
        {
            return response()->json([
                'status' => true,
            ]);
        }
        else {
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.']
            ], 500);
        }
    }



    public function multipleImageDelete($id)
    {
        $this->service->imageDelete($id);

        return response()->json([
            'status' => true,
        ]);
    }


    public function reviewApproved($id)
    {
        $this->service->updateStatus($id);

        return response()->json([
            'status' => true,
        ]);
    }

    public function reviewReply(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reply' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $this->service->adminReply($request->reply, $id);

        return response()->json([
            'status' => true,
        ], 201);
    }

    public function abuseReports()
    {
        $data = $this->service->getAbuseReports();

        return response()->json([
            'status' => true,
            'data' => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function changeAbuseStatus(Request $request, $id)
    {
        $this->service->changeAbuseStatus($request->status, $id);

        return response()->json([
            'status' => true
        ]);
    }

    public function restockRequests()
    {
        $data = $this->service->getAllRestock();

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function reviewGetAll()
    {
        $data = $this->service->getAllReviews();

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function getReview($id)
    {
        $data = $this->service->getReview($id);

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], is_null($data) ? 204 : 200);
    }

}
