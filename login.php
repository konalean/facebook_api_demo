<?php 
session_start();
require_once './config/config.php';

// require_once './inc/FacebookApi.php';
// require_once './inc/Exceptions/ApiException.php';

use net\kon\FacebookApi;
use net\kon\exceptions\ApiException;

// $exception = new ApiException();
$client = new FacebookApi($facebookAppId, $facebookSecret);
$loginUrl = $client -> getLoginUrl("http://localhost/loginBack.php", $permissions);

// echo ApiException::$FILE_NOT_EXIST;
// print_r(parse_url('http://www.kimo.com.tw/test/123/a.jpg'));

?>

<html>
<head>
</head>
<body>
<a href="<?php echo $loginUrl ?>">login</a>
</body>
</html>