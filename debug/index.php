<?php
include_once ("../includes/global.php");

//if($_SERVER['REMOTE_ADDR'] != DEBUG_IP) exit();

if (empty($_SESSION["userId"])) {
    $_SESSION["userId"] = 1;
}

$han = fopen("debugCase.txt", "r");
if ($han) {
    while (! feof($han)) {
        $buf = trim(fgets($han, 4096));
        if (empty($buf)) continue;
        $arr[] = explode(" ", $buf);
    }
    fclose($han);
}
$arr = json_encode($arr);

if (!empty($_POST["action"])) {
    $action = $_POST["action"];
    $args = $_POST["args"];
    switch ($action)
    {
        case "changeUser":
            $_SESSION["userId"] = $args['userId'];
			$_thisUser = new Object_User($_SESSION["userId"]);
			$_POST["action"] = "getUserData";
        default:
            $d = array(
                "action" => $_POST["action"],
                "args" => $args
            );
            $ds = json_encode($d);
            $d = json_decode($ds);
            Daemon_IndexControl::dispatch($d);
    }
    // 向浏览器PUSH数据
    Efuns::dataFlush();
    exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="js/jquery.min.js"></script>
<script src="js/dump.js"></script>
<script>
//ajax返回事件
function ajaxCallback(buffers)
{
    try{
        datainfo = eval( '(' + buffers + ')' ) ;
    }
    catch(err){
		var newWindow = window.open();
		var d = newWindow.document;
		d.open();
		d.write(buffers);
		d.close();
        return;
    }
    dump(datainfo);
	return;
};

//ajax发送失败
function send_failed(data)
{
	alert('403发送失败');
}

//ajax呼叫php module&function
function call_server(action, args)
{
    strData = {"action" : action, "args" : args};
	$.ajax({
	    type: "POST",
        url: "index.php",
        data: strData,
        cache: false,
        success: ajaxCallback,
        error :send_failed
    })
}

function changeUser()
{
    var userId = prompt('请输入用户UserId', '<?php echo $_SESSION["userId"];?>');
    if(userId){
		call_server('changeUser', {'userId':userId});
		$("#userId").text(userId);
    }
}

function doTest(tAction)
{
	data = $('#'+tAction).val();
	if(data == "") {
		alert('请输入参数');
		return;
	}
    dataArgs = eval( '(' + data + ')' );	
    call_server(tAction , dataArgs);
}

function addExample(tAction, array)
{
	var val = "{";
	var strArr = array.split("|");
	for(var i = 0; i < strArr.length; i ++) {
		var index = strArr[i].indexOf(":");
		if(index<0)
		{
			val += "\""+strArr[i]+"\":\"1\"";
		}
		else
		{
			var str1 = strArr[i].substring(0, index);
			var str2 = strArr[i].substring(index+1);
			var patrn=/^\[\S+\]$/;  
			var strTemp;
			if (!patrn.exec(str2)) strTemp = "\""+str2+"\"";
			else strTemp = str2;			
			val += "\""+str1+"\":"+strTemp;
		}	
		if(i != strArr.length - 1) val += ",";
	}
	val += "}";
    $('#'+tAction).val(val);
}

$(function() {
	var jsonmsg = eval("("+'<?php echo $arr;?>'+")");
	var html = "";
	$.each(jsonmsg, function(key, val) {
		if(val[1] == undefined) {
			html += val[0]+"<br/>";
		} else {					
			html += "<a href=\"javascript:doTest('"+val[1]+"')\">"+val[0]+" - "+val[1]+"</a>";
			html += " <label for='args'>参数：</label>";
			html += "<input id=\""+val[1]+"\" name='args' type='text' id='args' size='60'/>";
			html += "[<a href=\"javascript:addExample('"+val[1]+"', '"+val[2]+"')\">参数范例</a>]<br />";
		}
	});
	$("#form1").html(html);
});
</script>

<style type="text/css">
body,td,th {
	font-family: 微软雅黑;
	font-size: 14px;
}
</style>

<title>测试360</title>
</head>

<body>
	<p>
		服务端黑盒[当前测试用户ID <a href="javascript:changeUser()"><span id='userId'><?php echo $_SESSION["userId"];?></span></a>][<a href="javascript:doTest('getUserData')">?</a>] [<a href="javascript:doTest('heartbeat')">♥</a>] 测试：
	</p>
	<form id="form1" name="form1" method="post" action=""></form>
</body>
</html>
