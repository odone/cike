<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class UploadHelper extends CKObject {
	
	private $allowExts;
	private $denyExts;
	private $maxSize;
	
	public function __construct($allowExts = null, $denyExts = null) {
		$this->setAllowExt ( $allowExts );
		$this->setDenyExt ( $denyExts );
	}
	
	public function setAllowExt($exts) {
		if (is_array ( $exts )) {
			$this->allowExts = $exts;
		}
		return $this;
	}
	
	public function setDenyExt($exts) {
		if (is_array ( $exts )) {
			$this->denyExts = $exts;
		}
		return $this;
	}
	
	public function getAllowExt() {
		return $this->allowExts;
	}
	
	public function getDenyExt() {
		return $this->denyExts;
	}
	
	public function setMaxSize($size) {
		$this->maxSize = ( int ) $size;
		return $this;
	}
	
	public function getMaxSize() {
		return $this->maxSize;
	}
	
	public function upload($file, $dstDir) {
		if (isset ( $_FILES [$file] )) {
			$uploadFile = $_FILES [$file];
			
			// 数组方式上传
			if (is_array ( $uploadFile ['name'] )) {
				$returnVals = array ();
				foreach ( $uploadFile ['name'] as $k => $v ) {
					$upfile = array ('name' => $uploadFile ['name'] [$k], 'type' => $uploadFile ['type'] [$k], 'tmp_name' => $uploadFile ['tmp_name'] [$k], 'error' => $uploadFile ['error'] [$k], 'size' => $uploadFile ['size'] [$k] );
					$returnVals [$k] = $this->uploadFile ( $upfile, $dstDir );
				}
				return $returnVals;
			} else {
				return $this->uploadFile ( $uploadFile, $dstDir );
			}
		}
	}
	
	private function uploadFile($uploadFile, $dstDir) {
		$returnVal = array ('error' => 1, 'message' => '', 'filename' => '', 'basename' => '', 'ext' => '', 'path' => '', 'size' => 0, 'formatFileSize' => '0kb' );
		
		if ($uploadFile) {
			
			$returnVal ['size'] = $uploadFile ['size'];
			$returnVal ['name'] = $uploadFile ['name'];
			
			if (! is_uploaded_file ( $uploadFile ['tmp_name'] )) {
				$returnVal ['error'] = 1;
				$returnVal ['message'] = '临时文件可能不是上传文件';
				return $returnVal;
			}
			// 检查扩展名
			$basename = FS::getFileBasename ( $returnVal ['name'] );
			$ext = FS::getFileExt ( $returnVal ['name'] );
			$returnVal ['basename'] = $basename;
			$returnVal ['ext'] = $ext;
			
			// 阻止不允许的类型
			if (is_array ( $this->getDenyExt () ) && in_array ( $ext, $this->getDenyExt () )) {
				$returnVal ['message'] = '上传文件类型不允许';
				return $returnVal;
			}
			if (is_array ( $this->getAllowExt () ) && ! in_array ( $ext, $this->getAllowExt () )) {
				$returnVal ['message'] = '上传文件类型不允许';
				return $returnVal;
			}
			
			// 检查大小
			if ($this->getMaxSize () && $returnVal ['size'] > $this->getMaxSize ()) {
				$returnVal ['message'] = '上传的文件过大';
				return $returnVal;
			}
			
			$returnVal ['path'] = $dstDir . DS . Util::toGb2312 ( $basename ) . '.' . $ext;
			if (file_exists ( $returnVal ['path'] )) {
				$returnVal ['path'] = $dstDir . DS . Util::toGb2312 ( $basename ) . '(' . time () . ').' . $ext;
			}
			
			$returnVal ['filename'] = Util::toGb2312 ( $uploadFile ['name'] );
			
			// 上传文件夹
			if (! is_dir ( $dstDir )) {
				FS::mkdirs ( $dstDir );
			}
			
			// 上传文件
			if (move_uploaded_file ( $uploadFile ['tmp_name'], $returnVal ['path'] ) === false) {
				$returnVal ['message'] = '上传文件失败';
				return $returnVal;
			}
			
			$returnVal ['path'] = str_replace ( DS, '/', $returnVal ['path'] );
			
			$returnVal ['error'] = 0;
			$returnVal ['message'] = '文件上传成功';
			$returnVal ['formatFileSize'] = FS::formatFileSize ( $returnVal ['size'] );
			
			return $returnVal;
		} else {
			$returnVal ['message'] = '没有文件上传';
			return $returnVal;
		}
	}

}

?>