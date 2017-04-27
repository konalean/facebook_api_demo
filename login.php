<?php 
session_start();
require_once './config/config.php';
require_once './inc/FacebookApi.php';
$client = new FacebookApi($facebookAppId, $facebookSecret);
$loginUrl = $client -> getLoginUrl("http://localhost/loginBack.php", $permissions)
?>

<html>
<head>
</head>
<body>
<a href="<?php echo $loginUrl ?>">login</a>
</body>
</html>