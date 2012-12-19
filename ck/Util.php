<?php

! defined ( 'IN_CIKE' ) ? null : '!';

abstract class Util extends CKObject {
	
	static public function url($controller = '', $action = '', $params = array()) {
		static $appPath = null;
		if ($appPath == null) {
			$appPath = self::getAppPath ();
		}
		
		static $controller_accessor = null;
		static $action_accessor = null;
		static $url_mode = null;
		static $path_accessor = null;
		
		if ($controller_accessor == null) {
			$controller_accessor = Cike::configure ()->get ( 'controller.accessor' );
		}
		if ($action_accessor == null) {
			$action_accessor = Cike::configure ()->get ( 'action.accessor' );
		}
		if ($path_accessor == null) {
			$path_accessor = Cike::configure ()->get ( 'mvc.path_accessor' );
		}
		if ($url_mode == null) {
			$url_mode = Cike::configure ()->get ( 'mvc.mode' );
		}
		
		$queryString = '';
		
		if ($url_mode != 'rewrite') {
			$queryString = basename ( $_SERVER ['SCRIPT_NAME'] );
		}
		
		if (is_array ( $params )) {
			if (isset ( $params [$controller_accessor] )) {
				unset ( $params [$controller_accessor] );
			}
			if (isset ( $params [$action_accessor] )) {
				unset ( $params [$action_accessor] );
			}
		}
		
		$controller = $controller == '' ? Cike::configure ()->get ( 'controller.default' ) : $controller;
		$action = $action == '' ? Cike::configure ()->get ( 'action.default' ) : $action;
		
		switch ($url_mode) {
			case 'standard' :
				$params = array_merge ( array ($controller_accessor => $controller, $action_accessor => $action ), $params );
				$queryString .= '?' . http_build_query ( $params );
				$queryString = str_replace ( '??', '?', $queryString );
				break;
			case 'mixed' :
				$queryString .= '?' . $path_accessor . '=';
			case 'rewrite' :
			case 'pathinfo' :
				$queryString .= '/' . $controller . '/' . $action;
				foreach ( $params as $k => $v ) {
					$queryString .= '/' . $k . '/' . $v;
				}
				break;
		}
		
		$url = $appPath . '/' . $queryString;
		$url = str_replace ( '//', '/', $url );
		
		return $url;
	}
	
	/**
	 * 获取http协议的主机名称
	 * @return string
	 */
	static public function getAppHostname() {
		return (isset ( $_SERVER ['HTTPS'] ) ? 'https://' : 'http://') . $_SERVER ['SERVER_NAME'];
	}
	
	/**
	 * 获取当前应用路径
	 * @return string
	 */
	static public function getAppPath() {
		return str_replace ( '\\', '/', str_replace ( $_SERVER ['DOCUMENT_ROOT'], '', dirname ( $_SERVER ['SCRIPT_FILENAME'] ) ) );
	}
	
	/**
	 * 获取当前应用文件路径
	 * @return string
	 */
	static public function getAppFilename() {
		return str_replace ( '\\', '/', str_replace ( $_SERVER ['DOCUMENT_ROOT'], '', $_SERVER ['SCRIPT_FILENAME'] ) );
	}
	
	/**
	 *
	 * @param $url string 跳转地址
	 * @param $client boolean 是否为客户端跳转
	 */
	static public function redirect($url, $delay = -1) {
		$referer = self::getAppHostname () . URL;
		header ( "Referer: $referer\n\n" );
		
		if ($delay == - 1) {
			header ( "Location: $url\n\n" );
		} else {
			$html = <<<DOCHERE
<html>
<head>
<meta http-equiv="refresh" content="$delay; url=$url" />
<script language="javascript">
function redirect(){
	setTimeout(function(){
		document.location.href = "$url";
	}, $delay * 1000);
}
window.onload = redirect;
</script>
</head>
<body onload="redirect()">
</body>
</html>
DOCHERE;
			exit ( $html );
		}
	}
	
	/**
	 * 将GB2312转换为UTF-8
	 * @param $utf8Str
	 * @return string
	 */
	static public function toUtf8($utf8Str) {
		return iconv ( 'GB2312', 'UTF-8//IGNORE', $utf8Str );
	}
	
