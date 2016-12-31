<?php
//数据库操作
class Database
{
    //查询
    public static function query ($sql)
    {
        $rows = array();
        if ($res = mysql_query($sql)) {
            if (mysql_num_rows($res)) { //数组为有内容
                while ($row = mysql_fetch_assoc($res)) {
                    $rows[] = $row;
                }
            }
        }
        else {
            Efuns::logEvent("database", "sql=" . $sql . " error=" . mysql_error());
        }
        return $rows;
    }
	
    //执行
    public static function exec ($sql)
    {
        $ret = 0;
        if (mysql_query($sql)) {
            $ret = mysql_affected_rows(); //影响的条数  >=0
        }
        else {
            Efuns::logEvent("database", "sql=" . $sql . " error=" . mysql_error());
        }
        return $ret;
    }
    
    //执行数据插入时的ID
    public static function lastInsertId () 
    {
        return mysql_insert_id();
    }

}
