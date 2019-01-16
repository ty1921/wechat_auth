<?php
/**
 * 本页用于微信授权登录及其代理
 * 
 * @version    [V3.0]   		
 * @author     [ty1921] 			<[ty1921@gmail.com]>
 * @param 	[type]		 		[get参数type，不传则默认请求openid，为userinfo时取用户头像昵称]
 * @param 	[backurl]		 	[get参数backurl，则授权完成后返回到该backurl]
 * @createtime [2017-8-4 ]
 * @update 	   [2018-7-30]
 *
 *
 * 
 * eg. 【http://xxx.com/wx.php】为本PHP页面的公网地址,【http://192.168.xxx.xxx】为需要获取微信数据的页面
 *
 *     简单模式，仅获取openid  http://xxx.com/wx.php?backurl=http://192.168.xxx.xxx
 * 
 *     复杂模式，获取昵称、头像  http://xxx.com/wx.php?type=userinfo&backurl=http://192.168.xxx.xxx
 */



namespace wechat\sns;

session_start();

//----------------------------------------------------------------
//1 参数配置

//微信appid
$appid 		  = 'xxxxxxxxx';

//微信appsecret
$appsecret 	  = 'xxxxxxxxx';

//本PHP页面的公网地址
$redirect_url = urlencode('http://xxxx.com/wx.php');	

//----------------------------------------------------------------


if( empty($_GET['type']) )
{
	//默认简单模式
	$_SESSION['type'] = 'snsapi_base';
}
else
{
	$_SESSION['type'] = $_GET['type'];
}


if( empty($_GET['backurl']) )
{
	exit('backurl lost.');
}
else
{
	//记录返回地址
	$_SESSION['backurl'] = $_GET['backurl'];
}




//3 如果openid已经获取过则直接返回，否则发起授权
if( empty($_SESSION['openid']) )
{
	if( empty($_SESSION['type']) || $_SESSION['type'] == 'snsapi_base')
	{
		//3.1 默认snsapi_base模式，只获取openid
		if( empty($_GET['code']) )
		{
			getBaseInfo($appid,$redirect_url);
		}
		else
		{
			getUserOpenId($appid,$appsecret,$redirect_url);
		}
	}
	elseif( $_SESSION['type'] == 'userinfo')
	{
		//3.2 userinfo模式，获取openid、昵称和头像
		if( empty($_GET['code']) )
		{
			getUserDetail($appid,$redirect_url);
		}
		else
		{
			getUserinfo($appid,$appsecret,$redirect_url);
		}
	}
}
else
{
	if( empty($_SESSION['type']) || $_SESSION['type'] == 'snsapi_base')
	{
		//返回openid
		back( array('openid' =>$_SESSION['openid']) );
	}
	else
	{
		//返回头像昵称和openid
		echo "|||返回头像昵称和openid";
	}
}






/**
 * [getBaseInfo 获取用户基础信息的函数]
 * @param  [] $appid        []
 * @param  [] $redirect_url [即本页线上地址]
 * @return []               []
 */
function getBaseInfo($appid,$redirect_url)
{
	//1、获取code
	$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_url."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";

	header('location:'.$url);
}



/**
 * [getUserOpenId 获取用户OPENID]
 * @param  [type] $appid        []
 * @param  [type] $appsecret    []
 * @param  [type] $redirect_url [即本页线上地址]
 * @return [type]               [无返回，直接跳回backurl]
 */
function getUserOpenId($appid,$appsecret,$redirect_url)
{
	//2、获取网页授权的access_token
	$code = $_GET['code']; //从上面函数getBaseInfo获取得到

	$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code ";

	//3、获取openid
	$res = http_curl($url);

	//var_dump($res); //里面便是有openid数据

	$arr = json_decode($res,true);

	back( $arr );
	
}



/**
 * [getUserDetail 获取用户头像、昵称等的第一步]
 * @param  [type] $appid        [description]
 * @param  [type] $redirect_url [description]
 * @return [type]               [description]
 */
function getUserDetail($appid,$redirect_url)
{
	$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_url."&response_type=code&scope=snsapi_userinfo&state=333#wechat_redirect";

	header('location:'.$url);
}

/**
 * [getUserinfo 获取用户头像、昵称等的第二步]
 * @param  [type] $appid        [description]
 * @param  [type] $appsecret    [description]
 * @param  [type] $redirect_url [description]
 * @return [type]               [description]
 */
function getUserinfo($appid,$appsecret,$redirect_url)
{
	$code = $_GET['code']; //从上面函数getBaseInfo获取得到

	$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code ";

	$res = http_curl( $url );

	$arr1 = json_decode( $res, true );

	$openid = $arr1['openid'];

	$access_token = $arr1['access_token'];

	$url2 = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";

	$res2 = http_curl( $url2 );

	$arr2 = json_decode( $res2, true );

	back( $arr2 );

}

 
/**
 * [http_curl curl]
 * @param  [string]  $url        [访问的URL]
 * @param  string  $post         [post数据(不填则为GET)]
 * @param  string  $cookie       [提交的$cookies]
 * @param  integer $returnCookie [是否返回$cookies]
 * @return [？]                  [curl的返回数据]
 */
 function http_curl( $url,$post='',$cookie='', $returnCookie=0 ){
 	 	//echo $url."||";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
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
            return $info;
        }else{
            return $data;
        }
}


/**
 * [back 返回到backurl]
 * @param  [char] $openid [微信openid]
 * @return [无返回]
 */
function back( $arr )
{
	if( !empty($_SESSION['backurl']) )
	{	
		$get_str = '';

		foreach ($arr as $k => $v) 
		{
			if( $k == 'privilege' )
			{
				$get_str .= "&{$k}=" . json_encode($v) ;
			}
			else
			{
				$get_str .= "&{$k}={$v}";
			}
		}

		if( !strstr( $_SESSION['backurl'], '?' ) )
		{
			//如果返回参数不带？，则强制加上
			$get_str = '?' . substr( $get_str, 1 );
		}


		$backurl = $_SESSION['backurl'] . $get_str;
		
		//echo $backurl;

        exit ("<script> window.location = '{$backurl}'; </script>");
        //echo "即将返回{$_SESSION['backurl']}?1=1&openid={$openid}";
	}
	else
	{
		echo "backurl lost.";
	}
}
