<?php
//英雄对象
class Object_Hero extends Object_Base
{
    public function __construct($hid)
    {
    	$this->id = $hid;
		$this->table = "m_heroinfo";
		return $this->restore();
    }
	
	public function init($uid, $tid)
	{
		$this->set('id', $this->id);
		$this->set('owner', $uid);
		$this->set('tid', $tid);
		$this->set('atk', 0);
		$this->set('matk', 0);
		$this->set('hp', 0);
		$this->set('fc', 0);
		$this->set('level', 0);
		$this->set('skill1', array('id' => 0, 'level' => 0, 'upgradeCoin' => 0));
		$this->set('skill2', array('id' => 0, 'level' => 0, 'upgradeCoin' => 0));
		$this->set('upgradeCoin', 0);
		$this->levelUp(); //默认升到1级
		$this->upgradeSkill(1); //默认升到1级
		$this->upgradeSkill(2); //默认升到1级
	}
	
	public function output()
	{
        $out = $this->dbase;
		$tid = $this->query("tid");
		$sql = "select * from cfg_hero where id =" . $tid;
		$ohArr = Database::query($sql);
		$ohArr = $ohArr[0];
		unset($ohArr['id']);
		unset($ohArr['atk']);
		unset($ohArr['matk']);
		unset($ohArr['hp']);
		unset($ohArr['fc']);
		$ohArr['skill1'] = $this->getSkill(1);
		$ohArr['skill2'] = $this->getSkill(2);
		$ohArr['totalFc'] = $out['fc'] + $ohArr['skill1']['fc'] + $ohArr['skill2']['fc'];
		$out = array_merge($out, $ohArr);
        return $out;
	}
	
	public function getTotalFc(){
		$fc = $this->query("fc");
		$skill1 = $this->getSkill(1);
		$skill2 = $this->getSkill(2);
		return $fc + $skill1['fc'] + $skill2['fc'];
	}
	
	public function getSkill($index)
	{
		$skill = $this->query("skill" . $index);
		$sql = "select * from cfg_skill where id =" . $skill['id'];
		$ohArr = Database::query($sql);
		$ohArr = $ohArr[0];
		$ohArr['level'] = $skill['level'];
		$ohArr['upgradeCoin'] = $skill['upgradeCoin'];
		$ohArr['arg1'] = $ohArr['arg1'] + $ohArr['arg1_increment'] * ($skill['level'] - 1);
		$ohArr['arg2'] = $ohArr['arg2'] + $ohArr['arg2_increment'] * ($skill['level'] - 1);
		$ohArr['fc'] = $ohArr['fc'] + $ohArr['fc_increment'] * ($skill['level'] - 1);
		$ohArr['describe'] = str_replace( 'arg1', $ohArr['arg1'], $ohArr['describe']);
		$ohArr['describe'] = str_replace( 'arg2', $ohArr['arg2'], $ohArr['describe']);
		return $ohArr;
	}
	
	public function upgradeSkill($index)
	{
		$skill = $this->query('skill' . $index);
        $levelNew = $skill['level'] + 1;
        $this->set('skill' . $index . '/level', $levelNew);
		$sql = "select * from cfg_skilllevel where level = $levelNew";
		$rows = Database::query($sql);
        $this->set('skill' . $index . '/upgradeCoin', $rows[0]['upgradeCoin']);
		return 1;
	}
	
	public function levelUp ()
    {
        //提高等级
        $tid = $this->query('tid');
        $levelOld = $this->query('level');
        $levelNew = $levelOld + 1;
        $this->set('level', $levelNew);
		$sql = "select * from cfg_herolevel where level = $levelNew";
		$rows = Database::query($sql);
        $this->set('upgradeCoin', $rows[0]['upgradeCoin']);
		$sql = "select * from cfg_hero where id =" . $tid;
		$ohArr = Database::query($sql);
		$ohArr = $ohArr[0];
		//提升能力
		$atk = $ohArr['atk'] + $ohArr['atk_increment'] * $levelOld;
		$matk = $ohArr['matk'] + $ohArr['matk_increment'] * $levelOld;
		$hp = $ohArr['hp'] + $ohArr['hp_increment'] * $levelOld;
		$fc = $ohArr['fc'] + $ohArr['fc_increment'] * $levelOld;
		$this->set('atk', $atk);
		$this->set('matk', $matk);
		$this->set('hp', $hp);
		$this->set('fc', $fc);
		$this->set('skill1/id', $ohArr['skill1']);
		$this->set('skill2/id', $ohArr['skill2']);
        //推送
        Efuns::setVariablesHttp('heroes/' . $tid, $this->output());
        return 1;
    }
}