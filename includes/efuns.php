<?php
//常用方法
class Efuns
{	
    //命令数据
    private static $_commandData = array();
    //用户数据
    private static $_userData = array();
    //出错信息
    private static $_errorMsg = null;
	
    //呼叫客户端功能 唯有Command.php可调用
    public static function callFuncHttp ($js_func, $js_args = "")
    {
    	if($js_func == "errorMsg") {
    		self::$_errorMsg = $js_args;
		}
		else{
	        $result = array(
            "command" => $js_func,
            "args" => $js_args
	        );
	        self::$_commandData[] = $result;	
		}
    }
	
    //赋值到客户端
    public static function setVariablesHttp ($js_vars, $js_args)
    {
        self::$_userData[$js_vars] = $js_args;
    }
	
    //向浏览器倾倒数据
    public static function dataFlush ()
    {
        $responseData = array();
        //处理下data数据
        $tmp = array();
        //打包输出data内容
        if(!self::$_errorMsg){
	        if (sizeof(self::$_userData)) {
	            foreach (self::$_userData as $dataKey => $dataValue) {
	                $result = array(
	                        "data" => $dataKey,
	                        "args" => $dataValue
	                );
	                $tmp[] = $result;
	            }
	        }
	        //data数据压在前面
	        $responseData = array_merge($tmp, self::$_commandData);
		}else{
            $responseData = array(array(
                "command" => 'errorMsg',
                "args" => self::$_errorMsg
            ));
		}
        //准备输出
        $res = array(
                'responseData' => $responseData
        );
        //输出
        $out = json_encode($res);
        echo $out;
    }
    
    //日志
    public static function logEvent ($type, $content)
    {
		$fileDir = LOG_DIR;
        if (empty($fileDir)) return 0;
        $fileName = $type . date("Ymd", time()) . ".log";
        list ($usec, $time) = explode(" ", microtime());
        $rText = '';
        //获取用户/
        if (!empty($_SESSION["userId"])) {
            $rText = '[' . $_SESSION["userId"] . ']';
        }
        $rText .= date("Y-m-d H:i:s", $time) . ".$usec||" . $content;
        if (! file_exists($fileDir)) {
            mkdir($fileDir, 0777);
        }
        if ($fo = fopen($fileDir . $fileName, "a+")) {
            fwrite($fo, $rText . "\r\n");
            fclose($fo);
        }
    }
	
	/**
     *   只能是中文简体、英文、数字、下划线、点
     *   检查字符串 屏蔽字
     *   返回true or false
     */
    public static function checkStr ($str)
    {
        mb_regex_encoding("UTF-8");
        if (! mb_ereg("^[\w|·]+$", $str)) {
            return 0;
        }
        $sql = "select count(*) from cfg_badword where '" . addslashes($str) . "' like concat(concat('%',word),'%')";
        $rows = Database::query($sql);
        if (! is_array($rows)) return 0;
        if ($rows[0]["count(*)"]) return 0;
        return 1;
    }
	
    /**
     *   过滤特殊字符
     *   返回修正后的串
     */
    public static function filterStr ($str)
    {
        $sql = "select word from cfg_badword where '" . addslashes($str) . "' like concat(concat('%',word),'%')";
        $rows = Database::query($sql);
        if (! is_array($rows)) return $str;
        if (sizeof($rows) == 0) return $str;
        foreach ($rows as $row) {
            $str = str_replace($row["word"], '**', $str);
        }
        return $str;
    }
    
    //解析 xxx:xxx|xxx:xxx|xxx:xxx
    public static function parseStr($str)
    {
        $ret = array();
        if(empty($str)) return $ret;
        $arr = explode('|', $str);
        for ($i = 0; $i < sizeof($arr); $i ++) {
            $tmpArr = explode(':', $arr[$i]);
            $ret[$tmpArr[0]] = $tmpArr[1];
        }
        return $ret;
    }
    
    /**
     * 指定概率计算
     * @param array $proArr
     */
    public static function getRand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        $randNum = mt_rand(1, $proSum);
        $totalNum = 0;
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            if ($randNum <= ($proCur + $totalNum)) {
                $result = $key;
                break;
            } else {
                $totalNum += $proCur;
            }
        }
        return $result;
    }
	
	// are 2 floats equal  
	function floatcmp($f1,$f2,$precision = 10) { 
	    $e = pow(10,$precision);  
	    $i1 = intval($f1 * $e);  
	    $i2 = intval($f2 * $e);  
	    return ($i1 == $i2);  
	}  
	
	// is one float bigger than another   
	function floatgtr($big,$small,$precision = 10) {
	    $e = pow(10,$precision);  
	    $ibig = intval($big * $e);  
	    $ismall = intval($small * $e);  
	    return ($ibig > $ismall);  
	}  
	
	// is on float bigger or equal to another
	function floatgtre($big,$small,$precision = 10) {
	    $e = pow(10,$precision);  
	    $ibig = intval($big * $e);  
	    $ismall = intval($small * $e);  
	    return ($ibig >= $ismall);  
	}  	

}