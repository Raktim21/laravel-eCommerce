<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\OrderStatus;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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
            if( ob_get_level() > 0 ) {
                for( $i=0; $i < ob_get_level(); $i++ ) ob_flush();
                ob_end_clean();
            }

            $payload = JWTAuth::manager()->getJWTProvider()->decode($token);

            $start_time = time();

            if ($payload && !$payload['refresh_token'])
            {
                $user = JWTAuth::setToken($token)->toUser();

                if ($user)
                {
                    return new StreamedResponse(function () use ($start_time) {

                        echo ":" . str_repeat(" ", 2048) . "\n"; // adding 2kB padding for IE Bug
                        echo "retry: 2000\n";

                        $c = 0;
                        while ((time() - $start_time) < 30)
                        {
                            $notifications = Notification::
                                select('id','data','read_at','created_at')
                                ->where('notifiable_id', '=', auth()->user()->id)
                                ->where('is_send', '=', 0)
                                ->orderByDesc('created_at')
                                ->get();

                            foreach ($notifications as $notification)
                            {
                                $data = json_encode($notification);
                                echo "id: {$notification->id}\n";
                                echo "data: {$data}\n\n";

//                              Mark the notification as sent
                                $notification->update(['is_send' => 1]);

                                if( ob_get_level() > 0 ) for( $i=0; $i < ob_get_level(); $i++ ) ob_flush();
                                flush();

                                $c++;
                                if( $c % 1000 == 0 ){
                                    gc_collect_cycles();
                                    $c=1;
                                }
                            }

                            if (connection_aborted()) {break;}

                            usleep(50000); // 50ms
                        }

                    }, 200, [
                        'Content-Type'      => 'text/event-stream',
                        'Cache-Control'     => 'no-cache',
                        'Connection'        => 'keep-alive',
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
