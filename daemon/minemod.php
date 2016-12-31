<?php
//洞穴相关
class Daemon_MineMod
{
    //生成新地图
    public static function genMine($user)
    {
    	$mapId = mt_rand(1, 4);
		$sql = "select * from cfg_minemap where id = $mapId";
		$rows = Database::query($sql);
		$map = json_decode($rows[0]["map"]);
		foreach($map as &$tiles){
			foreach($tiles as &$tile){
				$tileId = $tile;
				$sql = "select * from cfg_minetile where id = $tileId";
				$rows2 = Database::query($sql);
				$tile = $rows2[0];
				$tile["current_hp"] = $tile["hp"];
				$tile["visible"] = false;
				$tile["locked"] = false;
				$tile["destroyed"] = false;
				$tile["canClick"] = false;
			}
		}
		$enemyLevel = mt_rand($rows[0]["enemyLevelMin"], $rows[0]["enemyLevelMax"]);
		$rewardCoin = mt_rand($rows[0]["coinMin"], $rows[0]["coinMax"]);
		$enemyArr = explode(',', $rows[0]["enemyRange"]);
		$count = min(sizeof($enemyArr), 4);
		$picked = array_rand($enemyArr, $count);
		$enemy = array();
		foreach($picked as $index) {
			$enemy[] = $enemyArr[$index];
		}
		$arr = array(
			'id' => $rows[0]["id"],
			'rewardCoin' => $rewardCoin,
			'enemyLevel' => $enemyLevel,
			'enemy' => $enemy,
			'map' => $map,
			'times' => 0,
			'lastRefreshDay' => date("Y-m-d")
		);
		$user->set("mine", $arr);
		return $arr;
    }
}