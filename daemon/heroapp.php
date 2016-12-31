<?php
//英雄相关
class Daemon_HeroApp
{
    //招募
    public static function hireHeroAction($args)
    {
    	global $_thisUser;
		$tid = $args->tid;
		$oh = Daemon_HeroMod::hireHero($_thisUser, $tid);
		if(!$oh) return;
        //输出英雄信息
        Efuns::setVariablesHttp('heroes/' . $tid, $oh->output());
        //保存
        $_thisUser->save();
        $oh->save();
        return;
    }
	
	//英雄升级
    public static function upgradeHeroAction($args)
    {
    	global $_thisUser;
		$tid = $args->tid;
		//判断是否已有这个类型的英雄
		$hid = $_thisUser->query('heroes/' . $tid);
		if(!$hid){
			Command::errorMsg(Enums_LangConfig::heroNotHired);
			return 0;
		}
    	//判定等级
        $oh = new Object_Hero($hid);
		$heroLevel = $oh->query('level');
		$userLevel = $_thisUser->query('level');
		if($userLevel <= $heroLevel){
			Command::errorMsg(Enums_LangConfig::heroLevelOverUserLevel);
			return 0;
		}
    	//判断金币
    	$upgradeCoin = $oh->query('upgradeCoin');
		if($_thisUser->consumeCoin($upgradeCoin)){
			Command::correctMsg(Enums_LangConfig::heroUpgradeSuccess);
			//判定是否是主英雄，是的话所有主英雄一同升级
			if(in_array($tid, Enums_HeroConfig::$mainHeroArr)){
				foreach(Enums_HeroConfig::$mainHeroArr as $tid){
					$hid = $_thisUser->query('heroes/' . $tid);
					$oh = new Object_Hero($hid);
					$oh->levelUp();
					$oh->save();
				}
			}else{
					$oh->levelUp();
					$oh->save();
			}
		}
		else{
			Command::errorMsg(Enums_LangConfig::notEnoughCoin);
			return 0;
		}
        //保存
        $_thisUser->save();
        return;
    }
	
	//英雄技能升级
    public static function upgradeHeroSkillAction($args)
    {
    	global $_thisUser;
		$tid = $args->tid;
		$index = $args->index;
		//判断是否已有这个类型的英雄
		$hid = $_thisUser->query('heroes/' . $tid);
		if(!$hid){
			Command::errorMsg(Enums_LangConfig::heroNotHired);
			return 0;
		}
    	//判定等级
        $oh = new Object_Hero($hid);
		$heroLevel = $oh->query('level');
		$skillLevel = $oh->query('skill' . $index . '/level');
		if($heroLevel <= $skillLevel){
			Command::errorMsg(Enums_LangConfig::skillLevelOverHeroLevel);
			return 0;
		}
    	//判断金币
    	$upgradeCoin = $oh->query('skill' . $index . '/upgradeCoin');
		if($_thisUser->consumeCoin($upgradeCoin)){
			Command::correctMsg(Enums_LangConfig::skillUpgradeSuccess);
			$oh->upgradeSkill($index);
		}
		else{
			Command::errorMsg(Enums_LangConfig::notEnoughCoin);
			return 0;
		}
		//推送
		Efuns::setVariablesHttp('heroes/' . $tid, $oh->output());
        //保存
        $_thisUser->save();
        $oh->save();
        return;
    }
	
	//英雄转职
	public static function transferMainHeroAction($args)
	{
    	global $_thisUser;
		$tid = $args->tid;
		$_thisUser->set('mainHero', $tid);
		//推送
		Efuns::setVariablesHttp('user/mainHero',  $tid);
        //保存
        $_thisUser->save();
        return;
	}
	
}