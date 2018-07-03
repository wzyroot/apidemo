<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{

   /**
    * Render an exception into an HTTP response.
    *
    * @param  \Illuminate\Http\Request $request
    * @param  \Exception $exception
    * @return \Illuminate\Http\Response
    */
   public function render($request, Exception $exception)
   {
       // 参数验证错误的异常，我们需要返回 400 的 http code 和一句错误信息
       if ($exception instanceof ValidationException) {
           return response(['error' => array_first(array_collapse($exception->errors()))], 400);
       }
       // 用户认证的异常，我们需要返回 401 的 http code 和错误信息
       if ($exception instanceof UnauthorizedHttpException) {
           return response($exception->getMessage(), 401);
       }

       return parent::render($request, $exception);
   }
}
