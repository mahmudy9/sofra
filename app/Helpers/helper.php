<?php


function apiRes($status , $msg , $data=null)
{
    return response()->json([
        'status' => $status,
        'msg' => $msg,
        'data' => $data
    ], $status);
}


function send_notification(array $data=null , array $player_ids , array $content)
{
    
    $fields = array(
        'app_id' => env('ONESIGNAL_APPID'),
        'include_player_ids' => $player_ids,
        'data' => $data,
        'contents' => $content
    );
    
    $fields = json_encode($fields);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8' ,
     'Authorization: Basic '.env('ONESIGNAL_REST_API_KEY')));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function notifyByFirebase($title, $body, $tokens, $data = [], $is_notification = true)
{
    $registrationIDs = $tokens;

// 'vibrate' available in GCM, but not in FCM
    $fcmMsg = array(
        'body' => $body,
        'title' => $title,
        'sound' => "default",
        'color' => "#203E78"
    );

    $fcmFields = array(
        'registration_ids' => $registrationIDs,
        'priority' => 'high',
        'data' => $data
    );
    if ($is_notification)
    {
        $fcmFields['notification'] = $fcmMsg;
    }

    $headers = array(
        'Authorization:key=' . env('FIREBASE_API_ACCESS_KEY'),
        'Content-Type:application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($result)
    {
        return $result;

    }else {
        return $error;
    }

}

?>