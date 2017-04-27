<?php
session_start();
require_once './config/config.php';
require_once './inc/FacebookApi.php';
$client = new FacebookApi($facebookAppId, $facebookSecret);
$accessToken = $_SESSION['fb_access_token'];
print_r($client -> queryMe($accessToken));
?>