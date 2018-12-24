<?php


function apiRes($status , $msg , $data=null)
{
    return response()->json([
        'status' => $status,
        'msg' => $msg,
        'data' => $data
    ], $status);
}


?>