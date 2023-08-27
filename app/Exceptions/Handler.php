<?php

namespace App\Exceptions;

use Doctrine\DBAL\Schema\Exception\IndexNameInvalid;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Mockery\Exception\InvalidOrderException;
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
       
        
        // $this->renderable(function (NotFoundHttpException $e,Request $request) {
        //     if (request()->ajax() || request()->wantsJson() || $request->is('api/*') ) {
        //         return response()->json([
        //             'errors' => ['Object not found'],
        //         ], 404);
        //     }
        // }); 



        // $this->renderable(function (InvalidOrderException $e,Request $request) {
        //     return response()->json([
        //         'errors' => ['Internal server error'],
        //     ], 500);
        // });


    }


}