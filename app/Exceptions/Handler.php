<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];


    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // $this->reportable(function (Throwable $e) {
        //     //
        // });


        $this->renderable(function (NotFoundHttpException $e,Request $request) {
            if (request()->ajax() || request()->wantsJson() || $request->is('api/*') ) {
                return response()->json([
                    'status' => false,
                    'errors'  => ['Records not found'],
                ], 404);
            }
        });


        // if ($this->isHttpException($exception)) {
        //     return $this->renderHttpException($exception);
        // } else {
        //     // Handle all 500 errors with a common message.
        //     return response()->json(['error' => 'Internal Server Error'], 500);
        // }


        // $this->renderable(function (GeneralJsonException $e,Request $request) {

        // });
    }



    public function render($request, Throwable $exception)
    {
        if ($this->isHttpException($exception)) {
            return $this->renderHttpException($exception);
        } else {
            Log::info($exception);
            return response()->json([
                'status' => false,
                'errors' => ['Internal Server Error']
            ], 500);
        }
    }


}
