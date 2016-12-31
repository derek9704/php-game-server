<?php
//竞技场相关
class Daemon_ArenaApp
{
    //获取信息
    public static function getArenaInfoAction($args)
    {
    	global $_thisUser;
		$id = $_thisUser->id;
		$sql = "select rank from arena where id = $id";
		$rows = Database::query($sql);
		$myRank = $rows[0]['rank'];
		$sql = "select * from arena where rank < $myRank order by rank";
		$rows = Database::query($sql);
		$count = min(count($rows), 3);
		$rivals = array();
		if($count){
			$randArr = $count > 1 ? array_rand($rows, $count) : array(0);
			foreach($randArr as $index){
				$one = $rows[$index];
				$user = new Object_User($one['id']);
				$one['name'] = $user->query('name');
				$one['level'] = $user->query('level');
				$one['defenceTeam'] = Daemon_ArenaMod::getDefenceTeam($user);				
				$rivals[] = $one;
			}	
		}
		//输出
		Efuns::setVariablesHttp('arena/myRank', $myRank);
		Efuns::setVariablesHttp('arena/rivals', $rivals);
		return;
    }
	
    //获取排行
    public static function getArenaRankAction($args)
    {
    	global $_thisUser;
		$sql = "select * from arena order by rank limit " . Enums_ArenaConfig::rankListCount;
		$rows = Database::query($sql);
		$rankList = array();
		foreach($rows as $one){
			$user = new Object_User($one['id']);
			$one['name'] = $user->query('name');
			$one['level'] = $user->query('level');
			$one['defenceTeam'] = Daemon_ArenaMod::getDefenceTeam($user);				
			$rankList[] = $one;
		}
		//输出
		Efuns::setVariablesHttp('arena/rankList', $rankList);
		return;
    }
	
    //获取对战记录
    public static function getArenaLogAction($args)
    {
    	global $_thisUser;
		$id = $_thisUser->id;
		$sql = "select * from arenalog where userId1 = $id or userId2 = $id order by id desc limit " . Enums_ArenaConfig::arenaLogCount;
		$rows = Database::query($sql);
		$data = array();
		foreach($rows as $row){
			$rivalId = $row['userId1'] == $id ? $row['userId2'] : $row['userId1'];
			$user = new Object_User($rivalId);
			$one = array();
			$one['rivalLevel'] = $user->query('level');
			$one['rivalName'] = $user->query('name');
			$one['time'] = $row['create_time'];
			$one['rankChange'] = $row['rankChange'];
			if(($row['win'] == 1 && $row['userId1'] == $id) || ($row['win'] == 0 && $row['userId2'] == $id)){
				$one['win'] = 1;
			}else{
				$one['win'] = 0;
			}
			$data[] = $one;
		}
		//输出
		Efuns::setVariablesHttp('arena/log', $data);
		return;
    }	
	
    //防守队伍配置
    public static function setArenaTeamAction($args)
    {
		global $_thisUser;
		$ids = $args->tids;
		//判定上阵佣兵个数
		if(count($ids) > 3){
            Command::errorMsg(Enums_LangConfig::battleHeroExceed);
			return;
		}
        //判定佣兵合法性
        foreach ($ids as $tid) {
            if(!$_thisUser->query('heroes/' . $tid)){
                Command::errorMsg(Enums_LangConfig::heroNotHired);
				return;
            }
		}
		$_thisUser->set('arena/defenceTeam', $ids);
		//输出
		Efuns::setVariablesHttp('arena/defenceTeam', $ids);
        //保存
        $_thisUser->save();
        return;
    }
	
    //开始挑战
    public static function startArenaBattleAction($args)
    {
    	global $_thisUser;
		$rank = $args->rank;
		$sql = "select id from arena where rank = $rank";
		$rows = Database::query($sql);
		$rival = new Object_User($rows[0]['id']);
		$battleData = Daemon_ArenaMod::getDefenceTeam($rival, true);
		//输出
		Efuns::setVariablesHttp('arena/battleData', $battleData);
		return;
    }
	
    //完成挑战
    public static function finishArenaBattleAction($args)
    {
    	global $_thisUser;
		$id = $_thisUser->id;
		$win = $args->win;
		$rivalId = $args->rivalId;
		if($win == 1){
			//加个内存锁，防止并发
			$needWait = Cache::waitLock(ARENA_LOCK);
			if ($needWait) {
	            Command::errorMsg();
	            return;
		    }
		    Cache::setLock(ARENA_LOCK, 1);
			//更新排行
			$sql = "select rank from arena where id = $id";
			$rows = Database::query($sql);
			$myRank = $rows[0]['rank'];
			$sql = "select rank from arena where id = $rivalId";
			$rows = Database::query($sql);
			$rivalRank = $rows[0]['rank'];
			$rankChange = 0;
			if($myRank < $rivalRank){ //我的排名高，不交换
				Cache::removeLock(ARENA_LOCK);
			}else{
				$rankChange = $myRank - $rivalRank;
				$sql = "update arena set id = $id where rank = $rivalRank";
				Database::exec($sql);
				$sql = "update arena set id = $rivalId where rank = $myRank";
				Database::exec($sql);
				Cache::removeLock(ARENA_LOCK);
			}
			$sql = "insert into arenalog set userId1 = $id, userId2 = $rivalId, win = 1, rankChange = $rankChange, create_time = now()";
			Database::exec($sql);
			//更新缓存
			$winCount = $_thisUser->query('arena/winCount') + 1;
			$_thisUser->set('arena/winCount', $winCount);
			$_thisUser->save();
			Efuns::setVariablesHttp('arena/winCount', $winCount);
		}else{
			$sql = "insert into arenalog set userId1 = $id, userId2 = $rivalId, win = 0, rankChange = 0, create_time = now()";
			Database::exec($sql);
		}
		return;
    }

}