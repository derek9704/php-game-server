<?php
//登陆接口
include_once("../includes/global.php");
$userport = $_REQUEST['userport'];
if(empty($userport)){
	exit('-1');
}
$sql = "select id from user where userport = '$userport'";
$rows = Database::query($sql);
if(empty($rows)){
	exit('-2');
}
//设置登录状态
$_SESSION["userId"] = $rows[0]['id'];
$_thisUser = new Object_User($_SESSION["userId"]);
$_thisUser->set("lastLoginTime", time());
$_thisUser->save();
//更新表
$sql = "update user set state = 1, login_time = now() where id =" . $_SESSION["userId"];
Database::exec($sql);
$id = session_id();
exit($id);