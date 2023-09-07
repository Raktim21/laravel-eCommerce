<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
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
                            Log::info('notification - ' . $start_time);

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
                            DB::disconnect();
                            sleep(3); // 50ms
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

    public function getNotifications()
    {
        $notifications = Notification::where('notifiable_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->select('id', 'data', 'created_at' , 'read_at')->paginate(10);

        Notification::where('notifiable_id', auth()->user()->id)
            ->where('is_send', 0)->update([
                'is_send' => 1
            ]);

        return response()->json([
            'status' => true,
            'data' => $notifications
        ], $notifications->isEmpty() ? 204 : 200);
    }


    public function readNotification($id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();

        return response()->json([
            'status' => true,
        ]);

    }


    public function BulkRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => true,
        ]);
    }

}
