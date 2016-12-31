<?php
//测试工具
class Daemon_GmApp
{
    public static function gmCreateUserAction ($args)
    {
		$user = Daemon_UserMod::createUser($args->userport, $args->name);
		if (!$user) return;
		//初始配置
		Daemon_ArenaMod::initRank($user);
		$arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
		foreach($arr as $index){
			$oh = Daemon_HeroMod::hireHero($user, $index);
			$oh->save();
		}				
		//保存
		$user->save();
		$user->saveToDb();
		//输出
        $userData = $user->output();
        Efuns::setVariablesHttp('user', $userData);
        return;
    }
	
	public static function gmAddUserStaminaAction ($args)
    {
        global $_thisUser;
        $_thisUser->addStamina($args->num);
        $_thisUser->save();
    }
	
	public static function gmUserLevelUpAction ($args)
    {
        global $_thisUser;
        $_thisUser->levelUp();
        $_thisUser->save();
    }
	
	public static function gmAddUserCurrencyAction ($args)
    {
        global $_thisUser;
        $_thisUser->addCoin($args->coin);
        $_thisUser->addGold($args->gold);
		$_thisUser->save();
    }
}
