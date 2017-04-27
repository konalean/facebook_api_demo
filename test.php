<?php 
session_start();
require_once './config/config.php';
require_once './inc/FacebookApi.php';
$client = new FacebookApi($facebookAppId, $facebookSecret);
print_r($client -> getLoginUrl("http://localhost/", $permissions));
?>