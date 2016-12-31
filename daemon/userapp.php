<?php
//用户相关
class Daemon_UserApp
{
    //这是整个游戏的起点  客户端将在这里开始正式让玩家进入游戏 请确保玩家已经通过登录接口，完成登录
    public static function getUserDataAction ($args)
    {
    	global $_thisUser;
		//用户信息
        $data = $_thisUser->output();
        Efuns::setVariablesHttp('user', $data);
        //英雄信息
        $data = $_thisUser->query('heroes');
        foreach( $data as  $tid => $hid){
            $oh = new Object_Hero($hid);
            $data[$tid] = $oh->output();
        }
        Efuns::setVariablesHttp('heroes', $data);
		//竞技场
		$data = $_thisUser->query('arena');
		Efuns::setVariablesHttp('arena', $data);
        return;
    }
	
   //默认心跳，返回用户最新数据
    public static function heartbeatAction ($args)
    {
    	global $_thisUser;
        
        /***********以下是每天间隔的更新*************/
        $date = $_thisUser->query("everyDayRefreshTime");
        if ($date != date("Y-m-d")) {
            $_thisUser->set("everyDayRefreshTime", date("Y-m-d"));
        }
        
        /***********以下是心跳间隔的更新*************/
        $_thisUser->set("lastLoginTime", time());
		//恢复体能
		Daemon_UserMod::updateStamina($_thisUser);
        //保存
        $_thisUser->save();
        return;
    }
	
	
	//队伍配置
	public static function setUserBattleTeamAction($args)
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
		$_thisUser->set('battleTeam', $ids);
		//输出
		Efuns::setVariablesHttp('user/battleTeam', $ids);
        //保存
        $_thisUser->save();
        return;
	}
	
}