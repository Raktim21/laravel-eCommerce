<?php

namespace App\Http\Controllers\Customer;

use App\Http\Requests\WishBulkDeleteRequest;
use App\Http\Requests\WishStoreRequest;
use App\Http\Requests\WishToCartRequest;
use App\Http\Services\WishlistService;
use App\Http\Controllers\Controller;
use App\Mail\SendWishListMail;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    protected $service;

    public function __construct(WishlistService $service)
    {
        $this->service = $service;
    }

    public function getList()
    {
        if(\request()->has('wishlist_token') && \request()->has('wishlist_id'))
        {
            $data = $this->service->getSharedWish(request()->input('wishlist_token'), request()->input('wishlist_id'));
        }
        else {
            $data = $this->service->getAuthWish();
        }

        return response()->json([
            'status'    => true,
            'data'      => $data
        ]);
    }

    public function store(WishStoreRequest $request)
    {
        if($this->service->storeWish($request))
        {
            return response()->json([
                'status' => true,
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.']
            ], 500);
        }
    }

    public function addToCart($id)
    {
        if($this->service->convertToCart($id) == 1)
        {
            return response()->json(['status' => true]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => ['You are not authorized to add this wish.']
            ], 401);
        }
    }

    public function delete($id)
    {
        if($this->service->deleteWish($id))
        {
            return response()->json([
                'status' => true,
            ]);
        }
        else {
            return response()->json([
                'status' => false,
                'errors' => ['You are not authorized to delete this wish.']
            ], 401);
        }
    }

    public function deleteItem($id)
    {
        if($this->service->deleteWishItem($id))
        {
            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => ['You are not authorized to delete this wish.']
            ], 401);
        }
    }

    public function bulkDelete(WishBulkDeleteRequest $request)
    {
        $this->service->multipleDelete($request);

        return response()->json([
            'status' => true,
        ]);
    }

    public function sendWishList(Request $request,$id)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }


        $wishlist = Wishlist::find($id);

        if (!$wishlist) {
            return response()->json([
                'status' => false,
                'errors' => ['Wishlist not found.']
            ], 404);
        }

        try {
            Mail::to($request->email)->send(new SendWishListMail($wishlist));
        } catch (\Throwable $th) {}

        return response()->json([
            'status' => true,
        ]);

    }
}
