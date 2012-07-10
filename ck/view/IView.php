<?php

! defined ( 'IN_CIKE' ) ? null : '!';

interface IView {
	function display();
	function assign($var);
}

?>