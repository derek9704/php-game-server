<?php
include_once ("../includes/global.php");

//if($_SERVER['REMOTE_ADDR'] != DEBUG_IP) exit;

//清空MEM
Cache::flush();

//清除用户数据
mysql_query("truncate m_heroinfo");
mysql_query("truncate m_serverinfo");
mysql_query("truncate m_userinfo");
mysql_query("truncate user");
mysql_query("truncate arena");
mysql_query("truncate arenalog");

//初始化
$args = new stdClass(); 
$args->userport = "1";
$args->name = "测试";
Daemon_DebugApp::gmCreateUserAction($args);

//插入机器人数据
Daemon_ArenaMod::initRobot();

echo "Installation complete. Pls delete this file at once.";