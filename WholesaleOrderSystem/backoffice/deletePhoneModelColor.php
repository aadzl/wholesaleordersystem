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
$productId = $_GET['productId'];
$id = $_GET['id'];
$serviceConfig = parse_ini_file('../serviceConfig.ini');

$service_url = $serviceConfig['serviceAddress'] . 'deleteProductPhoneModolColor/';
$curl_post_data = array(
    "id" => $id
);
$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
$curl_response = curl_exec($curl);
$rr = json_decode($curl_response, true);

header('Location: productDetail.php?id=' . $productId . '&deleted=true');
?>