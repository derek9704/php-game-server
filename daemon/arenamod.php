<?php
//竞技场相关
class Daemon_ArenaMod
{	
    //新用户进入排名，一般是用户到一定级数后触发
    public static function initRank($user)
    {
    	$id = $user->id;
		$sql = "select count(*) from arena";
		$rows = Database::query($sql);
		$rank = $rows[0]['count(*)'] + 1;
		$sql = "insert into arena set rank = $rank, id = $id";
		Database::exec($sql);
		return 1;
    }	
	
	//获取玩家防守队伍数据
	public static function getDefenceTeam($user, $detail = false)
	{
		$heroArr = array();
        $ids = $user->query('arena/defenceTeam');
		array_unshift($ids, $user->query('mainHero'));
        foreach ($ids as $tid) {
        	$hid = $user->query('heroes/' . $tid);
            $oh = new Object_Hero($hid);
			if($detail){
				$heroArr[] = $oh->output();
			}else{
				$heroArr[] = array('tid' => $tid, 'level' => $oh->query('level'), 'fc' => $oh->getTotalFc());	
			}
		}
		return $heroArr;
	}
	
	//初始化竞技场机器人
	public static function initRobot()
	{
		set_time_limit(0);
		for ($i = 40; $i >= 10; $i--) { 
			for ($j = 0; $j < 5; $j++) {
				do{
					$userport = 100000 + $i * 10 + $j;
					$randName1 = Enums_ArenaConfig::$RandName1[mt_rand(0, sizeof(Enums_ArenaConfig::$RandName1) - 1)];
	            	$randName2 = Enums_ArenaConfig::$RandName2[mt_rand(0, sizeof(Enums_ArenaConfig::$RandName2) - 1)];
					$randName3 = Enums_ArenaConfig::$RandName3[mt_rand(0, sizeof(Enums_ArenaConfig::$RandName3) - 1)];
					$name = $randName1 . $randName2 . $randName3;
					$robot = Daemon_UserMod::createUser($userport, $name);
				}while(!$robot);
				//初始配置
				for ($k = 0; $k < $i; $k++) { 
					$robot->levelUp();
				}
				Daemon_ArenaMod::initRank($robot);
				$ids = array_rand(array(5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0), 3);
				$robot->set('arena/defenceTeam', $ids);
				$mainHero = mt_rand(1, 4);
				$robot->set('mainHero', $mainHero);
				$ids[] = $mainHero;
				foreach($ids as $index){
					$oh = Daemon_HeroMod::hireHero($robot, $index);
					for ($k = 0; $k < $i; $k++) { 
						$oh->levelUp();
						$oh->upgradeSkill(1);
						$oh->upgradeSkill(2);
					}
					$oh->save();
				}			
				//保存
				$robot->save();
				$robot->saveToDb();
			}
		}
	}

}