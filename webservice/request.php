<?php
//所有来自用户的请求都会走这个文件
// if(isset($_REQUEST["sid"])){
// 	session_id($_REQUEST["sid"]);
// }
include_once("../includes/global.php");
//解析
$d = json_decode($_REQUEST["d"]);
Daemon_IndexControl::dispatch($d);
//向浏览器PUSH数据
Efuns::dataFlush();