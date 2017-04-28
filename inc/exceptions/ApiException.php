<?php 
namespace net\kon\exceptions;

class ApiException extends \Exception {
	public static $FILE_NOT_EXIST = '100';

	public static $ERROR_MESSAGE = array(
		'100' => 'file not exist'
	);

	public function __construct($errMsg, $errCode, Exception $previousException = null) {
		parent::__construct($errMsg, $errCode, $previousException);
	}
}

?>