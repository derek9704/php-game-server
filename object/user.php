<?php
//玩家对象
class Object_User extends Object_Base
{
    public function __construct($uid)
    {
    	$this->id = $uid;
		$this->table = "m_userinfo";
		return $this->restore();
    }
	
	public function init($name)
	{
		$this->set('id', $this->id);
		$this->set('name', $name);
		$this->set('level', 0);
		$this->set('stamina', 0);
		$this->set('staminaMax', 0);
		$this->set('staminaUpdateTime', time());
		$this->set('exp', 0);
		$this->set('upgradeExp', 0);
	    $this->set('gold', Enums_UserConfig::initGold);
        $this->set('coin', Enums_UserConfig::initCoin);
		$this->set('heroes', array());
		$this->set('mine', array());
		$this->set('mainHero', Enums_UserConfig::initHeroType);
		$this->set('everyDayRefreshTime', null);
		$this->set('battleTeam', array());
		$this->set('arena', array('defenceTeam' => array(), 'winCount' => 0));
		$this->levelUp(); //默认升到1级
		$this->addStamina($this->query('staminaMax')); //默认加满体力
	}
	
	public function output()
	{
        $out = $this->dbase;
		unset($out['everyDayRefreshTime']);
		unset($out['lastLoginTime']);
		unset($out['heroes']);
		unset($out['arena']);
		unset($out['mine']);
        return $out;
	}
	
	public function addCoin($coin)
	{
		if($coin <= 0) return 0;
		$coinOld = $this->query('coin');
		$coinNew = $coinOld + $coin;
		$this->set('coin', $coinNew);
		Efuns::setVariablesHttp('user/coin', $coinNew);
		return 1;
	}
	
	public function consumeCoin($coin)
	{
		if($coin <= 0) return 0;
		$coinOld = $this->query('coin');
		$coinNew = $coinOld - $coin;
		if($coinNew < 0) {
			Command::errorMsg(Enums_LangConfig::notEnoughCoin);
			return 0;
		}		
		$this->set('coin', $coinNew);
		Efuns::setVariablesHttp('user/coin', $coinNew);
		return 1;
	}
	
	public function addGold($gold)
	{
		if($gold <= 0) return 0;
		$goldOld = $this->query('gold');
		$goldNew = $goldOld + $gold;
		$this->set('gold', $goldNew);
		Efuns::setVariablesHttp('user/gold', $goldNew);
		return 1;
	}
	
	public function addStamina($stamina)
	{
		if($stamina <= 0) return 0;
		$staminaOld = $this->query('stamina');
		$staminaMax = $this->query('staminaMax');
		$staminaNew = $staminaOld + $stamina;
		if($staminaNew > $staminaMax) $staminaNew = $staminaMax;
		$this->set('stamina', $staminaNew);
		Efuns::setVariablesHttp('user/stamina', $staminaNew);
		return 1;
	}
	
	public function consumeStamina($stamina)
	{
		if($stamina < 0) return 0;
		$staminaOld = $this->query('stamina');
		$staminaNew = $staminaOld - $stamina;
		if($staminaNew < 0) {
			Command::errorMsg(Enums_LangConfig::notEnoughStamina);
			return 0;
		}
		$this->set('stamina', $staminaNew);
		Efuns::setVariablesHttp('user/stamina', $staminaNew);
		//消耗体力同比增加经验
		$this->addExp($stamina);
		return 1;
	}
	
    //增加经验，自动会完成升级判定，返回1是表示升级了
    public function addExp ($exp)
    {
        $expOld = $this->query('exp');
        $upgradeExp = $this->query('upgradeExp');
        $expNew = $expOld + $exp;
        if($expNew >= $upgradeExp){
            if($this->levelUp()){
	            //循环剩余经验
	            $expLeft = $expNew - $upgradeExp;
	            $this->set('exp', 0);
	            $this->addExp($expLeft);
	            return 1;
            }else{
            	//升到顶级的情况
            	$this->set('exp', $upgradeExp);
            }
        }else {
            $this->set('exp', $expNew);
        }
		//推送
        Efuns::setVariablesHttp('user/exp', $expNew);
        return 0;
    }
    
    public function levelUp ()
    {
        $levelOld = $this->query('level');
        if ($levelOld == Enums_UserConfig::levelMax) {
            return 0;
        }
        //提高等级
        $levelNew = $levelOld + 1;
        $this->set('level', $levelNew);
		$sql = "select * from cfg_userlevel where level = $levelNew";
		$rows = Database::query($sql);
        $this->set('upgradeExp', $rows[0]['upgradeExp']);
		$this->set('staminaMax', $rows[0]['staminaMax']);
        //推送
        Efuns::setVariablesHttp('user', $this->output());
        //升级成功
        return 1;
    }
}