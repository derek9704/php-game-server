<?php
//主动推送客户端执行的命令
class Command
{	
    //错误提示
    public static function errorMsg ($msg = '')
    {
    	if($msg == '') $msg = Enums_LangConfig::error;
        Efuns::callFuncHttp('errorMsg', $msg);
    }

    //正确提示
    public static function correctMsg ($msg)
    {
        Efuns::callFuncHttp('correctMsg', $msg);
    }
    
    //弹出确认框（带确认、取消按钮）
    public static function confirmMsg ($msg, $action, $args)
    {
        $confirmArr = array(
                'msg' => $msg,
                'action' =>$action,
                'args' => $args
        );
        Efuns::callFuncHttp('confirmMsg', $confirmArr);
    }
    
    //弹出提示框(带确认按钮)
    public static function noticeMsg ($msg)
    {
        $noticeArr = array(
                'msg' => $msg
        );
        Efuns::callFuncHttp('noticeMsg', $noticeArr);
    }
 
    //跳转URL
    public static function pageJump ($tips)
    {
        Efuns::callFuncHttp("pageJump", $tips);
    }
}

