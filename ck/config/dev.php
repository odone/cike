<?php

! defined ( 'IN_CIKE' ) ? null : '!';

return array (
	'mvc.mode' => 'standard',
	'mvc.response_charset' => 'gbk',
	'mvc.path_accessor' => 'q',
	'mvc.rules' => array(),
	
	'controller.dir' => 'controllers',
	'controller.accessor' => 'C',
	'controller.prefix' => '',
	'controller.suffix' => 'Controller',
	'controller.default' => 'home',

	'action.before' => '__before',
	'action.after' => '__after',

	'action.accessor' => 'A',
	'action.prefix' => '',
	'action.suffix' => 'Action',
	'action.default' => 'index',

	'session.name' => '',
	'session.key' => APP_ID . '.USER',

	'rbac.key' => APP_ID . '.RBAC',
	'rbac.acl' => '',
	
	'cookie.prefix' => APP_ID . '.',
	
	'log.enable' => true,
	'log.maxsize' => 102400,
	'log.level' => 'SQL,*SQL,ERROR,REQUEST,POST,SESSION,COOKIE,NOTICE',
	'log.dir' => APP_DIR . DS . 'logs',

	'filecache.dir' => APP_DIR . DS . 'caches',

	'view.cached' => false,
	'view.expires' => 10,

	'phpview.dir' => APP_DIR . DS . 'views',

	'smartyview.class_path' => APP_DIR . '/../libs/smarty/Smarty.class.php',
	'smartyview.compile_dir' => 'cache',
	'smartyview.template_dir' => APP_DIR . DS . 'views',
	'smartyview.left_delimiter' => '#{',
	'smartyview.right_delimiter' => '}#',
	'smartyview.extension_name' => '.tpl',

	/**
	 * 规则说明
	 * dsn.开发模式.数据库 => 配置参数    或
	 * dsn.开发模式 => array(
	 * 	'数据库' => 配置参数
	 * )
	 */ 
	'dsn.debug' => array(
	),
	'dsn.product' => array(
	) 
);

?>