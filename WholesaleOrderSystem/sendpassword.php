<?php

header('Content-Type: text/html; charset=utf-8');
ob_start();
session_start();
$ipAddress = "yok";
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
}

$serviceConfig = parse_ini_file('serviceConfig.ini');
$username = $_POST['username'];

if ($username == "") {
    header('Location: forgotpassword.php?&result=fail&code=' . $rr["ResponseCode"]);
} else {

    $service_url = $serviceConfig['serviceAddress'] . 'sendpassword/';
    $curl = curl_init($service_url);
    $curl_post_data = array(
        "username" => $username
    );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
//print_r($curl_post_data);
    $curl_response = curl_exec($curl);
    $rr = json_decode($curl_response, true);

    if ($rr["ResponseCode"] == "0") {
        header('Location: forgotpassword.php?&result=true');
    } else {
        header('Location: forgotpassword.php?&result = fail');
    }
}
?>