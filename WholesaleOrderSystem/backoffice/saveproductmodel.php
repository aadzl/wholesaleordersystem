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
$phoneModel = $_POST['phoneModel'];
$color = $_POST['color'];
$productId = $_POST['productId'];

$serviceConfig = parse_ini_file('../serviceConfig.ini');

$service_url = $serviceConfig['serviceAddress'] . 'addNewProductModel/';
$curl_post_data = array(
    "productId" => $productId,
    "phoneModelId" => $phoneModel,
    "colorId" => $color
);
$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
$curl_response = curl_exec($curl);
$rr = json_decode($curl_response, true);
//print_r($curl_response);
header('Location: productDetail.php?id=' . $productId);
?>