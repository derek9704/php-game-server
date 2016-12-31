<?php
//用户相关
class Daemon_UserMod
{
    
    //创建角色
    public static function createUser ($userport, $name)
    {
    	//检查用户名长度
    	$i = mb_strlen($name, 'utf-8');
		if($i > Enums_UserConfig::userNameLengthLimit) {
			Command::errorMsg(Enums_LangConfig::userNameLengthExceed);
			return 0;
		}
		//检查非法字符
		if (!Efuns::checkStr($name)) {
			Command::errorMsg(Enums_LangConfig::illegalWord);
			return 0;
		}
		//检查账号名是否存在
		$sql = "select count(*) from user where userport = '$userport'";
		$rows = Database::query($sql);
        if ($rows[0]['count(*)'] > 0) {
        	Command::errorMsg(Enums_LangConfig::userportExist);
            return 0;
        }
		//检查角色名是否存在
		$sql = "select count(*) from user where name = '$name'";
		$rows = Database::query($sql);
        if ($rows[0]['count(*)'] > 0) {
        	Command::errorMsg(Enums_LangConfig::nameExist);
            return 0;
        }
		//创建
		$sql = "insert into user set userport = '$userport', name = '$name', create_time = now(), state = 1";
		$ret = Database::exec($sql);
        if (!$ret) {
        	Command::errorMsg(Enums_LangConfig::nameExist);
            return 0;
        }
        //创建用户
        $id = Database::lastInsertId();
        $user = new Object_User($id);
        $user->init($name);
        return $user;
    }

	public static function updateStamina($user)
	{
        $last = $user->query('staminaUpdateTime');
        $passtime = time() - $last;
        $point = floor($passtime / Enums_UserConfig::staminaRecoveryInterval);
        if ($point > 0) {
            $user->set('staminaUpdateTime', time() - ($passtime % Enums_UserConfig::staminaRecoveryInterval));
			$user->addStamina($point);
        }
		return 1;
	}

}