<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CKGenerator extends CKObject {
	
	private $mkdirs;
	
	public function __construct() {
		
	}
	
	public function generateApp() {
		$this->mkdirs = array(
			'app' => APP_DIR,
			'controller' => APP_DIR . DS . Cike::configure()->get('controller.dir'),
			'configure' => APP_DIR . DS . 'configures',
			'model' => APP_DIR . DS . 'models',
			'view' => APP_DIR . DS . 'views',
			'cache' => APP_DIR . DS . 'caches',
		);
		
		foreach($this->mkdirs as $name => $dir){
			FS::mkdirs($dir, '0777');
		}
		
		$this->generateDoc();
	}
	
	public function generateDoc(){
		// 默认控制器
		FS::safe_file_put_contents($this->mkdirs['controller'].DS.'BaseController.php',
"<?php

Cike::import('$.models.*');

class BaseController extends CKController {

}

?>");
		FS::safe_file_put_contents($this->mkdirs['controller'].DS.'HomeController.php', 
"<?php

Cike::import('$.controllers.*');

class HomeController extends BaseController {
	function indexAction(){
		\$this->viewdata['hello'] = 'Hello,World! O(∩_∩)O'; 
		return \$this;
	}
}

?>");
		// 默认配置文件
		FS::safe_file_put_contents($this->mkdirs['configure'].DS.'acl(yaml).php', 
"#<?php exit(); ?>
home:
    allow: RBAC_ALL
");
		// 默认配置文件
		FS::safe_file_put_contents($this->mkdirs['configure'].DS.'config(yaml).php', 
"#<?php exit(); ?>
rbac:
    acl: \$.configures.acl(yaml)
");
		
		// 默认模型文件
		FS::safe_file_put_contents($this->mkdirs['model'].DS.'BaseModel.php', 
"<?php

class BaseModel extends CKModel {
}

?>");
		// 默认视图
		FS::safe_file_put_contents($this->mkdirs['view'].DS.'home.php', 
"<html>
	<head>
		<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
	</head>
	<body>
		<h1><?php echo \$hello ?></h1>
		<div>当前时间：<?php echo \$NOW ?></div>
		</p>
		<small>powered by <a href=\"http://ccike.com\" target=\"_blank\" style=\"color:#0000ff;\">ccike.com</a> . processed <?php echo Util::getProcessTime() ?> (s)</small>
	<body>
</html>
");
	}
}