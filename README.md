
**【PHP - 微信网页授权代理】**

支持PHP5及以上版本，支持PHP7；
可布置在其他服务器，供多个业务共同使用。

**部署：**

1、进入到web根目录

2、git clone https://github.com/ty1921/wechat_auth.git

3、本PHP页面的公网地址为【http://XXXXXXX/wechat_auth/wx.php】，其中XXXXXXX为你的web根目录访问地址


**示例：**

简单模式，仅获取openid    http://XXXXXXX/wechat_auth/wx.php?backurl=http://192.168.xxx.xxx

复杂模式，获取昵称、头像  http://XXXXXXX/wechat_auth/wx.php?type=userinfo&backurl=http://192.168.xxx.xxx

其中backurl为获取数据成功后，跳回的来源页面地址，也就是你的业务页地址。