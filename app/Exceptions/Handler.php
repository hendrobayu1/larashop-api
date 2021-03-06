<?php

namespace App\Exceptions;

use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // return parent::render($request, $exception);
        $debug = config('app.debug');
        $message = '';
        $status_code = 500;
        if($exception instanceof ModelNotFoundException){
            $message = 'Resource is not found';
            $status_code = 404;
        }else if($exception instanceof NotFoundHttpException){
            $message = 'Endpoint is not found';
            $status_code = 404;
        }else if($exception instanceof MethodNotAllowedException){
            $message = 'Method is not allowed';
            $status_code = 405;
        }else if($exception instanceof ValidationException){
            $validationError = $exception->validator->errors()->getMessages();
            $validationError = array_map(function ($error){
                return array_map(function ($message){
                    return $message;
                },$error);
            },$validationError);
            $message = $validationError;
            $status_code = 405;
        }else if($exception instanceof QueryException){
            if($debug){
                $message = $exception->getMessage();
            }else{
                $message = 'Query failed to execute';
            }
            $status_code = 500;
        }
        $rendered = parent::render($request,$exception);
        $status_code = $rendered->getStatusCode();
        if(empty($message)){
            $message = $exception->getMessage();
        }
        $errors = [];
        if($debug){
            $errors['exception'] = get_class($exception);
            $errors['trace'] = explode("\n",$exception->getTraceAsString());
        }
        return response()->json([
            'code' => $status_code,
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ],$status_code);
    }
}
