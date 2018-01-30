<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/8/1
 * Time: 11:11
 */
function view_path($path=null){
    $request=\core\Request::instance();
    if(is_null($path)){
        return APP_PATH.$request->module().DS.'view'.DS.$request->controller().DS.$request->action().'.php';
    }elseif (is_string($path)){
        if(strpos($path,'/')!==false){
            $path=explode('/',$path);
            return APP_PATH.$request->module().DS.'view'.DS.$path[0].DS.$path[1].'.php';
        }else{
            return APP_PATH.$request->module().DS.'view'.DS.$request->controller().DS.strtolower($path).'.php';
        }
    }
    return null;
}


function url($url='',array $vars=[],$domain=false){
    $info   =  parse_url($url);
    $url    =  !empty($info['path'])?$info['path']:\core\Request::instance()->action();
    if($domain===true){
        $domain=\core\Request::instance()->host();
    }
    if($url){
        $url=trim($url,'/');
        $path=explode('/',$url);
        $var=array();
        $var[]=!empty($path)?array_pop($path):\core\Request::instance()->action();
        $var[]=!empty($path)?array_pop($path):\core\Request::instance()->controller();
        $var[]=!empty($path)?array_pop($path):\core\Request::instance()->module();
    }
    $url=\core\Request::instance()->baseFile().'/'.implode('/',array_reverse($var));
    if(!empty($vars)){
        foreach ($vars as $var => $val){
            if('' !== trim($val))$url .= '/' . $var . '/' . urlencode($val);
        }
    }

    if($domain) {
        $url   =  'http://'.$domain.$url;
    }
    return $url;
}


function tablename($table=''){
    return \core\DB::tablename($table);
}

function post($name = '', $default = null, $filter = ''){
    return \core\Request::instance()->post($name, $default, $filter);
}


function redirect($url='', $time=0, $msg='') {
    if(empty($url)){
        if(isset($_SERVER['HTTP_REFERER'])){
            $url=$_SERVER['HTTP_REFERER'];
        }else{
            $url=url();
        }
    }
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}


/**
 * 更改用户socre记录
 * @param $member_id
 * @param $activity_id
 * @param int $change_score
 * @param string $recode
 */

function score_log($member_id,$change_score=0,$recode='',$operator=0){
        return \core\DB::insert('score_log',array('member_id'=>$member_id,'change_score'=>$change_score,'addtime'=>time(),'recode'=>$recode,'operator'=>$operator));
}

/**
 * 记录支付信息
 * @param $member_id
 * @param $pay_sn
 * @param $pay_money
 * @return bool|int
 */
function pay_log($member_id,$pay_sn,$pay_money){
    return  \core\DB::insert('pay_log',array('pay_sn'=>$pay_sn,'pay_money'=>floatval($pay_money),'member_id'=>intval($member_id),'pay_time'=>time()));
}


function formatBytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}


function time2second($seconds){
    $seconds = (int)$seconds;
    if( $seconds<86400 ){//如果不到一天
        $format_time = gmstrftime('%H %M %S', $seconds);
        $time = explode(' ',$format_time);
        $format_time = $time[0].'时'.$time[1].'分'.$time[2].'秒';
    }else{
        $time = explode(' ', gmstrftime('%j %H %M %S', $seconds));//Array ( [0] => 04 [1] => 14 [2] => 14 [3] => 35 )
        $format_time = ($time[0]-1).'天'.$time[1].'时'.$time[2].'分'.$time[3].'秒';
    }
    return $format_time;
}

/**
 * 获取游戏资源文件地址
 * @param string $game
 * @param bool $is_domain
 * @return string
 */
function game_view_path($game='',$is_domain=false){
    $domain=$is_domain?\core\Request::instance()->domain():'';
    return empty($game)?$domain.'/app/index/view/':$domain.'/app/index/view/'.$game.'/';
}

/**
 * 返回json内容
 * @param array $arr
 */
function ajax_return($arr=array()){
    header('Content-type: application/json');
    die(json_encode($arr));
}