<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationController extends Controller
{


    public function __construct()
    {
        \Config::set('auth.defaults.guard','admin-api');
    }


    public function index()
    {
        if (!request()->token || request()->token == null || request()->token == '' || request()->token == 'null') {
            return response()->json([
                'status'  => false,
                'error'   => 'Unauthenticated',
            ],401);
        }

        $token = request()->token;



        try
        {
            $payload = JWTAuth::manager()->getJWTProvider()->decode($token);

            $start_time = time();

            if ($payload && !$payload['refresh_token'])
            {
                $user = JWTAuth::setToken($token)->toUser();

                if ($user)
                {
                    return new StreamedResponse(function () use ($start_time) {

                        do {
                            $notifications = DB::table('notifications')
                                ->select('id','data','read_at','created_at')
                                ->where('notifiable_id', auth()->user()->id)
//                                ->where('is_send', 0)
                                ->orderByDesc('created_at')
                                ->get();

                            foreach ($notifications as $notification)
                            {
                                $data = json_encode($notification);
                                echo "id: {$notification->id}\n";
                                echo "data: {$data}\n\n";

//                              Mark the notification as sent
//                                DB::table('notifications')
//                                    ->where('id', $notification->id)
//                                    ->update(['is_send' => 1]);

                                ob_flush();
                                flush();
                                sleep(3);
                            }

                        } while ((time() - $start_time) < 60);
                    }, 200, [
                        'Content-Type' => 'text/event-stream',
                        'Cache-Control' => 'no-cache',
                        'Connection' => 'keep-alive',
                        'X-Accel-Buffering' => 'no'
                    ]);
                }
            }
        }
        catch (\Throwable $th) {
            return response()->json( [
                'status'   => false,
                'errors'   => ['Something went wrong']
            ],500 );
        }
    }



    public function getNotifications(){

        $notifications = Notification::where('notifiable_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->select('id', 'data', 'created_at' , 'read_at')->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $notifications
        ], $notifications->isEmpty() ? 204 : 200);
    }


    public function readNotification($id)
    {
        $notification = Notification::where('notifiable_id', auth()->user()->id)->where('id', $id)->first();

        if ($notification)
        {
            $notification->update([
                'read_at' => Carbon::now()
            ]);

            return response()->json([
                'status' => true,
            ]);
        }

        return response()->json([
            'status' => false,
            'errors' => ['Notification not found']
        ], 404);
    }


    public function BulkRead()
    {
        Notification::where('notifiable_id', auth()->user()->id)->where('read_at', null)->update([
            'read_at' => Carbon::now()
        ]);

        return response()->json([
            'status' => true,
        ]);
    }

}
