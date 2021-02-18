<?php

/**
* 发送模板消息
* 每天定时推送天气预报
*/
ini_set('display_errors', 'On');
define('APPID', '***');
define('SECRET', '***');
$send_num=0;
$url='https://devapi.qweather.com/v7/weather/now?key=***&location=10**218';
$tq=curl_request($url);
$tmp=json_decode($tq,true);
$res_tq=$tmp['now'];
$link=$tmp['fxLink'];
$content="天气：".$res_tq['text']." \n温度：".$res_tq['temp'].'°';
$title="天气预报";
echo send_notice($title,$content,$link).'---'.$send_num;

function send_notice($title,$content,$link){
    $access_token=get_access_token();
   //模板消息
   $json_template = json_tempalte($title,$content,$link);
   $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
   $res=https_request($url,'post','json',$json_template);
   $send_num++;
   if ($res['errcode']==0){
     return '发送成功';
   }else{
       if($send_num>1){
           return '发送失败';
       }else{
           file_put_contents('access_token.txt','');
           echo send_notice($title,$content,$link).'---'.$send_num;
       }
   }
}
 
  /**
   * 将模板消息json格式化
   */
   function json_tempalte($title,$content,$link){
    //模板消息
    $template=array(
      'touser'=>'obuaS5tpH9***GDmhw',  //用户openid
      'template_id'=>"Tnui27WP1Z6***AKHPFfBsc", //在公众号下配置的模板id
      'url'=>$link, //点击模板消息会跳转的链接
      'topcolor'=>"#FF0000",
      'data'=>array(
            'title'=>array('value'=>$title,'color'=>"#FF0000"),
            'content'=>array('value'=>$content)
        )
        // 
        );
    $json_template=json_encode($template);
    return $json_template;
  }
 

function get_access_token(){
    $access_token=file_get_contents('access_token.txt');
    if($access_token){
        return $access_token;
    }else{
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".APPID."&secret=".SECRET;
    	$res = https_request($url, 'get', 'json');
    	$access_token = $res["access_token"];
    	file_put_contents('access_token.txt',$access_token);
    	return $access_token;
    }
}

function https_request($url, $type="get", $res="", $data = ''){
    //1.初始化curl
    $curl = curl_init();
    //2.设置curl的参数
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if ($type == "post"){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    //3.采集
    $output = curl_exec($curl);
    //var_dump(curl_getinfo($curl));
    //4.关闭
    curl_close($curl);
    if ($res == 'json') {
        return json_decode($output,true);
    }else{
        return $output;
    }
}

 function curl_request($url,$post='',$cookie='', $returnCookie=0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        curl_setopt($curl, CURLOPT_ENCODING, '');
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            $info=mb_convert_encoding($info, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');//使用该函数对结果进行转码
            return $info;
        }else{
            $data=mb_convert_encoding($data, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');//使用该函数对结果进行转码
            return $data;
        }
}

?>