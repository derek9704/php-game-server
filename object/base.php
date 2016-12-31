<?php
//此类为基本继承
abstract class Object_Base
{
	//在每个对象的构造函数中进行初始化
	public $id;
	public $table;
    public $dbase = array();


    //查询某个值 如果这个值不存在，则返回0
    public function query ($parts)
    {
    	$dbase = $this->dbase;
        if (substr($parts, - 1) == "/") $parts = substr($parts, 0, strlen($parts) - 1);
        if (!is_array($dbase)) return 0;
        $parts = explode('/', $parts);
        if (!sizeof($parts)) $parts = array(
                $parts
        );
        $value = $dbase;
        foreach ($parts as $partone) {
            if (!is_array($value)) return 0;
            if (array_key_exists($partone, $value)) {
                $value = $value[$partone];
            }
            else {
                return 0;
            }
        }
        return $value;
    }

    //删除某个值
    public function delete ($parts)
    {
    	$dbase = $this->dbase;
        if (substr($parts, - 1) == "/") $parts = substr($parts, 0, strlen($parts) - 1);
        $parts = explode('/', $parts);
        if (! sizeof($parts)) $parts = array(
                $parts
        );
        $str = "";
        foreach ($parts as $partone) {
            $str .= "['" . $partone . "']"; //这里包含一个单引号，确保 $partone 总是不被再次解析 而仅仅是作为一个字符串
        }
        $evalstr = 'unset($dbase' . $str . ');'; //这里使用单引号， $map不被解析
        eval($evalstr);
		$this->dbase = $dbase;
    }
	
    //设置某个值 如果设置失败，则返回0 否则返回1 如果设置的路径上存在一个叶子（非数组值），就会导致失败
    // 比如  query("a/b/c") ==1 则 set("a/b/c/d/e") 就会失败  set并不会默认的把 query("a/b/c") 这个值清除掉。
    public function set ($parts, $value)
    {
        if (substr($parts, - 1) == "/") $parts = substr($parts, 0, strlen($parts) - 1);
        $parts = explode('/', $parts);
        if (!sizeof($parts)) $parts = array(
                $parts
        );
        $map = $temp = $this->dbase;
		if (! is_array($map)) return 0;
        $j = sizeof($parts);
        for ($i = 0; $i < $j; $i ++) {
            $partone = $parts[$i];
            if (array_key_exists($partone, $temp)) {
                $temp = $temp[$partone];
                if (!is_array($temp) && ($i < $j - 1)) { //路径上不能有叶子节点
                    return 0;
                }
            }
            else
                break;
        }
        $str = "";
        foreach ($parts as $partone) {
            $str .= "['" . $partone . "']"; //通过包含单引号 来防止注入
        }
        $evalstr = '$tmp = &$map' . $str . ";"; //使用单引号 防止变量被解析
        eval($evalstr);
        $tmp = $value;		
        $this->dbase = $map;
        return 1;
    }

    public function save ()
    {
    	$id = $this->id;
		$table = $this->table;
        if (empty($id)) return 0;
        $ret = Cache::set($table . "_" . $id, $this->dbase);
        return $ret;
    }
	
    public function restore ()
    {
    	$id = $this->id;
		$table = $this->table;
        if (empty($id)) return 0;
        $saveData = Cache::query($table . "_" . $id);
        if (! is_array($saveData)) {
        	//读取MYSQL
	        $sql = "select info from $table where id='$id'";
        	$res = Database::query($sql);
	        if (empty($res)) return 0;
	        $saveData = unserialize($res[0]["info"]);
        }
        $this->dbase = $saveData;
        return 1;
    }
    
    public function saveToDb ()
    {
    	$id = $this->id;
		$table = $this->table;    	
        if (empty($id)) return 0;
        $sql = "replace into {$table} set info = '" . addslashes(serialize($this->dbase)) . "', updatetime=now() ,id='$id'";
        Database::exec($sql);
        return 1;
    }

}