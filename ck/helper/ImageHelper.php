<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CImageHelper extends CKObject {
	
	public $width = 0;
	public $height = 0;
	public $font = '';
	
	private $type = null;
	private $img = null;
	
	public function __construct() {
		$this->font = 'arial.ttf';
		if (PHP_OS == 'WINNT') {
			$this->font = 'c:/windows/fonts/msyh.ttf';
		}
		
		@ini_set ( 'memory_limit', '128M' );
	}
	
	/**
	 * 从文件路径创建图像
	 * @param string $file	文件路径
	 */
	function createFromFile($file) {
		list ( $this->width, $this->height, $this->type ) = GetImageSize ( $file );
		
		switch ($this->type) {
			case IMAGETYPE_JPEG :
				$this->img = ImageCreateFromJpeg ( $file );
				break;
			case IMAGETYPE_GIF :
				$this->img = ImageCreateFromGif ( $file );
				break;
			case IMAGETYPE_BMP :
				$this->img = ImageCreateFromWbmp ( $file );
				break;
			case IMAGETYPE_PNG :
				$this->img = ImageCreateFromPng ( $file );
				break;
		}
		
		if (! $this->img) {
			trigger_error ( '创建图像失败' );
		}
		
		return $this;
	}
	
	function getImg() {
		return $this->img;
	}
	
	/**
	 * 检查图片尺寸
	 * @param type $width   允许最大宽度
	 * @param type $height  允许最大高度
	 * @return type 
	 */
	function checkDimensions($width = null, $height = null) {
		if ($width == null && $height == null) {
			return true;
		} elseif ($width != null && $height == null) {
			return ($this->width <= $width);
		} elseif ($height != null && $width == null) {
			return ($this->height <= $height);
		} else {
			return ($this->width <= $width) && ($this->height <= $height);
		}
	}
	
	/**
	 * 生成缩略图(可以单指定高或宽，程序自动等比例缩放)
	 * @param string $savePath	保存路径
	 * @param int $width		宽度
	 * @param int $height		高度
	 * @param boolean $output	是否输出
	 */
	function makeThumb($width = null, $height = null) {
		if ($width == null && $height != null) {
			$width = $this->width / $this->height * $height;
		}
		if ($width != null && $height == null) {
			$height = $this->height / $this->width * $width;
		}
		
		$imgD = ImageCreateTrueColor ( $width, $height );
		ImageCopyResampled ( $imgD, $this->img, 0, 0, 0, 0, $width, $height, $this->width, $this->height );
		
		// 输出图像
		$this->img = $imgD;
		
		$this->width = $width;
		$this->height = $height;
		
		return $this;
	}
	
	/**
	 * 按百分比生成缩略图
	 * @param string $savePath 	保存路径
	 * @param int $percent		比例
	 */
	function makeThumbByPercent($percent = .5) {
		$this->makeThumb ( $this->width * $percent, null );
		return $this;
	}
	
	/**
	 * 文字水印
	 * @var string $text 文字
	 * @var string $color 颜色
	 * @var int $fontSize 字体大小
	 * @var int $pos 位置
	 */
	function makeTextWater($text, $color = 'ff0000', $fontSize = 12, $pos = 1) {
		$len = strlen ( $text );
		$c = Util::hexColorToArray ( $color );
		$text_c = ImageColorAllocate ( $this->img, $c [0], $c [1], $c [2] );
		
		switch ($pos) {
			case 1 :
				$top = 5 + $fontSize;
				$left = 5;
				break;
			case 2 :
				$top = 5 + $fontSize;
				$left = $this->width / 2 - $len / 2 * $fontSize;
				break;
			case 3 :
				$top = 5 + $fontSize;
				$left = $this->width - $len * $fontSize - 5;
				break;
			case 4 :
				$top = $this->height / 2;
				$left = 5;
				break;
			case 5 :
				$top = $this->height / 2;
				$left = $this->width / 2 - $len / 2 * $fontSize;
				break;
			case 6 :
				$top = $this->height / 2;
				$left = $this->width - $len * $fontSize - 5;
				break;
			case 7 :
				$top = $this->height - $fontSize / 2;
				$left = 5;
				break;
			case 8 :
				$top = $this->height - $fontSize / 2;
				$left = $this->width / 2 - $len / 2 * $fontSize;
				break;
			case 9 :
				$top = $this->height - $fontSize / 2;
				$left = $this->width - $len * $fontSize - 5;
				break;
		}
		
		for($i = 0; $i < $len; $i ++) {
			$tmp = substr ( $text, $i, 1 );
			$array = array (- 1, 1 );
			$p = array_rand ( $array );
			$size = $fontSize;
			$x = $left + ceil ( 5 + $i * $size );
			$y = $top;
			imagettftext ( $this->img, $size, 0, $x, $y, $text_c, $this->font, $tmp );
		}
		
		return $this;
	}
	
	function makeImageWater($file, $pos = 1) {
		$m = Cike::helper ( 'Image' );
		$imgWater = $m->createFromFile ( 'i.jpg' )
			->getImg ();
		
		switch ($pos) {
			case 1 :
				$top = 5;
				$left = 5;
				break;
			case 2 :
				$top = 5;
				$left = $this->width / 2;
				break;
			case 3 :
				$top = 5;
				$left = $this->width - 10 - $m->width;
				break;
			case 4 :
				$top = $this->height / 2;
				$left = 5;
				break;
			case 5 :
				$top = $this->height / 2;
				$left = $this->width / 2;
				break;
			case 6 :
				$top = $this->height / 2;
				$left = $this->width - 10 - $m->width;
				break;
			case 7 :
				$top = $this->height - 10 - $m->height;
				$left = 5;
				break;
			case 8 :
				$top = $this->height - 10 - $m->height;
				$left = $this->width / 2;
				break;
			case 9 :
				$top = $this->height - 10 - $m->height;
				$left = $this->width - 10 - $m->width;
				break;
		}
		
		ImageCopyResampled ( $this->img, $imgWater, $left, $top, 0, 0, $m->width, $m->height, $m->width, $m->height );
		
		return $this;
	}
	
	function rotate($ang) {
		$this->img = imageRotate ( $this->img, $ang, 0 );
		
		return $this;
	}
	
	/**
	 * 输出图像
	 * @param $savePath	保存路径
	 */
	function output($savePath = null) {
		if ($savePath == null) {
			header ( 'Content-type: ' . image_type_to_mime_type ( $this->type ) . "\n\n" );
		}
		
		switch ($this->type) {
			case IMAGETYPE_JPEG :
				ImageJpeg ( $this->img, $savePath, 100 );
				break;
			case IMAGETYPE_GIF :
				ImageGif ( $this->img, $savePath );
				break;
			case IMAGETYPE_BMP :
				ImageWBmp ( $this->img, $savePath );
				break;
			case IMAGETYPE_PNG :
				ImagePng ( $this->img, $savePath );
				break;
		}
	}

}

?>