<?php
	
	// 包含框架文件
	require_once '../../Cike.php';
	
	// 开始执行
	try{
		Cike::application()->run();
	}
	catch(CKException $ex){
		var_dump($ex);
	}