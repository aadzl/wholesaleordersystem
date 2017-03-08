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
$namesurname = $_POST['namesurname'];
$password = $_POST['password'];
$email = $_POST['email'];
if ($namesurname == "" or $password == "" or $email == "") {
    header('Location: index.php?&exp=Please fill all fields');
} else {

    $service_url = $serviceConfig['serviceAddress'] . 'createAccountForMerchant/';
    $curl = curl_init($service_url);
    $curl_post_data = array(
        "namesurname" => $namesurname,
        "email" => $email,
        "password" => $password
    );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);

    $curl_response = curl_exec($curl);
    $rr = json_decode($curl_response, true);

    header('Location: index.php?&exp=' . $rr["ResponseDesc"]);
}
?>