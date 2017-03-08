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

$serviceConfig = parse_ini_file('../serviceConfig.ini');
$username = $_POST['username'];
$password = $_POST['password'];

if ($username == "" or $password == "") {
    header('Location: index.php?&result=fail&code=Please fill all fields');
} else {

    $service_url = $serviceConfig['serviceAddress'] . 'loginAdmin/';
    $curl = curl_init($service_url);
    $curl_post_data = array(
        "username" => $username,
        "password" => $password
    );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
//print_r($curl_post_data);
    $curl_response = curl_exec($curl);
    $rr = json_decode($curl_response, true);

    if ($rr["ResponseCode"] == "0") {
        $_SESSION['UserId'] = $rr["UserId"];
        $_SESSION['NameSurname'] = $rr["NameSurname"];
        $_SESSION['Email'] = $rr["Email"];
        header('Location: products.php');
    } else {
        print_r($curl_response);
        $_SESSION['ResponseDescription'] = $rr["ResponseDesc"];
        header('Location: index.php?&result=fail&code=' . $rr["ResponseCode"]);
    }
}
?>