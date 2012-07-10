<?php
	
	// 定义APP_DIR目录名称
	define('APP_DIR', 'app');
	
	// 包含框架文件
	require_once '../../Cike.php';
	
	// 开始执行
	try{
		Cike::application()->run();
	}
	catch(CKException $ex){
		print_r($ex);
	}