<?php
//缓存
class Cache
{	
    //移除
    public static function remove ($key)
    {
        global $_cacheServer;
        $_cacheServer->delete($key);
    }
	
    //设置
    public static function set ($key, $value)
    {
        global $_cacheServer;
        $_cacheServer->set($key, $value);
    }

    //查询
    public static function query ($key)
    {
        global $_cacheServer;
        $value = $_cacheServer->get($key);
        return $value;
    }

	//查询配置
    public static function getCoreCfg ($key)
    {
        global $_cacheServer;
        $value = $_cacheServer->get('_cfg_' . $key);
        if ($value === false) {
            $sql = "select info from m_serverinfo where keyname = '$key'";
            $rows = Database::query($sql);
            if ($rows) {
                $data = unserialize($rows[0]['info']);
                Cache::setCoreCfg($key, $data);
                $value = $data; 
            }
        }
        return $value;
    }

	//设置配置
    public static function setCoreCfg ($key, $value)
    {
        global $_cacheServer;
        $_cacheServer->set('_cfg_' . $key, $value);
    }

	//设置锁
    public static function setLock ($key, $value)
    {
        global $_cacheServer;
        $_cacheServer->set('_lock_' . $key, $value);
    }

	//查询锁
    public static function getLock ($key)
    {
        global $_cacheServer;
        $value = $_cacheServer->get('_lock_' . $key);
        return $value;
    }
	
	//等待锁
	public static function waitLock ($key)
	{
		$needWait = 0;
		$lockname = self::getLock($key);
		if ($lockname) {
	        $needWait = 1;
	        $ttSleep = 10;
	        for ($i = 0; $i < $ttSleep; $i++) {
	            $lockname = self::getLock($key);
	            if ($lockname) {
	                usleep(20000); //100 0000(1s)  10 0000(0.1s)
	            }
	            else {
	                $needWait = 0;
	                break; //锁已被释放了，可继续操作
	            }
	        }
		}
		return $needWait;
	}

	//移除锁
    public static function removeLock ($key)
    {
        global $_cacheServer;
        $_cacheServer->delete('_lock_' . $key);
    }
    
	//清空
    public static function flush ()
    {
        global $_cacheServer;
        $_cacheServer->flush();
    }
}

