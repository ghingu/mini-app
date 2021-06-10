<?php

namespace App\Http\Controllers;

use View;
use Response;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *   @SWG\Info(
 *     title="Laravel Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{

    /**
     * AppBaseController constructor.
     */
    public function __construct(){
    }

    /**
     * @param $result
     * @param $message
     * @return mixed
     */
    public function sendResponse($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    }

    /**
     * @param $error
     * @param int $code
     * @return mixed
     */
    public function sendError($error, $code = 404)
    {
        return Response::json(ResponseUtil::makeError($error), $code);
    }

    /**
     * @param $message
     * @return mixed
     */
    public function responseError($message)
    {
        return Response::json(array('ResponseCode' => '0', 'ResponseMessage' => $message));
    }


    /**
     * @param $data
     * @return mixed
     */
    public function responseWithData($data)
    {
        return Response::json(array('ResponseCode' => '1', 'ResponseMessage' => 'success', 'data' => $data));
    }

    /**
     * @param $message
     * @return mixed
     */
    public function responseSuccess($message)
    {
        return Response::json(array('ResponseCode' => '1', 'ResponseMessage' => $message));
    }
}
