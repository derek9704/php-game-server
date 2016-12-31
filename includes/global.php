<?php
session_start();
//公共类
include_once ('database.php');
include_once ('cache.php');
include_once ('efuns.php');
include_once ('command.php');
//Define autoloader
function __autoload ($class)
{
    if (class_exists($class, false) || interface_exists($class, false)) {
        return 1;
    }
    $className = ltrim($class, '\\');
    $file = FILE_DIR . DIRECTORY_SEPARATOR;
    $file .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    $file = strtolower($file);
    if (preg_match('/[^a-z0-9\\/\\\\_.:-]/i', $file)) {
        throw new Exception('Security check: Illegal character in filename');
        return 0;
    }
    if (file_exists($file)) {
        require_once $file;
        return 1;
    }
    throw new Exception('class : ' . $class . ' can not found');
    return 0;
}
//Load Game Configure
$iniGameConf = parse_ini_file("E:\www\server\conf.ini", true);
//默认1服
if(empty($serverId)) $serverId = 1;
foreach ($iniGameConf as $serverName => $serverOne) {
    if ($serverName == 'global') {
        $gMyServerGlobal = $serverOne;
    }else if ($serverOne["server_id"] == $serverId) {
        $gMyServer = $serverOne;
        break;
    }
}
if (empty($gMyServerGlobal) || empty($gMyServer)) exit('no server');
foreach ($gMyServer as $key => $value) {
    $gMyServerGlobal[$key] = $value;
}
// 初始化全局变量配置
define("SERVER_ID", $gMyServerGlobal['server_id']);
define("CHARGE_URL", $gMyServerGlobal['charge_url']);
define("LOG_DIR", $gMyServerGlobal['log_dir']);
define("FILE_DIR", $gMyServerGlobal['file_dir']);
define("GAME_STATUS", $gMyServerGlobal['game_status']);
define("DB_HOST", $gMyServerGlobal['db_host']);
define("DB_BASE", $gMyServerGlobal['db_base']);
define("DB_USER", $gMyServerGlobal['db_user']);
define("DB_PASS", $gMyServerGlobal['db_pass']);
define("CACHE_TYPE", $gMyServerGlobal['cache_type']); 
define("CACHE_SERVER", $gMyServerGlobal['cache_host']); 
define("CACHE_PORT", $gMyServerGlobal['cache_port']);
define("KAIFU_TIME", $gMyServerGlobal['kaifu_time']);
define("DEBUG_STATUS", $gMyServerGlobal['debug_status']);
define("DEBUG_IP", $gMyServerGlobal['debug_ip']);
//DEBUG
DEBUG_STATUS == 'on' ? error_reporting(7) : error_reporting(0);
//建立数据库连接
mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_query("SET NAMES UTF8");
mysql_query("use " . DB_BASE);
//建立Cache连接
if (CACHE_TYPE == "memcached") {
    $_cacheServer = new Memcached();
    $_cacheServer->addServer(CACHE_SERVER, CACHE_PORT);
}
else if (CACHE_TYPE == "memcache") {
    $_cacheServer = new Memcache();
    $_cacheServer->addServer(CACHE_SERVER, CACHE_PORT);
}
//初始化用户
if (!empty($_SESSION["userId"])) {
	$_thisUser = new Object_User($_SESSION["userId"]);
}
//这里定义各种 CFG 配置
define("ARENA_LOCK", "arenaLock"); //竞技场排行锁定