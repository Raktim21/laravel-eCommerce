<?php

namespace App\Http\Services;

use App\Models\ProductHasPromo;
use App\Models\PromoCode;
use App\Models\PromoProduct;
use App\Models\PromoUser;
use App\Models\UserPromo;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoCodeService
{
    protected $code;

    public function __construct(PromoCode $code)
    {
        $this->code = $code;
    }

    public function getList()
    {
        return $this->code->clone()
            ->with(['products' => function($q) {
                return $q->select('products.id','products.name');
            }])->with(['users' => function($q) {
                return $q->select('users.id','users.name');
            }])->latest()->paginate(10);
    }

    public function store(Request $request): bool
    {
        DB::beginTransaction();

        try {
            $promo = $this->code->clone()->create([
                'title'             => $request->title,
                'code'              => $request->code,
                'discount'          => $request->discount,
                'start_date'        => Carbon::parse($request->start_date)->format('Y-m-d H-i-s'),
                'end_date'          => $request->expiration==1 ? Carbon::parse($request->end_date)->format('Y-m-d H-i-s') : null,
                'is_percentage'     => $request->is_percent,
                'max_usage'         => $request->max_usage ?? 0,
                'max_num_users'     => $request->max_num_users ?? 0,
                'is_global_user'    => $request->is_global_user,
                'is_global_product' => $request->is_global_product
            ]);

            if($request->is_global_product == 0)
            {
                foreach($request->products as $product)
                {
                    PromoProduct::create([
                        'product_id'    => $product,
                        'promo_id'      => $promo->id,
                    ]);
                }
            }

            if($request->is_global_user == 0)
            {
                foreach ($request->users as $user)
                {
                    PromoUser::create([
                        'user_id'   => $user,
                        'promo_id'  => $promo->id,
                    ]);
                }
            }

            DB::commit();
            return true;
        } catch (QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    public function get($id)
    {
        return $this->code->clone()->where('is_active', 1)
            ->with(['products' => function($q) {
                return $q->select('products.id','products.name');
            }])->with(['users' => function($q) {
                return $q->select('users.id','users.name');
            }])->find($id);
    }

    public function update(Request $request, $id)
    {
        $this->code->clone()->findOrFail($id)->update([
            'title'             => $request->title,
            'code'              => $request->code,
            'discount'          => $request->discount,
            'start_date'        => Carbon::parse($request->start_date)->format('Y-m-d H-i-s'),
            'end_date'          => $request->expiration==1 ? Carbon::parse($request->end_date)->format('Y-m-d H-i-s') : null,
            'is_percentage'     => $request->is_percent,
            'max_usage'         => $request->max_usage,
            'max_num_users'     => $request->max_num_users ?? 0
        ]);
    }

    public function updateStatus($id)
    {
        $promo = PromoCode::findOrFail($id);

        $promo->is_active = $promo->is_active==0 ? 1 : 0;
        $promo->save();
    }
}
