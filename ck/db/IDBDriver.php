<?php

! defined ( 'IN_CIKE' ) ? null : '!';

interface IDBDriver {
	function connect($dsn);
	function close();
	function escapeField($fields);
	function query($sql);
	function free();
	function getAll($sql);
	function getAffected();
	function getInsertId();
	function startTransaction();
	function commit();
	function rollback();
}

?>