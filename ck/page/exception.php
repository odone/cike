<?php
if (ob_get_contents () > 0) {
	ob_clean ();
}

function getErrorSource($filename, $line) {
	if (! file_exists ( $filename )) {
		return;
	}
	$data = file ( $filename );
	$count = count ( $data ) - 1;
	$start = $line - 10;
	if ($start < 1) {
		$start = 1;
	}
	$end = $line + 10;
	if ($end > $count) {
		$end = $count + 1;
	}
	$returns = array ();
	for($i = $start; $i <= $end; $i ++) {
		if ($i == $line) {
			$returns [] = '<div class="codeline breakline"><span class="breaknbsp">&#9728;' . $i . '</span>' . highlightSource ( $data [$i - 1], TRUE ) . '</div>';
		} else {
			$returns [] = '<div class="codeline"><span class="nbsp">&nbsp;' . $i . '</span>' . highlightSource ( $data [$i - 1], TRUE ) . '</div>';
		}
	}
	return $returns;
}

function highlightSource($code) {
	if (preg_match ( '/<\?(php)?[^[:graph:]]/', $code )) {
		$code = highlight_string ( $code, TRUE );
	} else {
		$code = preg_replace ( '/(&lt;\?php&nbsp;)+/', '', highlight_string ( '<?php ' . $code, TRUE ) );
	}
	return $code;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<link rel="shortcut icon" href="/favicon.ico" />
<meta name="msapplication-task"
	content="name=论坛; action-uri=http://www.ccike.com;icon-uri=/favicon.ico" />
<meta name="msapplication-task"
	content="name=手册; action-uri=http://www.ccike.com;icon-uri=/favicon.ico" />
<style type="text/css">
body {
	background-color: #efefef;
	font-family: "Microsoft YaHei", "宋体", "Segoe UI", sans-serif;
	margin: 0 auto 0 auto;
	padding: 0;
	font-size: 14px;
}

.title,.expandsection,.tasksection,.expando,.icon,.help,.close {
	position: relative;
}

ul,ol {
	padding-left: 2em;
}

.title {
	font: 64px normal;
	line-height: 1.2em;
	height: 100%;
	background-color: #d8e2f4;
	color: #ffffff;
	margin: 0;
	border-bottom: 1px dotted #cccccc;
}

.tasksection,.expandsection {
	padding-bottom: 10px;
}

.mainContent,.footer {
	padding-left: 38px;
	padding-right: 38px;
}

.footer {
	width: 50em;
	float: left;
}

.expandsection {
	padding-top: 10px;
}

.expando {
	top: 3px;
	padding-left: 3px;
	border: none;
}

.close,.icon,.help {
	top: 3px;
	margin-right: 5px;
	border: none;
	text-decoration: none;
	outline: none;
}

.description,.textcount {
	color: #505050;
}

.tasks {
	font-size: 130%;
	line-height: 160%;
	color: #549c00;
	text-decoration: none;
	outline: none;
}

.line {
	margin: 0;
	padding: 0;
	font-size: 1px;
	height: 5px;
	background-color: #ffffff;
}

a {
	color: #4F81BD;
	text-decoration: none;
}

a:hover {
	text-decoration: underline;
}

.clipboard {
	white-space: nowrap;
	overflow: hidden;
}

.clipboardViewer {
	float: left;
	vertical-align: top;
	width: 26em;
}

.clipboardViewerOpen {
	float: left;
	vertical-align: top;
	width: 40em;
}

.code {
	margin: 5px 0 5px 0;
	list-style-type: none;
	padding: 0px;
	background: #ffffcc;
	border: 1px solid #cccccc;
}

.codeline {
	line-height: 1.5em;
	font-style: nomal;
}

.nbsp {
	display: block;
	float: left;
	width: 30px;
	color: #333333;
	text-align: right;
	font-size: 10px;
	padding-right: 5px;
	border-right: 1px solid #cccccc;
}

.breaknbsp {
	display: block;
	float: left;
	width: 30px;
	color: #ff0000;
	text-align: right;
	font-size: 10px;
	padding-right: 5px;
	border-right: 1px solid #cccccc;
}

.breakline {
	background-color: #FFB3B5;
}

.itemlist {
	background: #ffffcc;
	border: 1px solid #cccccc;
	padding: 3px;
}
</style>
<script language="javascript">
            function toggleCode(t, id){
                var e = document.getElementById(id);
                e.style.display = e.style.display=='none' ? 'block' : 'none';
                t.innerHTML = t.innerHTML == '[+]' ? '[-]' : '[+]';
            }
        </script>
</head>
<body>
<div id="titleHeader" class="title">未捕捉异常</div>
<div class="line"></div>
<div id="contentContainer" class="mainContent">
<p class="tasks">
                <?php
																echo get_class ( $ex ), ' : ', $ex->getMessage ();
																?>
                <div>
                    错误代码：<?php
																				echo $ex->getCode ();
																				?><br />
调用堆栈：
<ol class="help">
                        <?php
																								$traces = $ex->getTrace ();
																								foreach ( $traces as $trace ) {
																									if (! isset ( $trace ['file'] ))
																										continue;
																									$id = '_' . md5 ( $trace ['file'] . $trace ['line'] );
																									?>
                            <li>
	<div>
                                    <?php
																									echo $trace ['file']?>,&nbsp;行：<?php
																									echo $trace ['line']?>
                                    <a href="javascript:;"
		onclick="toggleCode(this, '<?php
																									echo $id?>')">[+]</a>
	<ul class="code" style="display: none;" id="<?php
																									echo $id?>">
                                        <?php
																									foreach ( getErrorSource ( $trace ['file'], $trace ['line'] ) as $line ) {
																										?>
                                            <li>
                                                <?php
																										echo $line?>
                                            </li>
                                        <?php
																									}
																									?>
                                    </ul>
	</div>

	</li>
                        <?php
																								}
																								?>
                    </ol>
</div>
</p>
<p class="description">您可以选择： <a href="javascript:;"
	onclick="document.location.reload();">重试</a> &nbsp; <a
	href="javascript:;" onclick="history.back();">返回</a></p>
<i style="font-size: 10px; color: #666666;">
					process times：<?php
					echo Util::getProcessTime();
					?> s . powered by <a
	href="http://ccike.com" >ccike</a>.com <?php
	echo CIKE_VER?>
			</i></div>

<div class="footer"></div>
</body>
</html>