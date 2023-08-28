<?php

namespace App\Exceptions;

use Exception;

class GeneralJsonException extends Exception
{

    public function render($request){
        return response()->json([
            'status' => false,
            'errors' => [$this->getMessage()]
        ], $this->code);
    }
}
