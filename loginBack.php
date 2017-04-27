<?php 
session_start();
require_once './config/config.php';
require_once './inc/FacebookApi.php';
$client = new FacebookApi($facebookAppId, $facebookSecret);
$accessToken = $client -> getAccessToken();
$_SESSION['fb_access_token'] = $accessToken;
header("Location: showMe.php");
?>