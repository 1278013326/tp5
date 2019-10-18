<?php
namespace app\index\controller;
use app\index\model\User;
use DOMDocument;
use think\Db;
use think\Request;
use think\Controller;
use app\index\controller\Base;

class Taobao extends Base
{
    //获取天猫
    public function index($itemid = '', $taobaourl = '', $cates = '', $merchid = 0)
    {
        $url = 'https://detail.tmall.com/item.htm?spm=a220m.1000858.1000725.13.64933ec2swDDSb&id=603508410508&skuId=4225043624842&areaId=110100&user_id=2177037697&cat_id=2&is_b=1&rn=07d58050093c5c45d7398a960da0be06';
        set_time_limit(0);
        if (is_numeric($url))
        {
            $itemid = $url;
        }
        else
        {
            preg_match('/id\\=(\\d+)/i', $url, $matches);
            if (isset($matches[1]))
            {
                $itemid = $matches[1];
            }
        }

        error_reporting(0);
//        global $_W;
//        $g = pdo_fetch('select * from ' . tablename('ewei_shop_goods') . ' where uniacid=:uniacid and merchid=:merchid and catch_id=:catch_id and catch_source=\'taobao\' limit 1', array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid, ':catch_id' => $itemid));
        $url = $this->get_tmall_page_url($itemid);
//        load()->func('communication');
        $response = $this->ihttp_get($url);
//        print_r($response);die;
        $length = strval($response['headers']['Content-Length']);
        if ($length != NULL)
        {
            return array('result' => '0', 'error' => '未从淘宝获取到商品信息!');
        }
        $content = $response['content'];
        if (function_exists('mb_convert_encoding'))
        {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        }
        $item = array();
        $arr = array();
        preg_match('/TShop\\.Setup\\(([\\s\\S]*)\\s+\\);/', $content, $arr);
        $arr = json_decode(trim($arr[1]), true);
        $item['marketprice'] = $arr['detail']['defaultItemPrice'];
        $dom = new DOMDocument();
        $dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' . $content);
        $xml = simplexml_import_dom($dom);

        $specsArray  = array();
        $specInfo = $xml->xpath('//*[@class="tb-sku"]/dl/dd/ul');
        foreach ($specInfo as $key => $val ) {
            $sizeArr = (array) $val;
            if (isset($sizeArr['@attributes']['data-property'])) {
                $specsArray[$key]['title']      = $sizeArr['@attributes']['data-property'];
            } else {
                continue;
            }
            $specsArray[$key]['itemsCount'] = count($sizeArr['li']);
            $propId                  = explode(':', strval($sizeArr['li'][0]['data-value']));
            $specsArray[$key]['propId']     = $propId[0];
            foreach ($sizeArr['li'] as $li => $k) {
                $k = (array)$k;
                $valueId              = explode(':', $k['@attributes']['data-value']);
                $specsArray[$key]['items'][$li]['valueId'] = $valueId[1];
                $specsArray[$key]['items'][$li]['title']   = strval($k['a']->span);
                $typeInfo = (array)$k['a'];
                if (isset($typeInfo['@attributes']['style'])) {
                    $thumbVal = $typeInfo['@attributes']['style'];
                    $sizeLiInfo = substr($thumbVal, stripos($thumbVal, '//'));
                    $subLength  = stripos($sizeLiInfo, ')');
                    $sizeLiInfoAttrStyle = substr($sizeLiInfo, 0, $subLength);
                    $thumb    =   substr($sizeLiInfoAttrStyle,0, -13);
                    $thumbUrl = 'http:' . $thumb;
                    $specsArray[$key]['items'][$li]['thumb'] = $thumbUrl;
                } else {
                    $specsArray[$key]['items'][$li]['thumb'] = '';
                }
            }
        }
        $item['specs'] = $specsArray;
        $optionsArray = array();
        $i = 0;
        foreach($specsArray[0]['items'] as $key => $val) {
            foreach($specsArray[1]['items'] as $k => $v) {
                $optionsArr[0] = array('propId' => $specsArray[0]['propId'], 'valueId' => $val['valueId']);
                $optionsArr[1] = array('propId' => $specsArray[1]['propId'], 'valueId' => $v['valueId']);
                $optionsArray[$i]['option_specs'] = $optionsArr;
                $optionsArray[$i]['stock'] = '';
                $optionsArray[$i]['title'] = array($val['title'], $v['title']);
                $optionsArray[$i]['marketprice'] = $item['marketprice'];
                $i++;
            }
        }
        $item['options'] = $optionsArray;

        $item['id'] = $g['id'];
        $item['merchid'] = $merchid;
//        if (!(empty($merchid)))
//        {
//            if (empty($_W['merch_user']['goodschecked']))
//            {
//                $item['checked'] = 1;
//            }
//            else
//            {
//                $item['checked'] = 0;
//            }
//        }
        $item['checked'] = 0;
        $prodectNameContent = $xml->xpath('//*[@id="J_DetailMeta"]/div[1]/div[1]/div/div[1]');
        $prodectName = trim(strval($prodectNameContent[0]->h1));
        if (empty($prodectName))
        {
            $prodectName = trim(strval($prodectNameContent[0]->h1->a));
        }
        $item['title'] = $prodectName;
        $imgs = array();
        $i = 1;
        while ($i < 6)
        {
            $img = $xml->xpath('//*[@id="J_UlThumb"]/li[' . $i . ']/a/img');
            if (!(empty($img)))
            {
                $img = strval($img[0]->attributes()->src);
                $img = mb_substr($img, 0, strpos($img, '_60x60q90.jpg'));
                $img = 'http:' . $img;
                $imgs[] = $img;
            }
            ++$i;
        }
        $item['pics'] = $imgs;
        $paramsContent = $xml->xpath('//*[@id="J_AttrList"]');
        $paramsContent = $paramsContent[0]->ul->li;
        $paramsContent = (array) $paramsContent;
        if (!(empty($paramsContent['@attributes'])))
        {
            unset($paramsContent['@attributes']);
        }
        $params = array();
        foreach ($paramsContent as $paramitem )
        {
            $paramitem = strval($paramitem);
            if (!(empty($paramitem)))
            {
                $paramitem = trim(str_replace('：', ':', $paramitem));
                $p1 = mb_strpos($paramitem, ':');
                $ptitle = mb_substr($paramitem, 0, $p1);
                $pvalue = mb_substr($paramitem, $p1 + 1, mb_strlen($paramitem));
                $param = array('title' => $ptitle, 'value' => $pvalue);
                $params[] = $param;
            }
        }
        $item['params'] = $params;
        $pcates = array();
        $ccates = array();
        $tcates = array();
        $pcateid = 0;
        $ccateid = 0;
        $tcateid = 0;
//        if (is_array($cates))
//        {
//            foreach ($cates as $key => $cid )
//            {
//                $c = pdo_fetch('select level from ' . tablename('ewei_shop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $cid, ':uniacid' => $_W['uniacid']));
//                if ($c['level'] == 1)
//                {
//                    $pcates[] = $cid;
//                }
//                else if ($c['level'] == 2)
//                {
//                    $ccates[] = $cid;
//                }
//                else if ($c['level'] == 3)
//                {
//                    $tcates[] = $cid;
//                }
//                if ($key == 0)
//                {
//                    if ($c['level'] == 1)
//                    {
//                        $pcateid = $cid;
//                    }
//                    else if ($c['level'] == 2)
//                    {
//                        $crow = pdo_fetch('select parentid from ' . tablename('ewei_shop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $cid, ':uniacid' => $_W['uniacid']));
//                        $pcateid = $crow['parentid'];
//                        $ccateid = $cid;
//                    }
//                    else if ($c['level'] == 3)
//                    {
//                        $tcateid = $cid;
//                        $tcate = pdo_fetch('select id,parentid from ' . tablename('ewei_shop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $cid, ':uniacid' => $_W['uniacid']));
//                        $ccateid = $tcate['parentid'];
//                        $ccate = pdo_fetch('select id,parentid from ' . tablename('ewei_shop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $ccateid, ':uniacid' => $_W['uniacid']));
//                        $pcateid = $ccate['parentid'];
//                    }
//                }
//            }
//        }
        $item['pcate'] = $pcateid;
        $item['ccate'] = $ccateid;
        $item['tcate'] = $tcateid;
        if (!(empty($cates)))
        {
            $item['cates'] = implode(',', $cates);
        }
        $item['pcates'] = implode(',', $pcates);
        $item['ccates'] = implode(',', $ccates);
        $item['tcates'] = implode(',', $tcates);
//		$url = $this->get_taobao_detail_url($itemid);
//        load()->func('communication');

        preg_match("/descnew\.taobao\.com\/([^\"]*)['\"],/",$content,$descUrlMatch);
//        print_r($descUrlMatch);die;
        $descContent = '';
        if(!empty($descUrlMatch) && !is_array($descUrlMatch[1])){
            $descurl  = $descUrlMatch[1];
            $descurl = "https://descnew.taobao.com/".$descurl;
//            print_r($descurl);die;
            $cookie = 't=39b2ec6263aede3375966903dd01ba9f; thw=cn; hng=CN%7Czh-CN%7CCNY%7C156; enc=kyJ3xykdJBDYrDMUB7wJ4%2FZDufrDcneIVIJqi7OlKROENc9IjlgjNyes%2Fs2M0m4288l2zN6LeWsWOYRXU4HQzw%3D%3D; x=e%3D1%26p%3D*%26s%3D0%26c%3D0%26f%3D0%26g%3D0%26t%3D0%26__ll%3D-1%26_ato%3D0; _m_h5_tk=0e937890cd9649f906fc42399ecd0619_1571199159711; _m_h5_tk_enc=f5980e111c2548eac774f277c6a299eb; cna=QdAqFuF1PicCAXlFUUqkBqIg; uc3=lg2=VFC%2FuZ9ayeYq2g%3D%3D&nk2=2%2FxklMv8n2I%3D&vt3=F8dByuDjOl1yl1GfhSE%3D&id2=UNN0neAo5o5r7Q%3D%3D; lgc=%5Cu9AD8%5Cu9E4F1121; uc4=nk4=0%402ZIB9BtLSyf3btQAtWpfO2ozqQ%3D%3D&id4=0%40UgQ8do8RTt%2FtvGltJbPYUnvZA4Hz; tracknick=%5Cu9AD8%5Cu9E4F1121; _cc_=VT5L2FSpdA%3D%3D; tg=0; v=0; mt=ci=-1_0; cookie2=17459d4ee48748c26ba23ed7a28bd104; _tb_token_=e6860ee763ae8; l=dB_92FIHq-xrjL2CKOfwVZO1Br791IRb4sPPhlVQNICPOlCe54KAWZIr05YwCnGVh6DeJ37NvzW0BeYBqhX_oojsq61NoCMmn; isg=BGJi3ZmOSxvM4VdFxAsiIkM4s-jOuE9L36E_caz7bVWAfwL5lUJo3Yv1r_sm795l; uc1=cookie14=UoTbnKISXqC3Bw%3D%3D';
//            $descurlRes = $this->get_taobao_ihttp_request($descurl, $cookie);

//            $descurlRes = ihttp_get($descurl,$cookie);
            $descurlRes = $this->get_taobao_ihttp_request($descurl, $cookie);
//            $descurlRes = file_get_contents($descurl);

//            if (empty($descurlRes)) {
//                //邮件预警
//                if($_W['config']['setting']['e_waring']['on']){
//                    $subject = '预警邮件--天猫详情抓取失败';
//                    $body = "用户：".$_W['user']['username'] . "<br/>原因：【" . $descurl . "】代理失效，或者天猫升级了<br/>创建时间：" . date('Y-m-d H:i:s',time()) . "<br/>来源：商城淘宝抓取<br/>" ;
//                    $toUser = $_W['config']['setting']['e_waring']['to'];
//                    ihttp_email_for_ewaring($toUser,$subject,$body);
//                }
//            }
            $descContent = $descurlRes;
            if(function_exists('mb_convert_encoding')){
                $descContent = mb_convert_encoding($descContent,'UTF-8','GBK');
            }
            $descContent = str_replace( "\n", '',$descContent);
            preg_match("/^var desc='(.*)';$/", $descContent ,$desc);
            $descContent = $desc[1];
        }
        $item['content'] = $descContent;
        print_r($item);die;
        return $this->save_taobao_goods($item, $taobaourl);
    }

