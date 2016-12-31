<?php
//英雄相关
class Daemon_HeroMod
{
    //招募
    public static function hireHero($user, $tid)
    {
		//判断是否已有这个类型的英雄
		if($user->query('heroes/' . $tid)){
			Command::errorMsg(Enums_LangConfig::heroAlreadyHired);
			return 0;
		}
		//招募
		$info = serialize(array());
        $sql = "insert into m_heroinfo set info='$info', updatetime=now()";
        Database::exec($sql);
		$hid = Database::lastInsertId();
        //创建英雄
        $oh = new Object_Hero($hid);
        $oh->init($user->id, $tid);
        //设置用户数据
        $user->set('heroes/' . $tid, $hid);
        return $oh;
    }
	
	//生成怪物信息
	public static function genMonsterInfo($tid, $level)
	{
		$sql = "select * from cfg_hero where id =" . $tid;
		$rows = Database::query($sql);
		$info = $rows[0];
		$info['level'] = $level;
		$info['atk'] = $info['atk'] + $info['atk_increment'] * ($level - 1);
		$info['matk'] = $info['matk'] + $info['matk_increment'] * ($level - 1);
		$info['hp'] = $info['hp'] + $info['hp_increment'] * ($level - 1);
		$info['fc'] = $info['fc'] + $info['fc_increment'] * ($level - 1);
		$info['tid'] = $info['id'];
		unset($info['atk_increment']);
		unset($info['matk_increment']);
		unset($info['hp_increment']);
		unset($info['fc_increment']);
		unset($info['soul_awake']);
		unset($info['id']);
		return $info;
	}
}