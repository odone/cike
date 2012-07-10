<?php

! defined ( 'IN_CIKE' ) ? null : '!';

abstract class FS extends CKObject {
	
	/**
	 * 创建一个目录树
	 *
	 * 用法：
	 * mkdirs('/top/second/3rd');
	 *
	 * @param string $dir
	 * @param int $mode
	 */
	static public function mkdirs($dir, $mode = 0777) {
		if (! is_dir ( $dir )) {
			self::mkdirs ( dirname ( $dir ), $mode );
			mkdir ( $dir );
			chmod($dir, $mode);
			return true;
		}
		return true;
	}
	
	/**
	 * 删除指定目录及其下的所有文件和子目录
	 *
	 * 用法：
	 * // 删除 my_dir 目录及其下的所有文件和子目录
	 * rmdirs('/path/to/my_dir');
	 *
	 * 注意：使用该函数要非常非常小心，避免意外删除重要文件。
	 *
	 * @param string $dir
	 */
	static public function rmdirs($dir) {
		$dir = realpath ( $dir );
		if ($dir == '' || $dir == '/' || $dir == '\\' || (strlen ( $dir ) == 3 && substr ( $dir, 1 ) == ':\\')) {
			// 禁止删除根目录
			return false;
		}
		
		// 遍历目录，删除所有文件和子目录
		if (false !== ($dh = opendir ( $dir ))) {
			while ( false !== ($file = readdir ( $dh )) ) {
				if ($file == '.' || $file == '..') {
					continue;
				}
				$path = $dir . DIRECTORY_SEPARATOR . $file;
				if (is_dir ( $path )) {
					if (! self::rmdirs ( $path )) {
						return false;
					}
				} else {
					unlink ( $path );
				}
			}
			closedir ( $dh );
			rmdir ( $dir );
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 一次性完成打开文件，写入内容，关闭文件三项工作，并且确保写入时不会造成并发冲突
	 * @param string $filename
	 * @param string $content
	 * @param int $flag
	 * @return boolean
	 */
	static public function safe_file_put_contents($filename, $content) {
		if (SAE_MODE) {
			$storage = Cike::singleton ( 'SaeStorage' );
			$storage->write ( Cike::configure ()->get ( 'sae.storage.domain' ), $filename, $content );
		} else {
			$fp = fopen ( $filename, 'wb' );
			if ($fp) {
				flock ( $fp, LOCK_EX );
				fwrite ( $fp, $content );
				flock ( $fp, LOCK_UN );
				fclose ( $fp );
				return true;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * 用共享锁模式打开文件并读取内容，可以避免在并发写入造成的读取不完整问题
	 * @param string $filename
	 * @return mixed
	 */
	static public function safe_file_get_contents($filename) {
		if (SAE_MODE) {
			$storage = Cike::singleton ( 'SaeStorage' );
			return $storage->read ( Cike::configure ()->get ( 'sae.storage.domain' ), $filename );
		} else {
			if (file_exists ( $filename )) {
				$fp = fopen ( $filename, 'rb' );
				if ($fp) {
					flock ( $fp, LOCK_SH );
					clearstatcache ();
					$filesize = filesize ( $filename );
					if ($filesize > 0) {
						$data = fread ( $fp, $filesize );
					} else {
						$data = false;
					}
					flock ( $fp, LOCK_UN );
					fclose ( $fp );
					return $data;
				}
			}
			return false;
		}
	}
	
	/**
	 * 获取文件扩展名
	 * @param $filename
	 * @return
	 */
	static public function getFileExt($filename) {
		return strrchr ( $filename, '.' );
	}
	
	static public function getFileBasename($filename) {
		return str_replace ( self::getFileExt ( $filename ), '', $filename );
	}
	
	/**
	 * 移动文件
	 * @param string $source
	 * @param string $desc
	 * @return bool
	 */
	static public function moveFile($source, $desc) {
		if (@copy ( $source, $desc )) {
			@unlink ( $source );
			return true;
		}
		return false;
	}
	
	static public function getFileSize($filename) {
		$filesize = - 1;
		if (file_exists ( $filename )) {
			$filesize = filesize ( $filename );
		}
		return self::formatFileSize ( $filesize, true );
	}
	
	/**
	 * 格式化文件大小为可识别的名称
	 * @param string $filesize
	 * @param boolean $truncate
	 * @return string
	 */
	static public function formatFileSize($filesize, $truncate = false) {
		if ($filesize < 1024) {
			if ($truncate) {
				if (strpos ( $filesize, '.' )) {
					$filesize = substr ( $filesize, 0, strpos ( $filesize, '.' ) + 2 );
				}
			}
			return $filesize . '字节';
		} elseif ($filesize < 1024 * 1024) {
			$filesize = $filesize / 1024;
			if ($truncate) {
				if (strpos ( $filesize, '.' )) {
					$filesize = substr ( $filesize, 0, strpos ( $filesize, '.' ) + 2 );
				}
			}
			return $filesize . 'KB';
		} else {
			$filesize = $filesize / 1024 / 1024;
			if ($truncate) {
				if (strpos ( $filesize, '.' )) {
					$filesize = substr ( $filesize, 0, strpos ( $filesize, '.' ) + 2 );
				}
			}
			return $filesize . 'MB';
		}
	}
	
	static public function reverseFormatFileSize($formatFilesize) {
		if (strpos ( $formatFilesize, '字节' ) === false) {
			if (strpos ( $formatFilesize, 'KB' ) === false) {
				if (strpos ( $formatFilesize, 'MB' ) === false) {
				
				} else {
					return (str_replace ( 'MB', '', $formatFilesize )) * 1024 * 1024;
				}
			} else {
				return (str_replace ( 'KB', '', $formatFilesize )) * 1024;
			}
		} else {
			return str_replace ( '字节', '', $formatFilesize );
		}
	}
	
	/**
	 * 
	 * 获取安全的路径
	 * @param $dirname
	 */
	static public function getSafeDirname($dirname) {
		$dirname = preg_replace ( '/\\{2,}/', '/', $dirname );
		$dirname = preg_replace ( '/\/{2,}/', '/', $dirname );
		$dirname = str_replace ( '\\', '/', $dirname );
		return str_replace ( '..', '', $dirname );
	}
	
	/**
	 * 
	 * 读取文件夹文件列表
	 * @param $folder
	 */
	static public function readDirFiles($folder, $getFilesize = true, $allowExts = null, $notAllowExts = null) {
		$files = array ();
		$list = array ();
		if (file_exists ( $folder )) {
			$dir = opendir ( $folder );
			if ($dir) {
				while ( ($file = readdir ( $dir )) != null ) {
					if ($file == '..' || $file == '.')
						continue;
					$ext = self::getFileExt ( $file );
					
					if ($notAllowExts != null || $allowExts != null) {
						if (is_dir ( $folder . DS . $file )) {
						
						} else {
							if ($allowExts != null && is_array ( $allowExts ) && in_array ( $ext, $allowExts )) {
							
							} else {
								$file = null;
							}
							
							if ($notAllowExts != null && is_array ( $notAllowExts ) && in_array ( $ext, $notAllowExts )) {
								$file = null;
							}
						}
					} else {
					
					}
					
					if ($file) {
						$files [] = $file;
					}
				}
				
				if (! empty ( $files )) {
					foreach ( $files as $f ) {
						$addRow = array ('filename' => $f, 'ext' => 'dir', 'fileordir' => is_dir ( $folder . DS . $f ) ? 'dir' : 'file', 'filesize' => '0' );
						if ($getFilesize && $addRow ['fileordir'] == 'file') {
							$addRow ['ext'] = self::getFileExt ( $f );
							$addRow ['filesize'] = self::getFileSize ( $folder . DS . $f );
						}
						
						$addRow ['filename'] = Util::toUtf8 ( $addRow ['filename'] );
						$list [] = $addRow;
					}
					$list = Util::array_group_by ( $list, 'ext' );
				}
			}
		}
		return $list;
	}

}

?>