<?php
//$serverId = $argv[1];
$globleFile = "../includes/global.php";
if(!file_exists($globleFile)){
    echo "$globleFile is not exists.";
    exit();
}
include_once ($globleFile);
//停服的时候不运行
if(GAME_STATUS == "off") exit();
//只保存在线用户即可
$sql = "select id from user where state = 1";
$rows = Database::query($sql);
foreach($rows as $row){
    $userId = $row['id'];
    $user = new Object_User($userId);
    //更新在线状态
    if(time() - $user->query('lastLoginTime') > 60){ // 已经离线60s
    	$sql = "update user set state = 0 where id = $userId";
		Database::exec($sql);
    }
    //保存到数据库
    $user->saveToDb();
    //保存所有英雄
    foreach($user->query('heroes') as $tid => $hid){
        $oh = new Object_Hero($hid);
        $oh->saveToDb(); 
    }
}