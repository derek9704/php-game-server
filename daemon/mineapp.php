<?php
//洞穴相关
class Daemon_MineApp
{
    //进入洞穴
    public static function enterMineAction($args)
    {
    	global $_thisUser;
		//判定体力是否足够
		if($_thisUser->consumeStamina(Enums_MineConfig::enterMineStamine)){
			$sql = "select name, resource, levelLimit from cfg_minemap where id = 1";
			$rows = Database::query($sql);
			//等级限制
			$userLevel = $_thisUser->query("level");
			if($rows[0]['levelLimit'] > $userLevel){
				Command::errorMsg(Enums_LangConfig::userLevelTooLow);
				return;
			}
			$info = $_thisUser->query("mine");
			if(empty($info) || $info["lastRefreshDay"] != date("Y-m-d")){
				$info = Daemon_MineMod::genMine($_thisUser);
			}
			//解析
			$enemyArr = array();
			foreach($info['enemy'] as $tid){
				$enemyArr[] = Daemon_HeroMod::genMonsterInfo($tid, $info['enemyLevel']);
			}
			unset($info['enemyLevel']);
			$info['enemy'] = $enemyArr;
			$info['stamina'] = array(
				'hit' => Enums_MineConfig::hitStamina,
				'magic' => Enums_MineConfig::magicStamina,
				'win' => Enums_MineConfig::winBattleStamina,
				'lose' => Enums_MineConfig::loseBattleStamina
			);
			$info = array_merge($info, $rows[0]);
			//保存
        	$_thisUser->save();
			//输出
			Efuns::setVariablesHttp('mine', $info);
		}
		return;
    }
	
    //结算，每次离开矿洞时结算
    public static function leaveMineAction($args)
    {
    	global $_thisUser;
		$stamina = $args->stamina;
		//判定体力是否足够
		if($_thisUser->consumeStamina($stamina)){
			$coin = $args->coin;
			$gold = $args->gold;
			$map = $args->map;
			$_thisUser->addCoin($coin);
			$_thisUser->addGold($gold);
			$_thisUser->set("mine/map", $map);
			$times = $_thisUser->query("mine/times");
			$_thisUser->set("mine/times", $times + 1);
			//保存
        	$_thisUser->save();
		}else{
			Command::errorMsg(Enums_LangConfig::notEnoughStamina);
			return;
		}
		return;
    }	
	
	//刷新矿洞
	public static function refreshMineAction ($args)
    {
        global $_thisUser;
        $_thisUser->set("mine", array());
        $_thisUser->save();
		Command::correctMsg(Enums_LangConfig::refreshMineSuccess);
		return;
    }

}