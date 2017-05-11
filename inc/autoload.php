<?php 
spl_autoload_register(function ($class) {
	//package
	$prefix = 'net\\kon\\';
	$len = strlen($prefix);

	//比較是否為自己的package
	if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
	
    $baseDir = __DIR__ . '/';

	//取得class名稱
    $requireClass = substr($class, $len);
    
    //如果有多層，將 \ 換成 /，要取得目錄
    $file = $baseDir . str_replace('\\', '/', $requireClass) . '.php';

    //判斷檔案是否存在
    if (file_exists($file)) {
        require $file;
    }
    
});
?>