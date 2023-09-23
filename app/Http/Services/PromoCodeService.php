<?php

namespace App\Http\Services;

use App\Models\PromoCode;
use App\Models\PromoProduct;
use App\Models\PromoUser;
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

    public function updateStatus($id): bool
    {
        $promo = $this->code->clone()->findOrFail($id);

        if($promo->is_active == 0 && $promo->end_date <= now())
        {
            return false;
        }

        $promo->is_active = $promo->is_active==0 ? 1 : 0;
        $promo->save();
        return true;
    }

    public function getUserPromos($user_id): array
    {
        $applicablePromos = [];
        $data = $this->code->clone()
            ->with(['products' => function($q) {
                return $q->select('products.id','products.category_id','products.slug','products.name','products.thumbnail_image',
                    'products.display_price','products.previous_display_price')
                    ->with(['category' => function($q1) {
                        return $q1->select('id','name');
                    }])
                    ->with('productReviewRating')
                    ->withSum('inventories', 'stock_quantity');
            }])
            ->where('is_active', 1)
            ->latest()->get();

        foreach ($data as $item)
        {
            if(is_null($item->end_date) || ($item->end_date && $item->end_date>=Carbon::today()->format('Y-m-d'))) {
                if ($item->is_global_user == 1) {
                    if ($item->max_usage != 0) {
                        $promo_usage = PromoUser::where('promo_id', $item->id)
                            ->where('user_id', $user_id)
                            ->first();

                        if ($promo_usage) {
                            if ($promo_usage->usage_number < $item->max_usage) {
                                if ($item->max_num_users == 0) {
                                    $applicablePromos[] = $item;
                                } else if ($item->max_num_users > PromoUser::where('promo_id', $item->id)->whereNot('usage_number',0)->count()) {
                                    $applicablePromos[] = $item;
                                }
                            }
                        } else {
                            if ($item->max_num_users == 0) {
                                $applicablePromos[] = $item;
                            } else if ($item->max_num_users > PromoUser::where('promo_id', $item->id)->whereNot('usage_number',0)->count()) {
                                $applicablePromos[] = $item;
                            }
                        }
                    } else {
                        if ($item->max_num_users == 0) {
                            $applicablePromos[] = $item;
                        } else if ($item->max_num_users > PromoUser::where('promo_id', $item->id)->whereNot('usage_number',0)->count()) {
                            $applicablePromos[] = $item;
                        }
                    }
                } else {
                    $promo_usage = PromoUser::where('promo_id', $item->id)
                        ->where('user_id', $user_id)
                        ->first();

                    if ($promo_usage) {
                        if ($item->max_usage != 0) {
                            if ($item->max_usage > $promo_usage->usage_number) {
                                if ($item->max_num_users == 0) {
                                    $applicablePromos[] = $item;
                                } else if ($item->max_num_users > PromoUser::where('promo_id', $item->id)->whereNot('usage_number',0)->count()) {
                                    $applicablePromos[] = $item;
                                }
                            }
                        } else {
                            if ($item->max_num_users == 0) {
                                $applicablePromos[] = $item;
                            } else if ($item->max_num_users > PromoUser::where('promo_id', $item->id)->whereNot('usage_number',0)->count()) {
                                $applicablePromos[] = $item;
                            }
                        }
                    }
                }
            }
        }

        return $applicablePromos;
    }
}