    public function get_taobao_ihttp_request($url, $cookie) {
        // 要访问的目标页面
        $targetUrl = $url;
        // 代理服务器
//        $proxyServer = "http://http-dyn.abuyun.com:9020";
//        // 隧道身份信息
//        $proxyUser   = "H4S2875R86JL6U4D";
//        $proxyPass   = "EFF8A9C611364A51";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $targetUrl);

        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_COOKIE, $cookie);

        // 设置代理服务器
//        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
//        curl_setopt($ch, CURLOPT_PROXY, $proxyServer);

        // 设置隧道验证信息
//        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
//        curl_setopt($ch, CURLOPT_PROXYUSERPWD, "{$proxyUser}:{$proxyPass}");

        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;)");

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        //$info = curl_getinfo($ch);

        curl_close($ch);

        return $result;
    }

    public function get_tmall_page_url($itemid)
    {
        $url = 'https://detail.tmall.com/item.htm?id=' . $itemid;
        $url = $this->getRealURL($url);
        return $url;
    }
    public function getRealURL($url)
    {
        if (function_exists('stream_context_set_default'))
        {
            stream_context_set_default(array( 'http' => array('method' => 'HEAD') ));
        }
        $header = get_headers($url, 1);
        if (strpos($header[0], '301') || strpos($header[0], '302'))
        {
            if (is_array($header['Location']))
            {
                return $header['Location'][count($header['Location']) - 1];
            }
            return $header['Location'];
        }
        return $url;
    }




}