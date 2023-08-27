<?php

namespace App\Http\Controllers\Admin\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\BillingStoreRequest;
use App\Http\Services\BillingService;
use App\Models\BillingCart;
use App\Models\BillingCartItems;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BillingCartController extends Controller
{

    protected $service;

    public function __construct(BillingService $service)
    {
        $this->service = $service;
    }


    /**
     * @OA\Get(
     *     path="/api/admin/billing-cart-list",
     *     tags={"Billing Cart"},
     *     summary="Billing Cart List",
     *     description="Billing Cart List",
     *     operationId="billingCartList",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cart List",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cart List"),
     *             @OA\Property(
     *                 property="cart",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=1),
     *                     @OA\Property(property="price", type="integer", example=1),
     *                     @OA\Property(property="total", type="integer", example=1),
     *                     @OA\Property(property="variation", type="string", example=""),
     *                     @OA\Property(property="customer_id", type="integer", example=1),
     *                 )
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *         ),
     *     ),
     * )
     *
     *
    */
    public function cartList()
    {
        $data = $this->service->getCart();

        return response()->json([
                'status' => true,
                'data' => $data
        ], $data->isEmpty() ? 204 : 200);
    }


    /**
     * @OA\Post(
     *      path="/api/user/billing-cart-store",
     *      operationId="addBillingCart",
     *      tags={"Billing Cart"},
     *      summary="Add product to billing cart",
     *      description="Add product to billing cart",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"product_id","quantity"},
     *              @OA\Property(property="product_id", type="integer", example=61),
     *              @OA\Property(property="quantity", type="integer", example=6),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Product is added to cart"),
     *          ),
     *      ),
     *
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated"),
     *              @OA\Property(property="errors", type="object", example=""),
     *              @OA\Property(property="status_code", type="integer", example="401"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Forbidden"),
     *              @OA\Property(property="errors", type="object", example=""),
     *              @OA\Property(property="status_code", type="integer", example="403"),
     *          ),
     *      ),
     *  )
    */
    public function cartStore(BillingStoreRequest $request)
    {
        $cart_id = $this->service->store($request);

        if($cart_id != 0)
        {
            return response()->json([
                'status' => true,
                'data'   => array('billing_id' => $cart_id)
            ], 201);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.'],
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/user/billing-cart-delete/{id}",
     *     operationId="billingcartDelete",
     *     tags={"Billing Cart"},
     *     summary="Billing Cart Delete",
     *     description="Billing Cart Delete",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Billing Cart Id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *
     *    @OA\Response(
     *        response=200,
     *        description="Cart Deleted",
     *        @OA\JsonContent(
     *            @OA\Property(property="status", type="boolean", example=true),
     *            @OA\Property(property="message", type="string", example="Cart Deleted"),
     *        ),
     *    ),
     *
     *    @OA\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @OA\JsonContent(
     *            @OA\Property(property="status", type="boolean", example=false),
     *            @OA\Property(property="message", type="string", example="Unauthenticated"),
     *        ),
     *    ),
     *
     *    @OA\Response(
     *        response=422,
     *        description="Unprocessable Entity",
     *        @OA\JsonContent(
     *            @OA\Property(property="status", type="boolean", example=false),
     *            @OA\Property(property="message", type="string", example="The given data was invalid."),
     *        )
     *    ),
     * )
    */
    public function cartDelete($id)
    {
        if($this->service->deleteBill($id))
        {
            return response()->json([
                'status' => true,
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => ["This cart can not be deleted."]
            ], 400);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/admin/convert-billing-to-order/{id}",
     *      operationId="convertBilling",
     *      tags={"Billing Cart"},
     *      summary="convert billing to order",
     *      description="Order products of billing cart",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="order is placed"),
     *          ),
     *      ),
     *
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated"),
     *              @OA\Property(property="errors", type="object", example=""),
     *              @OA\Property(property="status_code", type="integer", example="401"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Forbidden"),
     *              @OA\Property(property="errors", type="object", example=""),
     *              @OA\Property(property="status_code", type="integer", example="403"),
     *          ),
     *      ),
     *  )
    */
    public function convertBilling($id)
    {
        $status = $this->service->convert($id);

        if($status == 1)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Order is already placed.']
            ], 400);
        }
        else if($status == 2)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Select customer first.']
            ], 422);
        }
        else if($status == 3)
        {
            return response()->json([
                'status' => true,
            ], 201);
        }
        else if($status == 4)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Some of the selected product is out of stock.']
            ], 400);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong']
            ], 500);
        }
    }
}
