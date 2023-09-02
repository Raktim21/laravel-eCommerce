<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
                    $notification_last_id = Notification::where('notifiable_id', auth()->user()->id)->orderByDesc('created_at')->first();

                    $response = new StreamedResponse(function() use ($start_time,$notification_last_id)  {

                        do {

                            if (!isset($_SERVER["HTTP_LAST_EVENT_ID"]) || $_SERVER["HTTP_LAST_EVENT_ID"] == 0) {

                                if ($notification_last_id != null) {

                                    $_SERVER["HTTP_LAST_EVENT_ID"] = $notification_last_id->created_at->toDateTimeString();

                                } else {

                                    $_SERVER["HTTP_LAST_EVENT_ID"] = 0;
                                }

                            }
                            $lastEventId = $_SERVER["HTTP_LAST_EVENT_ID"];

                            $data_get = null;

                            if ($lastEventId != 0) {
                                $data_get = Notification::where('notifiable_id', auth()->user()->id)->where('created_at', '>', Carbon::parse($lastEventId))->orderBy('created_at', 'desc')
                                    ->select('id', 'data', 'created_at' , 'read_at')->first();
                            }

                            if ($data_get) {

                                $_SERVER["HTTP_LAST_EVENT_ID"] = Carbon::parse($data_get->created_at)->toDateTimeString();

                                echo 'data: ' . json_encode($data_get) . "\n\n";
                                ob_flush();
                                flush();

                                $data_get->update([
                                    'is_send' => 1
                                ]);

                            }
//                            else {
//                                echo 'data: ' . "No data found" . "\n\n";
//                                ob_flush();
//                                flush();
//                                $lastEventId = 0;
//                            }

                            sleep(3);

                        } while ($lastEventId != 0 && (time() - $start_time) < 60);
                    });

                    $response->headers->set('Content-Type', 'text/event-stream');
                    $response->headers->set('X-Accel-Buffering', 'no');
                    $response->headers->set('Cache-Control', 'no-cache');
                    return $response;
                }
            }

        } catch (\Throwable $th) {

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
