<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class PagerHelper extends CKObject {
	
	private $model;
	
	public function __construct($model) {
		$this->model = Cike::singleton ( $model );
	}
	
	public function paging($fields = null, $conditions = '1=1', $orderby = null, $page = 1, $pagesize = 20) {
		$page = $page < 1 ? 1 : $page;
		
		$count = $this->model
			->findCount ( $conditions );
		$pages = ceil ( $count / $pagesize );
		$pages = $pages < 1 ? 1 : $pages;
		$page = $page > $pages ? $pages : $page;
		
		$start = ($page - 1) * $pagesize;
		$limit = $start . ',' . $pagesize;
		
		$rows = $this->model
			->findAll ( $fields, $conditions, $orderby, $limit );
		
		$data = array ('rows' => $rows, 'pages' => $pages, 'page' => $page, 'pagesize' => $pagesize, 'first' => 1, 'last' => $pages, 'prev' => $page > 1 ? $page - 1 : '', 'next' => $page < $pages - 1 ? $page + 1 : '' );
		
		$cross = array ();
		
		$minPages = 10;
		if ($pages <= $minPages) {
			if ($pages < $minPages) {
				$minPages = $pages;
			}
			for($i = 1; $i < $minPages; $i ++) {
				$cross [] = $i;
			}
		} else {
			if ($page + $minPages < $pages) {
				$minPages = $page + $minPages;
				for($i = $page; $i < $minPages; $i ++) {
					$cross [] = $i;
				}
			} else {
				for($i = $pages - $minPages; $i < $pages; $i ++) {
					$cross [] = $i;
				}
			}
		}
		
		$data ['cross'] = $cross;
		
		return $data;
	}

}

?>