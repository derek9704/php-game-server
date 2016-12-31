<?php
//测试工具
class Daemon_DebugApp
{
	
   public static function debugQueryUserCacheAction ($args)
    {
        global $_thisUser;
        $userData = $_thisUser->dbase;
        Efuns::setVariablesHttp('user', $userData);
        return;
    }

    public static function debugRemoveUserCacheAction ($args)
    {
        global $_thisUser;
        $_thisUser->delete($args->path);
        $_thisUser->save();
        //输出用户
        $userData = $_thisUser->dbase;
        Efuns::setVariablesHttp('user', $userData);
        return;
    }	
    
    public static function debugSetUserCacheAction ($args)
    {
        global $_thisUser;
        $_thisUser->delete($args->path, $args->val);
        $_thisUser->save();
        //输出用户
        $userData = $_thisUser->dbase;
        Efuns::setVariablesHttp('user', $userData);
        return;
    }
	
    public static function debugQueryHeroCacheAction ($args)
    {
        $oh = new Object_Hero($args->id);
        //输出英雄
        $heroData = $oh->dbase;
        Efuns::setVariablesHttp('hero', $heroData);
        return;
    }

    public static function debugRemoveHeroCacheAction ($args)
    {	
        $oh = new Object_Hero($args->id);
		$oh->delete($args->path);
        $oh->save();
        //输出英雄
        $heroData = $oh->dbase;
        Efuns::setVariablesHttp('hero', $heroData);
        return;
    }	
    
    public static function debugSetHeroCacheAction ($args)
    {
        $oh = new Object_Hero($args->id);
		$oh->set($args->path, $args->val);
        $oh->save();
        //输出英雄
        $heroData = $oh->dbase;
        Efuns::setVariablesHttp('hero', $heroData);
        return;
    }
	
}
