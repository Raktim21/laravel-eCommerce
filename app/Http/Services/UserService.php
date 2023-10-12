<?php

namespace App\Http\Services;

use App\Jobs\NewAdminJob;
use App\Mail\AdminPasswordMail;
use App\Models\Order;
use App\Models\OrderPickupAddress;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserService
{
    protected $user;


    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function getAuthPermissions()
    {
        $role = auth()->guard('admin-api')->user()->roles;

        if($role && $role[0])
        {
            return Role::find($role[0]['id'])->permissions;
        }

        return [];
    }


    public function getAllUser($isAdmin)
    {
        return $this->user->clone()
            ->when(!$isAdmin, function ($q) {
                return $q->where(function ($q1) {
                    $q1->where('name', 'like', '%'.request()->input('search').'%')
                        ->orWhere('username', 'like', '%'.request()->input('search').'%')
                        ->orWhere('phone', 'like', '%'.request()->input('search').'%');
                })->where('is_active', 1)->whereNull('shop_branch_id');
            })
            ->when($isAdmin, function ($q) {
                return $q->where(function ($q1) {
                    $q1->where('name', 'like', '%'.request()->input('search').'%')
                        ->orWhere('username', 'like', '%'.request()->input('search').'%')
                        ->orWhere('phone', 'like', '%'.request()->input('search').'%');
                })
                ->whereNot('id', auth()->user()->id)->whereHas('roles', function ($query) {
                    $query->whereIn('id', [1,2]);
                })->with('roles','branch');
            })
            ->with('profile')->latest()->paginate(10);
    }


    public function storeAdmin(Request $request): bool
    {
        DB::beginTransaction();
        try {
            $password = Str::random(8);

            $admin = $this->user->clone()
                ->create([
                    'shop_branch_id'    => $request->shop_branch_id,
                    'name'              => $request->name,
                    'username'          => $request->username,
                    'password'          => Hash::make($password),
                    'phone'             => $request->phone,
                    'email_verified_at' => Carbon::now(),
                    'phone_verified_at' => Carbon::now(),
                ]);

            $admin->assignRole($request->role);

            $profile = UserProfile::create([
                'user_id'           => $admin->id,
                'user_sex_id'       => $request->gender,
                'image'             => ''
            ]);

            if ($request->hasFile('avatar')) {
                saveImage($request->file('avatar'), '/uploads/admin/avatars/', $profile, 'image');
            }

            $data = array(
                'user'      => $admin->name,
                'password'  => $password
            );

//            dispatch(new NewAdminJob($admin->username, $data));

            Mail::to($admin->username)->send(new AdminPasswordMail($data));

            DB::commit();
            return true;
        } catch(QueryException $e)
        {
            DB::rollback();
            return false;
        }
    }


    public function show($id, $isAdmin)
    {
        return $this->user->clone()
            ->when($isAdmin, function ($q) {
                return $q->with('roles','branch');
            })
            ->when(!$isAdmin, function ($q) {
                return $q->with(['addresses' => function ($q) {
                    $q->with('upazila.district.division.country')->with('union');
                }]);
            })->with('profile.gender')->find($id);
    }


    public function getUserAddress($id)
    {
        return UserAddress::with(['upazila.district.division.country'])
            ->with('union')
            ->where('user_id', $id)
            ->where('is_active', '=',1)
            ->latest()
            ->get();
    }


    public function getOrders($id)
    {
        return Order::where('user_id', $id)
            ->with('status','paymentMethod','deliveryMethod','deliveryAddress','promoCode','paymentStatus')
            ->withCount('items')->latest()->get();
    }


    public function orderReport(Request $request, $id)
    {
        return DB::table('orders')
            ->where('orders.user_id','=',$id)
            ->when($request->start_date != null && $request->end_date != null, function ($query) use ($request) {
                $query->whereBetween('orders.created_at', [$request->start_date, $request->end_date]);
            })
            ->leftJoin('order_items','orders.id','=','order_items.order_id')
            ->leftJoin('product_combinations','order_items.product_combination_id','=','product_combinations.id')
            ->leftJoin('products','product_combinations.product_id','=','products.id')
            ->selectRaw(
                "distinct product_id,products.name,count(*) as no_times,
                                    (select count(id) from orders where user_id=". $id .") as order_count"
            )->groupBy('product_combinations.product_id','products.name')
            ->orderByDesc('no_times')->get();
    }


    public function update(Request $request, $id, $profile, $isAdmin): void
    {
        $user = $this->user->clone()->findOrFail($id);

        $user->update([
            'shop_branch_id'    => (!$profile && $isAdmin && $request->has('shop_branch_id')) ? $request->shop_branch_id : $user->shop_branch_id,
            'username'          => $request->username,
            'name'              => $request->name,
            'phone'             => $request->phone
        ]);

        $user->profile->update([
            'user_sex_id'       => $request->gender,
        ]);

        if(!$profile && $isAdmin && $request->has('role')) {
            $user->roles()->detach();
            $user->assignRole($request->role);
            Cache::forget('permissions'.$id);
        }
    }


    public function updateAvatar(Request $request, $id, $isAdmin): void
    {
        $user = $this->user->clone()->findOrFail($id);

        if ($user->profile->image != null)
        {
            deleteFile($user->profile->image);
        }

        saveImage($request->file('avatar'), $isAdmin ?'/uploads/admin/avatars/' : '/uploads/customer/avatars/', $user->profile, 'image');
    }


    public function updatePassword(Request $request): bool
    {
        if(!Hash::check($request->old_password, auth()->user()->password))
        {
            return false;
        }

        $user = $this->user->clone()->find(auth()->user()->id);

        $user->password = Hash::make($request->password);
        $user->save();

        return true;
    }


    public function storeAddress(Request $request, $uid): void
    {
        $address =  UserAddress::create([
            'user_id'    => $uid,
            'address'    => $request->address,
            'phone_no'   => $request->phone_no,
            'upazila_id' => $request->upazila_id,
            'union_id'   => $request->union_id,
            'postal_code'=> $request->postal_code,
            'lat'        => $request->lat,
            'lng'        => $request->lng,
        ]);

        if ($request->is_default == 1) {
            $address->makeDefault();
        }
    }


    public function updateAddress(Request $request, $id, $isAdmin): bool
    {
        $address = UserAddress::findOrFail($id);

        if ($isAdmin == 0 && $address->user_id != auth()->user()->id) {
            return false;
        }

        $address->update([
            'address'    => $request->address,
            'phone_no'   => $request->phone_no,
            'upazila_id' => $request->upazila_id,
            'union_id'   => $request->union_id,
            'postal_code'=> $request->postal_code,
            'lat'        => $request->lat,
            'lng'        => $request->lng
        ]);

        if ($request->is_default && $request->is_default == 1) {
            $address->makeDefault();
        }

        return true;
    }


    public function makeAddressDefault($id): bool
    {
        $address = UserAddress::findOrFail($id);

        if (auth()->guard('user-api')->check() && ($address->user_id != auth()->guard('user-api')->user()->id)) {
            return false;
        }

        $address->makeDefault();
        return true;
    }


    public function deleteCustomer($id): bool
    {
        $user = $this->user->clone()->findOrfail($id);

        if($user->hasRole('Customer')) {
            $user->addresses()->delete();
            $user->contactForms()->delete();
            $user->cart()->delete();
            $user->wishlist()->delete();
            $user->requests()->delete();
            $user->messenger_subscriptions()->delete();
            $user->delete();
            return true;
        }
        return false;
    }

    public function deleteAdmin($id): bool
    {
        $user = $this->user->clone()->findOrfail($id);

        if($user->hasRole('Super Admin')) {
            return false;
        }
        $user->delete();
        return true;
    }


    public function deleteCustomers(Request $request): void
    {
        $this->user->clone()->whereIn('id', $request->ids)->delete();
    }


    public function deleteAdmins(Request $request): void
    {
        $this->user->clone()->whereIn('id', $request->ids)->delete();
    }


    public function deleteAddress($id): int
    {
        $address = UserAddress::findOrFail($id);

        if (auth()->guard('user-api')->check() && ($address->user_id != auth()->guard('user-api')->user()->id)) {
            return 1;
        }
        if($address->is_default == 1)
        {
            return 2;
        }
        $address->delete();
        return 3;
    }


    public function deleteAddresses(Request $request): void
    {
        UserAddress::whereIn('id', $request->ids)->update(['is_active' => 0]);
    }


    public function adminAddress($branch)
    {
        return OrderPickupAddress::with('union','upazila.district.division.country','branch')
            ->where('shop_branch_id', $branch)->first();
    }

    public function updateAdminAddress(Request $request): void
    {
        OrderPickupAddress::updateOrCreate(
            ['shop_branch_id' => $request->shop_branch_id],
            [
                'name'          => $request->name,
                'phone'         => $request->phone,
                'email'         => $request->email,
                'upazila_id'    => $request->upazila_id,
                'union_id'      => $request->union_id,
                'postal_code'   => $request->postal_code,
                'address'       => $request->address,
                'lat'           => $request->lat,
                'lng'           => $request->lng
            ]
        );
    }

}
