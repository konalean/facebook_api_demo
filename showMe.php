<?php
session_start();
require_once './config/config.php';
require_once './inc/FacebookApi.php';

use net\kon\FacebookApi;
use net\kon\exceptions\ApiException;

$client = new FacebookApi($facebookAppId, $facebookSecret);
$accessToken = $_SESSION['fb_access_token'];

/*====================================
=            取得個人資訊 start        =
====================================*/
print_r($client -> queryMe($accessToken));
/*=====    取得個人資訊 End     ======*/




/*=============================================
=           建立相本、上傳圖片  start           =
=============================================*/
$response = $client -> createAlbum($accessToken, 'me', 'kon create album');
$albumId = $response['id'];
$photo = __DIR__.'/1.jpg';
try {
	print_r($client -> uploadPhotoToAlbum($accessToken, $albumId, $photo, 'hahah'));
}
catch(Exception $e) {
	echo 'upload error: ' . $e -> getMessage();
}
/*=====        建立相本、上傳圖片 end     ======*/



?>