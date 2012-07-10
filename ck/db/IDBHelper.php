<?php

! defined ( 'IN_CIKE' ) ? null : '!';

interface IDBHelper {
	function getSelectSql($fields = '*', $condition = '1=1', $orderby = null, $limit = null);
	function getInsertSql($row);
	function getUpdateSql($row, $condition = '1=0');
	function getDeleteSql($condition = '1=0');
	function getMetas();
	function filterRow($row);
}

?>