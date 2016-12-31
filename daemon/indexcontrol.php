<?php
//用户
include_once ('userapp.php');
//英雄
include_once ('heroapp.php');
//挖矿
include_once ('mineapp.php');
//竞技场
include_once ('arenaapp.php');
//GM工具
include_once ('gmapp.php');
//测试用
include_once ('debugapp.php');

class Daemon_IndexControl
{	
    //自动继承静态类 同接口类似，这些静态类的方法必须是唯一的
    public static function dispatch ($d)
    {
    	//用户必须是登录并且已经创建了角色
		if(!$_SESSION["userId"]){
			Command::errorMsg(Enums_LangConfig::loginWrong);
			return;
		}
		//游戏关闭的话，自动踢线
		if (GAME_STATUS == "off" && $_SERVER['REMOTE_ADDR'] != DEBUG_IP) {
		    Command::errorMsg(Enums_LangConfig::gameOff);
		    return;
		}
		//执行
		$uid = $_SESSION["userId"];
		$lockname = Cache::getLock($uid);
	    $funcName = $d->action . 'Action';
	    if ($lockname) {
	        Efuns::logEvent('lock', $funcName . "locked by (" . $lockname . ')');
	        if ($funcName == 'heartbeatAction') continue; //心跳优先级最低 如果当前操作正在进行，心跳被忽略
	        //刷新了 removeLock
	        if ($funcName == 'getUserDataAction') {
	            Cache::removeLock($uid);
	        }
	        //非心跳锁，玩家点太快了
	        $needWait = Cache::waitLock($uid);
	        if ($needWait) {
	            Efuns::logEvent('lock', $funcName . "locked by death_lock(" . $lockname . ')');
	            Command::errorMsg(Enums_LangConfig::clickLocked);
	            //是否存在死锁 有可能的话 就移除掉
	            Cache::removeLock($uid);
	            return;
	        }
	    }
	    //加锁
	    Cache::setLock($uid, $funcName);
	    $arguments = $d->args;
	    $allClasses = get_declared_classes();
	    foreach ($allClasses as $staticClassDefined) {
	        if (! strstr($staticClassDefined, 'Daemon_')) continue;
	        if (method_exists($staticClassDefined, $funcName)) {
	            call_user_func($staticClassDefined . '::' . $funcName, $arguments);
	        }
    	}
	    //解锁
	    Cache::removeLock($uid);
    }
}