	/**
	 * 将UTF-8转换为GB2312
	 * @param $gbStr
	 * @return string
	 */
	static public function toGb2312($gbStr) {
		return iconv ( 'UTF-8', 'GB2312//IGNORE', $gbStr );
	}
	
	/**
	 * BOM检查
	 * @param string $filename
	 * @return boolean
	 */
	static public function isBOM($filename) {
		$contents = file_get_contents ( $filename );
		$charset [1] = substr ( $contents, 0, 1 ); // EF
		$charset [2] = substr ( $contents, 1, 1 ); // BB
		$charset [3] = substr ( $contents, 2, 1 ); // BF
		if (ord ( $charset [1] ) == 239 && ord ( $charset [2] ) == 187 && ord ( $charset [3] ) == 191) {
			return true;
		}
		return false;
	}
	
	/**
	 * 检查文件编码
	 * @param string $str
	 * @return boolean
	 */
	static public function isUTF8File($filename) {
		$str = file_get_contents ( $filename );
		if ($str === mb_convert_encoding ( mb_convert_encoding ( $str, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32' )) {
			return true;
		}
		return false;
	}
	
	/**
	 * 是否为UTF8字符
	 * @param string $str
	 * @return boolean
	 */
	static public function isUTF8Str($str) {
		if ($str === mb_convert_encoding ( mb_convert_encoding ( $str, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32' )) {
			return true;
		}
		return false;
	}
	
	/**
	 * 获取随机字符串
	 * @param $len			随机数长度
	 * @param $randType		随机数类型
	 * @return string
	 */
	static public function getRandStr($len = 6, $randType = 3) {
		$str = '';
		switch ($randType) {
			case 1 :
				$str = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz';
				break;
			case 2 :
				$str = '0123456789';
				break;
			default :
				$str = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxy23456789';
		}
		
		$strlen = strlen ( $str );
		$randStr = '';
		while ( strlen ( $randStr ) < $len ) {
			$randStr .= $str [mt_rand ( 0, $strlen - 1 )];
		}
		return $randStr;
	}
	
	/**
	 * 向浏览器发送文件内容
	 *
	 * @param string $serverPath 文件在服务器上的路径（绝对或者相对路径）
	 * @param string $filename 发送给浏览器的文件名（尽可能不要使用中文）
	 * @param string $mimeType 指示文件类型
	 */
	static public function sendFile($serverPath, $filename, $charset = 'utf-8', $mimeType = 'application/octet-stream') {
		header ( "Content-Type: {$mimeType}\n\n" );
		
		$filename = '"' . htmlspecialchars ( $filename ) . '"';
		$filesize = filesize ( $serverPath );
		header ( "Content-Disposition: attachment; filename={$filename}; charset={$charset}\n\n" );
		header ( "Pragma: cache\n\n" );
		header ( "Cache-Control: public, must-revalidate, max-age=0\n\n" );
		header ( "Content-Length: {$filesize}\n\n" );
		readfile ( $serverPath );
		exit ();
	}
	
	/**
	 * 返回客户端IP地址
	 * @return 
	 */
	static public function getIp() {
		return isset($_SERVER ['REMOTE_ADDR']) ? $_SERVER ['REMOTE_ADDR'] : '127.0.0.1';
	}
	
	/**
	 * 支持中文的substr
	 * @param string $str
	 * @param int $from
	 * @param int $len
	 * @param string $fix
	 * @return string
	 */
	static public function substr($str, $from, $len, $fix = '') {
		preg_match_all ( '#(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)#s', $str, $array, PREG_PATTERN_ORDER );
		$from1 = 0;
		$len1 = 0;
		$s = '';
		foreach ( $array [0] as $key => $val ) {
			$n = ord ( $val ) >= 128 ? 2 : 1;
			$from1 += $n;
			if ($from1 > $from) {
				$len1 += $n;
				if ($len1 <= $len) {
					$s .= $val;
				} else {
					return $s . $fix;
				}
			}
		}
		return $s;
	}
	
	/**
	 * 将树形结构的数组转换成select表单
	 * @param array $categories
	 * @param int $level
	 * @param int $selected
	 * @param string $key
	 * @param string $val
	 */
	static public function getOptionList($categories, $level = 0, $selected = 0, $childrens = 'childrens', $key = 'categoryid', $val = 'category') {
		$options = '';
		foreach ( $categories as $category ) {
			if (isset ( $category [$childrens] ) && is_array ( $category [$childrens] )) {
				$options .= sprintf ( '<option hasChildren="true" value="%d" %s>%s %s</option>' . "\r\n", $category [$key], ($selected == $category [$key]) ? 'selected initSelect="true" style="color:#f00;"' : '', str_repeat ( '│ ', $level ) . '│ └', $category [$val] );
				$level ++;
				$options .= self::getOptionList ( $category [$childrens], $level, $selected, $childrens, $key, $val );
				$level --;
			} else {
				$options .= sprintf ( '<option value="%d" %s>%s %s</option>' . "\r\n", $category [$key], ($selected == $category [$key]) ? 'selected initSelect="true" style="color:#f00;"' : '', str_repeat ( '│ ', $level ) . '│ ├', $category [$val] );
			}
		}
		return $options;
	}
	
	/**
	 * 替换<>html标记
	 * @param $str
	 */
	static public function t($str) {
		if (empty ( $str ))
			return '';
		$str = str_replace ( '<', '&lt;', $str );
		$str = str_replace ( '>', '&gt;', $str );
		return $str;
	}
	
	/**
	 * 
	 * 转换特殊的HTML字符为实体字符
	 * @param string $str
	 */
	static public function h($str) {
		if (empty ( $str ))
			return '';
		return htmlentities ( $str );
	}
	
	/**
	 * 是否为中文
	 * @param string $str
	 */
	static public function isChinese($str) {
		return preg_match ( '/([\x80-\xFE][\x40-\x7E\x80-\xFE])+/', $str );
	}
	
	static public function hexColorToArray($hexColor) {
		$l = strlen ( $hexColor );
		if ($l == 3) {
			$tmp = '';
			for($i = 0; $i < $l; $i ++) {
				$tmp .= str_repeat ( substr ( $hexColor, $i, 1 ), 2 );
			}
			$hexColor = $tmp;
		}
		
		$arr = array ();
		
		for($i = 0; $i < 3; $i ++) {
			$arr [$i] = hexdec ( substr ( $hexColor, $i * 2, 2 ) );
		}
		
		return $arr;
	}
	
	static public function loadYaml($file) {
		return Cike::singleton ( 'Spyc' )->load ( $file );
	}
	
	static public function pinyin($chs, $delimiter = '') {
		//		return VANE::helper ( 'PinYin' )->getFullSpell ( toGb2312 ( $chs ), $delimiter );
	}
	
	/**
	 * 移除空的数组项
	 *
	 * @param array $arr
	 * @param bool $trim
	 * @return array
	 */
	static public function arrayRemoveEmpty($arr, $trim = true) {
		foreach ( $arr as $k => $v ) {
			if ($trim) {
				$v = trim ( $v );
			}
			if ($v == '' || $v == null) {
				unset ( $arr [$k] );
			}
		}
		return $arr;
	}
	
	/**
	 * 从数组中删除空白的元素（包括只有空白字符的元素）
	 *
	 * @param array $arr
	 * @param boolean $trim
	 */
	static public function array_remove_empty(& $arr, $trim = true) {
		foreach ( $arr as $key => $value ) {
			if (is_array ( $value )) {
				self::array_remove_empty ( $arr [$key] );
			} else {
				$value = trim ( $value );
				if ($value == '') {
					unset ( $arr [$key] );
				} elseif ($trim) {
					$arr [$key] = $value;
				}
			}
		}
	}
	
	/**
	 * 从一个二维数组中返回指定键的所有值
	 *
	 * @param array $arr
	 * @param string $col
	 *
	 * @return array
	 */
	static public function array_col_values(& $arr, $col) {
		$ret = array ();
		foreach ( $arr as $row ) {
			if (isset ( $row [$col] )) {
				$ret [] = $row [$col];
			}
		}
		return $ret;
	}
	
	/**
	 * 将一个二维数组转换为 hashmap
	 *
	 * 如果省略 $valueField 参数，则转换结果每一项为包含该项所有数据的数组。
	 *
	 * @param array $arr
	 * @param string $keyField
	 * @param string $valueField
	 *
	 * @return array
	 */
	static public function array_to_hashmap(& $arr, $keyField, $valueField = null) {
		$ret = array ();
		if ($valueField) {
			foreach ( $arr as $row ) {
				$ret [$row [$keyField]] = $row [$valueField];
			}
		} else {
			foreach ( $arr as $row ) {
				$ret [$row [$keyField]] = $row;
			}
		}
		return $ret;
	}
	
	/**
	 * 将一个二维数组按照指定字段的值分组
	 *
	 * @param array $arr
	 * @param string $keyField
	 *
	 * @return array
	 */
	static public function array_group_by(& $arr, $keyField) {
		$ret = array ();
		foreach ( $arr as $row ) {
			$key = $row [$keyField];
			$ret [$key] [] = $row;
		}
		return $ret;
	}
	
	/**
	 * 将一个平面的二维数组按照指定的字段转换为树状结构
	 *
	 * 当 $returnReferences 参数为 true 时，返回结果的 tree 字段为树，refs 字段则为节点引用。
	 * 利用返回的节点引用，可以很方便的获取包含以任意节点为根的子树。
	 *
	 * @param array $arr 原始数据
	 * @param string $fid 节点ID字段名
	 * @param string $fparent 节点父ID字段名
	 * @param string $fchildrens 保存子节点的字段名
	 * @param boolean $returnReferences 是否在返回结果中包含节点引用
	 *
	 * return array
	 */
	static public function array_to_tree($arr, $fid, $fparent = 'parentid', $fchildrens = 'children', $returnReferences = false) {
		$pkvRefs = array ();
		foreach ( $arr as $offset => $row ) {
			$pkvRefs [$row [$fid]] = & $arr [$offset];
		}
		
		$tree = array ();
		foreach ( $arr as $offset => $row ) {
			$parentId = $row [$fparent];
			if ($parentId) {
				if (! isset ( $pkvRefs [$parentId] )) {
					continue;
				}
				$parent = & $pkvRefs [$parentId];
				$parent [$fchildrens] [] = & $arr [$offset];
			} else {
				$tree [] = & $arr [$offset];
			}
		}
		if ($returnReferences) {
			return array ('tree' => $tree, 'refs' => $pkvRefs );
		} else {
			return $tree;
		}
	}
	
	/**
	 * 将树转换为平面的数组
	 *
	 * @param array $node
	 * @param string $fchildrens
	 *
	 * @return array
	 */
	static public function tree_to_array(& $node, $fchildrens = 'childrens') {
		$ret = array ();
		if (isset ( $node [$fchildrens] ) && is_array ( $node [$fchildrens] )) {
			foreach ( $node [$fchildrens] as $child ) {
				$ret = array_merge ( $ret, self::tree_to_array ( $child, $fchildrens ) );
			}
			unset ( $node [$fchildrens] );
			$ret [] = $node;
		} else {
			$ret [] = $node;
		}
		return $ret;
	}
	
	/**
	 * 根据指定的键值对数组排序
	 *
	 * @param array $array 要排序的数组
	 * @param string $keyname 键值名称
	 * @param int $sortDirection 排序方向
	 *
	 * @return array
	 */
	static public function array_column_sort($array, $keyname, $sortDirection = SORT_ASC) {
		return self::array_sortby_multifields ( $array, array ($keyname => $sortDirection ) );
	}
	
	/**
	 * 将一个二维数组按照指定列进行排序，类似 SQL 语句中的 ORDER BY
	 *
	 * @param array $rowset
	 * @param array $args
	 */
	static public function array_sortby_multifields($rowset, $args) {
		$sortArray = array ();
		$sortRule = '';
		foreach ( $args as $sortField => $sortDir ) {
			foreach ( $rowset as $offset => $row ) {
				$sortArray [$sortField] [$offset] = $row [$sortField];
			}
			$sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
		}
		if (empty ( $sortArray ) || empty ( $sortRule )) {
			return $rowset;
		}
		eval ( 'array_multisort(' . $sortRule . '$rowset);' );
		return $rowset;
	}
	
	/**
	 * 检测数组是否为空数组
	 * @param array $arr
	 * @return bool
	 */
	static public function isEmptyArray($arr) {
		if (empty ( $arr ))
			return true;
		foreach ( $arr as $k => $ar ) {
			if (is_array ( $ar )) {
				return self::isEmptyArray ( $ar );
			} else {
				if ($ar === 0)
					return false;
				else
					return $ar == '';
			}
		}
	}
	
	/**
	 * 在数组的指定位置前插入元素
	 * @param array $arrS
	 * @param mixed $arrD
	 * @param int $pos
	 * @return array
	 */
	static public function array_insert_before($arr, $val, $pos) {
		if ($pos == count ( $arr )) {
			array_push ( $arr, $val );
			return $arr;
		}
		if ($pos == 0) {
			$newArr = array ($val );
			$newArr = array_merge ( $newArr, $arr );
		} else {
			$newArr = array ();
			$arrstart = array_slice ( $arr, 0, $pos );
			$arrend = array_slice ( $arr, $pos );
			$newArr = array_merge ( $arrstart, array ($val ), $arrend );
		}
		return $newArr;
	}
	
	/**
	 * 在数组的指定位置后插入元素
	 * @param array $arrS
	 * @param mixed $arrD
	 * @param int $pos
	 * @return array
	 */
	static public function array_insert_after($arr, $val, $pos) {
		if ($pos == count ( $arr )) {
			array_push ( $arr, $val );
			return $arr;
		}
		$newArr = array ();
		$arrstart = array_slice ( $arr, 0, $pos + 1 );
		$arrend = array_slice ( $arr, $pos + 1 );
		$newArr = array_merge ( $arrstart, array ($val ), $arrend );
		
		return $newArr;
	}
	
	/**
	 * 
	 * 获取数组的第一个元素(通用)
	 * @param array $arr	数组
	 */
	static public function array_get_first($arr) {
		if (! is_array ( $arr ))
			return null;
		reset ( $arr );
		return current ( $arr );
	}
	
	static public function array_rename_key($arr) {
		$pkvRefs = array ();
		foreach ( $arr as $offset => $row ) {
			$pkvRefs [self::array_get_first ( $row )] = $arr [$offset];
		}
		return $pkvRefs;
	}
	
	/**
	 * 
	 * 转换PHP数组为JS数组
	 * @param array $arr	php数组
	 * @param string $name   数组名称
	 */
	static public function js_item($arr, $name) {
		$script = '';
		if (is_array ( $arr )) {
			$script = "{$name} = new Array();\n";
		}
		
		foreach ( $arr as $i => $data ) {
			if (is_array ( $arr [$i] )) {
				$script .= self::js_item ( $arr [$i], "{$name}[{$i}]" );
			} else {
				$script .= "{$name}[{$i}] = \"{$arr[$i]}\";\n";
			}
		}
		return $script;
	}
	
	/**
	 * 
	 * 转换PHP数组为JS数组
	 * @param array $arr	php数组
	 * @param string $name   数组名称
	 */
	static public function js_array($arr, $name) {
		$script = "var {$name};\n";
		$script .= self::js_item ( $arr, $name );
		return $script;
	}
	
	/**
	 * 将树形结构的数组转换成select表单
	 * @param array $categories
	 * @param int $level
	 * @param int $selected
	 * @param string $key
	 * @param string $val
	 */
	static public function tree_to_options($categories, $level = 0, $selected = 0, $childrens = 'childrens', $key = 'categoryid', $val = 'category') {
		$options = '';
		foreach ( $categories as $category ) {
			if (isset ( $category [$childrens] ) && is_array ( $category [$childrens] )) {
				$options .= sprintf ( '<option hasChildren="true" value="%d" %s>%s %s</option>' . "\r\n", $category [$key], ($selected == $category [$key]) ? 'selected initSelect="true" style="color:#f00;"' : '', str_repeat ( '&nbsp;&nbsp;&nbsp;', $level ) . '└', $category [$val] );
				$level ++;
				$options .= self::tree_to_options ( $category [$childrens], $level, $selected, $childrens, $key, $val );
				$level --;
			} else {
				$options .= sprintf ( '<option value="%d" %s>%s %s</option>' . "\r\n", $category [$key], ($selected == $category [$key]) ? 'selected initSelect="true" style="color:#f00;"' : '', str_repeat ( '&nbsp;&nbsp;', $level ) . '├', $category [$val] );
			}
		}
		return $options;
	}
	
	static public function now($format = 'Y-m-d H:i:s') {
		return date ( $format );
	}
	
	static public function getMicrotime() {
		list ( $usec, $sec ) = explode ( " ", microtime () );
		return (( float ) $usec + ( float ) $sec);
	}
	
	static public function getProcessTime() {
		return number_format ( (self::getMicrotime () - $_SERVER ['REQUEST_TIME']), 4 );
	}
	
	/**
	 * 计算参数时间与当前时间差，精确的秒，最大单位天
	 * @param int $times
	 * @return string
	 */
	static public function puttime($times) {
		if ($times == '' || $times == 0)
			return false;
		$dtime = is_int ( $times ) ? $times : strtotime ( $times );
		$ptime = time () - $dtime;
		if ($ptime < 60) {
			$pct = sprintf ( "%d秒前", $ptime );
		} elseif ($ptime > 60 && $ptime < 3600) {
			$pct = sprintf ( "%d分钟前", ceil ( $ptime / 60 ) );
		} elseif ($ptime > 3600 && $ptime < (3600 * 24)) {
			$pct = sprintf ( "%d小时%d分钟前", floor ( $ptime / 3600 ), ceil ( ($ptime % 3600) / 60 ) );
		} elseif ($ptime > (3600 * 24) && $ptime < (3600 * 24 * 30)) {
			$d = $ptime / (3600 * 24);
			$h = ($ptime % (3600 * 24)) / 3600;
			$m = ceil ( (($ptime % (3600 * 24)) % 3600) / 60 );
			$pct = sprintf ( "%d天%d小时%d分钟前", $d, $h, $m );
		} else {
			$mt = $ptime / (3600 * 24 * 30);
			$d = ($ptime % (3600 * 24 * 30)) / (3600 * 24);
			$h = (($ptime % (3600 * 24 * 30)) % (3600 * 24)) / 3600;
			$m = ceil ( (($ptime % (3600 * 24 * 30)) % (3600 * 24)) % 3600 / 60 );
			$pct = sprintf ( "%d月%d天%d小时%d分钟前", $mt, $d, $h, $m );
		}
		return $pct;
	}
	
	/**
	 * 计算参数时间与当前时间差(第二种）
	 * @param string $date
	 * @param string $isShowDate
	 */
	static public function puttime2($date, $isShowDate = true) {
		$limit = time () - $date;
		if ($limit < 60) {
			return $limit . '秒钟之前';
		} elseif ($limit >= 60 && $limit < 3600) {
			return floor ( $limit / 60 ) . '分钟之前';
		} elseif ($limit >= 3600 && $limit < 86400) {
			return floor ( $limit / 3600 ) . '小时之前';
		} elseif ($limit >= 86400 and $limit < 259200) {
			return floor ( $limit / 86400 ) . '天之前';
		} elseif ($limit >= 259200 and $isShowDate) {
			return date ( 'Y-m-d H:i:s', $date );
		} else {
			return '';
		}
	}
	
	static public function isXSS() {
  		//urldecode解码已编码的URL 字符串
  		$temp = strtoupper(urldecode(urldecode(URL)));
 		
  		if(strpos($temp, '<') !== false || strpos($temp, '"') !== false || strpos($temp, 'CONTENT-TRANSFER-ENCODING') !== false) {
    		die('XSS攻击');
		}
		
		if ( isset($_SERVER['HTTP_REFERER']) ){
			$tmp = parse_url($_SERVER['HTTP_REFERER']);
			if ( $tmp['host'] != $_SERVER['HTTP_HOST'] ) {
				die( '禁止从外部提交表单');
			}
		}
		
  		return true;
	}
}

?>