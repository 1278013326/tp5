<?php

$app_id = "个人 APP ID";
$app_secret = "个人 APP KEY";
$my_url = "申请应用时所填的回调地址";
//注  回调地址必须和该代码域名一致
if (empty($_GET['code'])) {//判断是否有code,若无code则去qq第三方接口请求授权
    $_SESSION['state'] = md5(uniqid(rand(), TRUE));//一串随机的字符串，用于回调时对比参数防止csrf攻击
    //拼接url地址同时url化回调地址并跳转
    $url = 'https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=' . $app_id . '&redirect_uri=' . urlencode($my_url) . '&state=' . $_SESSION['state'];
    header("location: " . $url);
} else {
    //省略一步  使用state判断是否csrf攻击   使用$_SESSION['state']和$_GET['state']进行对比判断授权过程是否被劫持
    //拼接url地址使用code请求并获得access_token
    $url = 'https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=' . $app_id . '&client_secret='
        . $app_secret . '&code=' . $_GET['code'] . '&redirect_uri=' . $my_url;
    //发出请求
    $info = file_get_contents($url);
    //var_dump($info);由于传回来的数据是个字符串，不能直接使用，所以进行变量化
    $params = array();
    parse_str($info, $params);//把传回来的数据参数变量化
    //var_dump($params);变量化后的结果
    /*
     * array(3) {
     *  ["access_token"]=> string(32) "*********"  access_token
     *  ["expires_in"]=> string(7) "7776000"    access_token有效时间
     * ["refresh_token"]=> string(32) "****" }
     * */
    //使用access_token去请求openid
    $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" . $params['access_token'];
    $str = file_get_contents($graph_url);
    //var_dump($str);  请求得到的结果
    //string(83) "callback( {"client_id":"*****","openid":"*****"} ); "
    //由于得到的结果还是字符串且还不是json类型，只能手动截取json格式的数据
    //判断callback在字符串$str是否存在，如果存在则返回下标，否则false，下标不完全等于false，因为0会被判断为false
    if (strpos($str, "callback") !== false) {
        $lpos = strpos($str, "(");//判断(出现的下标
        $rpos = strrpos($str, ")");//判断)出现的下标，加上r从右边开始查找
        $str = substr($str, $lpos + 1, $rpos - $lpos - 1);//截取字符串
        //从$rpos+1下标 { 处截取  $rpos - $lpos -1 位 ，此时$str的值就为一个json格式的数据了
        // {"client_id":"*****","openid":"*****"}
    }
    $user = json_decode($str);//json转对象,存放有返回的数据 client_id ，openid
    //拼接url并访问，用access_token和openid得到用户信息
    $urls = 'https://graph.qq.com/user/get_user_info?access_token=' . $params['access_token'] . '&oauth_consumer_key=' . $app_id . '&openid=' . $user->openid;
    $rs = file_get_contents($urls);
    $ref = json_decode($rs);//得到的用户信息
}