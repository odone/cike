<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.view.*' );

class SmartyView extends CKObject implements IView {
	
	private $viewDir;
	private $template;
	private $viewdata;
	
	private $smarty;
	
	public function __construct($controller, $action, $viewdata) {
		$this->template = $controller . '_' . $action;
		if ($action == Cike::configure ()->get ( 'action.default' )) {
			$this->template = $controller;
		}
		$extName = Cike::configure ()->get ( 'smartyview.extension_name' );
		if (! $extName) {
			$extName = '.tpl';
		}
		$this->template .= $extName;
		
		$smartyLibPath = Cike::configure ()->get ( 'smartyview.class_path' );
		if ($smartyLibPath != '') {
			require ($smartyLibPath);
			$this->smarty = new Smarty ();
		}
		$compileDir = Cike::configure ()->get ( 'smartyview.compile_dir' );
		if (! file_exists ( $compileDir )) {
			Cike::throwException ( 'DirNotExistsException', 'smartyview.compile_dir ' . $compileDir, true );
		}
		$this->smarty->compile_dir = $compileDir;
		
		$this->viewDir = Cike::configure ()->get ( 'smartyview.template_dir' );
		if (! file_exists ( $this->viewDir )) {
			Cike::throwException ( 'DirNotExistsException', 'smartyview.template_dir ' . $this->viewDir, true );
		}
		$this->smarty->template_dir = $this->viewDir;
		
		$leftDelimiter = Cike::configure ()->get ( 'smartyview.left_delimiter' );
		$rightDelimiter = Cike::configure ()->get ( 'smartyview.right_delimiter' );
		if ($leftDelimiter) {
			$this->smarty->left_delimiter = $leftDelimiter;
		}
		if ($rightDelimiter) {
			$this->smarty->right_delimiter = $rightDelimiter;
		}
		
		$this->viewdata = $viewdata;
		
		$GLOBALS ['_viewer_'] = $this->smarty;
		$this->registFunctions ();
		
		$tagLibs = Cike::configure ()->get ( 'smartyview.taglib' );
		if($tagLibs){
			foreach ( $tagLibs as $tag => $tplfile ) {
				$tagTplFile = $this->smarty->template_dir . DS . $tplfile;
				if (! file_exists ( $tagTplFile )) {
					Cike::throwException ( 'TemplateNotExistsException', $tagTplFile );
				} else {
					$this->registTagControl ( $tag, $tplfile );
				}
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::display()
	 */
	public function display() {
		$this->smarty
			->assign ( $this->viewdata );
		if (! file_exists ( $this->viewDir . DS . $this->template )) {
			Cike::throwException ( 'TemplateNotExistsException', $this->template, true );
		}
		$this->smarty
			->display ( $this->template );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::assign()
	 */
	public function assign($var) {
		$this->viewdata = array_merge ( $this->viewdata, $var );
	}
	
	private function registFunctions() {
		$this->smarty
			->register_function ( 'url', array (& $this, 'fun_url' ) );
		$this->smarty
			->register_function ( 'md5', array (& $this, 'fun_md5' ) );
		$this->smarty
			->register_function ( 'json', array (& $this, 'fun_tojson' ) );
		// modify
		$this->smarty
			->register_modifier ( '_truncate', array (& $this, 'mod_truncate' ) );
		$this->smarty
			->register_modifier ( 'tojson', array (& $this, 'mod_tojson' ) );
	
	}
	
	public function fun_url($params) {
		$controllerName = isset ( $params ['C'] ) ? $params ['C'] : null;
		unset ( $params ['C'] );
		$actionName = isset ( $params ['A'] ) ? $params ['A'] : null;
		unset ( $params ['A'] );
		
		$args = array ();
		foreach ( $params as $key => $value ) {
			if (is_array ( $value )) {
				$args = array_merge ( $args, $value );
				unset ( $params [$key] );
			}
		}
		$args = array_merge ( $args, $params );
		return Util::url ( $controllerName, $actionName, $args );
	}
	
	public function fun_md5($params) {
		$val = isset ( $params ['var'] ) ? $params ['var'] : null;
		return md5 ( $val );
	}
	
	public function fun_tojson($params) {
		$js = '';
		foreach ( $params as $k => $v ) {
			$js .= "var $k = " . json_encode ( $v ) . ";\r\n";
		}
		
		return $js;
	}
	
	public function registTagControl($name, $template) {
		$funName = 'fun_control_' . $name;
		$funCode = 'function ' . $funName . '
(){  
	$GLOBALS[\'_viewer_\']->_smarty_include(
		array
		(
			\'smarty_include_tpl_file\' => \'' . $template . '\', 
			\'smarty_include_vars\' => array()
		)
	);  
};';
		
		$this->smarty
			->register_function ( $name, $funName );
		eval ( $funCode );
	}
	
	public function mod_truncate($string, $len) {
		return Util::substr ( $string, 0, $len );
	}
	
	public function mod_tojson($string) {
		return json_encode ( $string );
	}

}

?>