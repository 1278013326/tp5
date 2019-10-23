<?php
namespace app\peng\controller;
use DOMDocument;
use app\peng\controller\TaobaoBase;

class Taobao extends TaobaoBase
{
    private $num = 0;


    /**
     * 京东商品规格抓取xpath地址
     */
    const JD_xpath_params = ['//*[@id="detail"]/div[2]/div[1]/div[1]/ul[3]/li',
        '//*[@id="detail"]/div[2]/div[1]/div[1]/ul[2]/li',
        '//*[@id="item-detail"]/div[1]/ul/li'];


    /**
     * 京东商品标题抓取xpath地址
     */
    const JD_xpath_title = '//*[@class="sku-name"]';


    /**
     * 京东商品展示图抓取正则
     */
    const JD_pattern_img = "#imageList:\s\[(.*)\]#";

    /**
     * 京东海豚全球xpath抓取正则
     */

    const JD_xpath_hk_img = "//*[@id=\"spec-list\"]/ul/li";

    /**
     * 京东商品详情xpath地址
     */
    const JD_xpath_content = '//*[@id="J-detail-content"]/table';
    const JD_pattern_content = "#<div.*id=\"J-detail-content\".*>[\s\S]+<!-- #J-detail-content -->#";

    /**
     * 商品sku抓取地址
     */
    const JD_xpath_specs = '//*[@id="choose-attrs"]';

    //抓取视图
    public function index()
    {
        return view('index');
    }
    /**
     * 获取抓取url
     */
    public function getUrl($url){
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
        return $itemid;
    }
    /**
     * 天猫抓取
     */
    public function tianmao($itemid = '', $taobaourl = '', $cates = '', $merchid = 0) {
        $url = 'https://detail.tmall.com/item.htm?spm=a220m.1000858.1000725.13.64933ec2swDDSb&id=603508410508&skuId=4225043624842&areaId=110100&user_id=2177037697&cat_id=2&is_b=1&rn=07d58050093c5c45d7398a960da0be06';
        $itemid = $this->getUrl($url);

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
   /**
    * 淘宝抓取
    */
    public function taobao($itemid = '', $taobaourl = '', $cates = '', $merchid = 0)
    {
        $url = 'https://item.taobao.com/item.htm?spm=2013.1.20141001.3.1866208c1sCYJF&id=544769715015&scm=1007.12144.97955.42296_0_0&pvid=1b4a4c72-2909-4b1a-b01c-f60f678e66af&utparam=%7B%22x_hestia_source%22%3A%2242296%22%2C%22x_object_type%22%3A%22item%22%2C%22x_mt%22%3A0%2C%22x_src%22%3A%2242296%22%2C%22x_pos%22%3A3%2C%22x_pvid%22%3A%221b4a4c72-2909-4b1a-b01c-f60f678e66af%22%2C%22x_object_id%22%3A544769715015%7D';
        $itemid = $this->getUrl($url);
//        global $_W;
        error_reporting(0);
//        $g = pdo_fetch('select * from ' . tablename('ewei_shop_goods') . ' where uniacid=:uniacid and merchid=:merchid and catch_id=:catch_id and catch_source=\'taobao\' limit 1', array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid, ':catch_id' => $itemid));
        $item = array();
//        $item['id'] = $g['id'];
        $item['merchid'] = $merchid;
        if (!(empty($merchid)))
        {
            if (empty($_W['merch_user']['goodschecked']))
            {
                $item['checked'] = 1;
            }
            else
            {
                $item['checked'] = 0;
            }
        }
        $url = $this->get_taobao_page_url($itemid);
//        print_r($url);die;
//        $url = $this->get_tmall_page_url($itemid);
//        load()->func('communication');
        $response = ihttp_get($url);
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
        if (strexists($response['content'], 'ERRCODE_QUERY_DETAIL_FAIL'))
        {
            return array('result' => '0', 'error' => '宝贝不存在!');
        }
        $dom = new DOMDocument();
        $dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' . $content);
        $xml = simplexml_import_dom($dom);

        preg_match('/var g_config\\s*=(.*);/isU', $content, $match);
        $matchOne = str_replace(array(' ', "\r", "\n", "\t"), array(''), $match[1]);
        $erdr = substr($matchOne, stripos($matchOne, 'sibUrl'));
        $erdr2 = substr($erdr, 0, stripos($erdr, 'descUrl'));
        $asd = explode(':', $erdr2);
        $two = substr($asd[1], 1);
        $threeUrl = substr($two, 0, -2);
        $detailskip = ihttp_request('https:' . $threeUrl, '', array('referer' => 'https://item.taobao.com?id=' . $itemid, 'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8', 'accept-encoding' => '', 'accept-language' => 'zh-CN,zh;q=0.9,en;q=0.8', 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36', 'CURLOPT_USERAGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36'));
        $detailskip = json_decode($detailskip['content'], true);
        $stockArray = array();
        if (($detailskip['code']['code'] == 0) && ($detailskip['code']['message'] == 'SUCCESS'))
        {
            $stockArray = $detailskip['data']['dynStock']['sku'];
        }
        $specifications = $xml->xpath('//*[@id="J_isku"]/div/dl/dd/ul');
        $specificationsArray = array();
        $guigeArr = array();
        foreach ($specifications as $key => $specificationsInfo )
        {
            $sizeArray = (array) $specificationsInfo;
            $sizeAttributesArray = explode(':', $sizeArray['@attributes']['data-property']);
            $specificationsArray[$key]['title'] = $sizeAttributesArray[0];
            $sizeLiArray = $sizeArray['li'];
            if (!(is_object($sizeLiArray)))
            {
                $specificationsArray[$key]['itemsCount'] = count($sizeLiArray);
                foreach ($sizeLiArray as $j => $sizeLiInfo )
                {
                    $sizeLiInfoArray = (array) $sizeLiInfo;
                    $guigeArr[$key][$j][] = ';' . $sizeLiInfoArray['@attributes']['data-value'];
                    $sizeLiInfoAttributesArray = explode(':', $sizeLiInfoArray['@attributes']['data-value']);
                    $specificationsArray[$key]['propId'] = $sizeLiInfoAttributesArray[0];
                    $specificationsArray[$key]['items'][$j]['valueId'] = $sizeLiInfoAttributesArray[1];
                    $sizeLiInfoA = (array) $sizeLiInfoArray['a'];
                    $specificationsTitle = (array) $sizeLiInfoA['span'];
                    $specificationsArray[$key]['items'][$j]['title'] = $specificationsTitle[0];
                    $guigeArr[$key][$j][] = $specificationsTitle[0];
                    $sizeLiInfoAttr = $sizeLiInfoA['@attributes'];
                    if (!(empty($sizeLiInfoAttr['style'])))
                    {
                        $sizeLiInfoAttrStyle = substr($sizeLiInfoAttr['style'], stripos($sizeLiInfoAttr['style'], '//'));
                        $sizeLiInfoAttrStyleUrl = substr($sizeLiInfoAttrStyle, 0, stripos($sizeLiInfoAttrStyle, ')'));
                        $thumb = mb_substr($sizeLiInfoAttrStyleUrl, 0, strpos($sizeLiInfoAttrStyleUrl, '_30x30.jpg'));
                        $specificationsArray[$key]['items'][$j]['thumb'] = 'http:' . $thumb;
                    }
                    else
                    {
                        $specificationsArray[$key]['items'][$j]['thumb'] = '';
                    }
                }
            }
            else
            {
                $objsctArr = (array) $sizeLiArray;
                $specificationsArray[$key]['itemsCount'] = 1;
                $objsctArrAttributes = explode(':', $objsctArr['@attributes']['data-value']);
                $specificationsArray[$key]['propId'] = $objsctArrAttributes[0];
                $specificationsArray[$key]['items'][0]['valueId'] = $objsctArrAttributes[1];
                $sizeLiInfoA = (array) $objsctArr['a'];
                $specificationsTitle = (array) $sizeLiInfoA['span'];
                $specificationsArray[$key]['items'][0]['title'] = $specificationsTitle[0];
                $guigeArr[$key][0][] = ';' . $objsctArr['@attributes']['data-value'];
                $guigeArr[$key][0][] = $specificationsTitle[0];
                $sizeLiInfoAttr = $sizeLiInfoA['@attributes'];
                if (!(empty($sizeLiInfoAttr['style'])))
                {
                    $sizeLiInfoAttrStyle = substr($sizeLiInfoAttr['style'], stripos($sizeLiInfoAttr['style'], '//'));
                    $sizeLiInfoAttrStyleUrl = substr($sizeLiInfoAttrStyle, 0, stripos($sizeLiInfoAttrStyle, ')'));
                    $thumb = mb_substr($sizeLiInfoAttrStyleUrl, 0, strpos($sizeLiInfoAttrStyleUrl, '_30x30.jpg'));
                    $specificationsArray[$key]['items'][0]['thumb'] = 'http:' . $thumb;
                }
                else
                {
                    $specificationsArray[$key]['items'][0]['thumb'] = '';
                }
            }
        }
        $item['specs'] = $this->my_sort($specificationsArray, 'itemsCount', SORT_ASC, SORT_STRING);
        $count = count($guigeArr);
        if ($count == 1)
        {
            $i = 0;
            while ($i < count($guigeArr[0]))
            {
                $value = $guigeArr[0][$i][0];
                $title = $guigeArr[0][$i][1];
                $arr[] = $value . ';|' . $title;
                ++$i;
            }
        }
        else if ($count == 2)
        {
            $i = 0;
            while ($i < count($guigeArr[0]))
            {
                $value = $guigeArr[0][$i][0];
                $title = $guigeArr[0][$i][1];
                $j = 0;
                while ($j < count($guigeArr[1]))
                {
                    $valueTwo = $value . $guigeArr[1][$j][0];
                    $titleTwo = $title . '+' . $guigeArr[1][$j][1];
                    $arr[] = $valueTwo . ';|' . $titleTwo;
                    ++$j;
                }
                ++$i;
            }
        }
        else if ($count == 3)
        {
            $i = 0;
            while ($i < count($guigeArr[0]))
            {
                $value = $guigeArr[0][$i][0];
                $title = $guigeArr[0][$i][1];
                $j = 0;
                while ($j < count($guigeArr[1]))
                {
                    $valueTwo = $value . $guigeArr[1][$j][0];
                    $titleTwo = $title . '+' . $guigeArr[1][$j][1];
                    $g = 0;
                    while ($g < count($guigeArr[2]))
                    {
                        $valueThree = $valueTwo . $guigeArr[2][$g][0];
                        $titleThree = $titleTwo . '+' . $guigeArr[2][$g][1];
                        $arr[] = $valueThree . ';|' . $titleThree;
                        ++$g;
                    }
                    ++$j;
                }
                ++$i;
            }
        }
        else if ($count == 4)
        {
            $i = 0;
            while ($i < count($guigeArr[0]))
            {
                $value = $guigeArr[0][$i][0];
                $title = $guigeArr[0][$i][1];
                $j = 0;
                while ($j < count($guigeArr[1]))
                {
                    $valueTwo = $value . $guigeArr[1][$j][0];
                    $titleTwo = $title . '+' . $guigeArr[1][$j][1];
                    $g = 0;
                    while ($g < count($guigeArr[2]))
                    {
                        $valueThree = $valueTwo . $guigeArr[2][$g][0];
                        $titleThree = $titleTwo . '+' . $guigeArr[2][$g][1];
                        $r = 0;
                        while ($r < count($guigeArr[3]))
                        {
                            $valueFour = $valueThree . $guigeArr[3][$r][0];
                            $titleFour = $titleThree . '+' . $guigeArr[3][$r][1];
                            $arr[] = $valueFour . ';|' . $titleFour;
                            ++$r;
                        }
                        ++$g;
                    }
                    ++$j;
                }
                ++$i;
            }
        }
        else if ($count == 5)
        {
            $i = 0;
            while ($i < count($guigeArr[0]))
            {
                $value = $guigeArr[0][$i][0];
                $title = $guigeArr[0][$i][1];
                $j = 0;
                while ($j < count($guigeArr[1]))
                {
                    $valueTwo = $value . $guigeArr[1][$j][0];
                    $titleTwo = $title . '+' . $guigeArr[1][$j][1];
                    $g = 0;
                    while ($g < count($guigeArr[2]))
                    {
                        $valueThree = $valueTwo . $guigeArr[2][$g][0];
                        $titleThree = $titleTwo . '+' . $guigeArr[2][$g][1];
                        $r = 0;
                        while ($r < count($guigeArr[3]))
                        {
                            $valueFour = $valueThree . $guigeArr[3][$g][0];
                            $titleFour = $titleThree . '+' . $guigeArr[3][$g][1];
                            $t = 0;
                            while ($t < count($guigeArr[4]))
                            {
                                $valueFive = $valueFour . $guigeArr[4][$t][0];
                                $titleFive = $titleFour . '+' . $guigeArr[4][$t][1];
                                $arr[] = $valueFive . ';|' . $titleFive;
                                ++$t;
                            }
                            ++$r;
                        }
                        ++$g;
                    }
                    ++$j;
                }
                ++$i;
            }
        }
        else if ($count == 6)
        {
            $i = 0;
            while ($i < count($guigeArr[0]))
            {
                $value = $guigeArr[0][$i][0];
                $title = $guigeArr[0][$i][1];
                $j = 0;
                while ($j < count($guigeArr[1]))
                {
                    $valueTwo = $value . $guigeArr[1][$j][0];
                    $titleTwo = $title . '+' . $guigeArr[1][$j][1];
                    $g = 0;
                    while ($g < count($guigeArr[2]))
                    {
                        $valueThree = $valueTwo . $guigeArr[2][$g][0];
                        $titleThree = $titleTwo . '+' . $guigeArr[2][$g][1];
                        $r = 0;
                        while ($r < count($guigeArr[3]))
                        {
                            $valueFour = $valueThree . $guigeArr[3][$g][0];
                            $titleFour = $titleThree . '+' . $guigeArr[3][$g][1];
                            $t = 0;
                            while ($t < count($guigeArr[4]))
                            {
                                $valueFive = $valueFour . $guigeArr[4][$t][0];
                                $titleFive = $titleFour . '+' . $guigeArr[4][$t][1];
                                $k = 0;
                                while ($k < count($guigeArr[5]))
                                {
                                    $valueSix = $valueFive . $guigeArr[5][$k][0];
                                    $titleSix = $titleFive . '+' . $guigeArr[5][$k][1];
                                    $arr[] = $valueSix . ';|' . $titleSix;
                                    ++$k;
                                }
                                ++$t;
                            }
                            ++$r;
                        }
                        ++$g;
                    }
                    ++$j;
                }
                ++$i;
            }
        }
        $item['options'] = array();
        $item['total'] = 0;
        foreach ($arr as $key => $asdInfo )
        {
            $asdInfoArrAs = explode('|', $asdInfo);
            $asdInfoArr = explode(';', $asdInfoArrAs[0]);
            $asdInfoArr = array_filter($asdInfoArr);
            $j = 0;
            foreach ($asdInfoArr as $asdInfoArrInfo )
            {
                $asdInfoArrInfoArr = explode(':', $asdInfoArrInfo);
                $item['options'][$key]['option_specs'][$j]['propId'] = $asdInfoArrInfoArr[0];
                $item['options'][$key]['option_specs'][$j]['valueId'] = $asdInfoArrInfoArr[1];
                ++$j;
            }
            if (!(empty($stockArray[$asdInfoArrAs[0]])))
            {
                $item['options'][$key]['stock'] = $stockArray[$asdInfoArrAs[0]]['stock'];
                $item['total'] = $item['total'] + $stockArray[$asdInfoArrAs[0]]['stock'];
            }
            else
            {
                $item['options'][$key]['stock'] = 0;
            }
            $item['options'][$key]['title'] = explode('+', $asdInfoArrAs[1]);
            $item['options'][$key]['marketprice'] = $detailskip['data']['price'];
        }
        $prodectNameContent = $xml->xpath('//*[@id="J_Title"]');
        $titleArr = (array) $prodectNameContent[0];
        $item['title'] = trim(strval($titleArr['h3']));
        $prodectDescContent = $xml->xpath('//div/div/div/div/div/div/div/div/div/div/div[1]');
        $item['subTitle'] = trim(strval($prodectDescContent[1]->p));
        $prodectPrice = $xml->xpath('//*[@id="J_StrPrice"]');
        $prodectPriceArr = (array) $prodectPrice[0];
        $taoBaoPrice = trim(strval($prodectPriceArr['em'][1]));
        $taoBaoPriceArr = explode('-', $taoBaoPrice);
        $item['productPrice'] = $taoBaoPriceArr[0];
        $imgs = array();
        $i = 1;
        while ($i < 6)
        {
            $img = $xml->xpath('//*[@id="J_UlThumb"]/li[' . $i . ']');
            if (!(empty($img)))
            {
                $img = strval($img[0]->div->a->img['data-src']);
                $img = mb_substr($img, 0, strpos($img, '_50x50.jpg'));
                $imgArr = explode(':', $img);
                if (count($imgArr) == 2)
                {
                    $img = 'http:' . $imgArr[1];
                }
                else
                {
                    $img = 'http:' . $imgArr[0];
                }
                $imgs[] = $img;
            }
            ++$i;
        }
        $item['pics'] = $imgs;
        $paramsContent = $xml->xpath('//*[@id="attributes"]');
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
        if (is_array($cates))
        {
            foreach ($cates as $key => $cid )
            {
                $c = pdo_fetch('select level from ' . tablename('ewei_shop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $cid, ':uniacid' => $_W['uniacid']));
                if ($c['level'] == 1)
                {
                    $pcates[] = $cid;
                }
                else if ($c['level'] == 2)
                {
                    $ccates[] = $cid;
                }
                else if ($c['level'] == 3)
                {
                    $tcates[] = $cid;
                }
                if ($key == 0)
                {
                    if ($c['level'] == 1)
                    {
                        $pcateid = $cid;
                    }
                    else if ($c['level'] == 2)
                    {
                        $crow = pdo_fetch('select parentid from ' . tablename('ewei_shop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $cid, ':uniacid' => $_W['uniacid']));
                        $pcateid = $crow['parentid'];
                        $ccateid = $cid;
                    }
                    else if ($c['level'] == 3)
                    {
                        $tcateid = $cid;
                        $tcate = pdo_fetch('select id,parentid from ' . tablename('ewei_shop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $cid, ':uniacid' => $_W['uniacid']));
                        $ccateid = $tcate['parentid'];
                        $ccate = pdo_fetch('select id,parentid from ' . tablename('ewei_shop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $ccateid, ':uniacid' => $_W['uniacid']));
                        $pcateid = $ccate['parentid'];
                    }
                }
            }
        }
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
        preg_match("/descnew\.taobao\.com\/([^\"]*)['\"], /",$content,$descUrlMatch);
        $descContent = '';
        if(!empty($descUrlMatch) && !is_array($descUrlMatch[1])){
            $descurl  = $descUrlMatch[1];
            $descurl = "https://descnew.taobao.com/".$descurl;
            $cookie = 't=39b2ec6263aede3375966903dd01ba9f; thw=cn; hng=CN%7Czh-CN%7CCNY%7C156; enc=kyJ3xykdJBDYrDMUB7wJ4%2FZDufrDcneIVIJqi7OlKROENc9IjlgjNyes%2Fs2M0m4288l2zN6LeWsWOYRXU4HQzw%3D%3D; x=e%3D1%26p%3D*%26s%3D0%26c%3D0%26f%3D0%26g%3D0%26t%3D0%26__ll%3D-1%26_ato%3D0; _m_h5_tk=0e937890cd9649f906fc42399ecd0619_1571199159711; _m_h5_tk_enc=f5980e111c2548eac774f277c6a299eb; cna=QdAqFuF1PicCAXlFUUqkBqIg; uc3=lg2=VFC%2FuZ9ayeYq2g%3D%3D&nk2=2%2FxklMv8n2I%3D&vt3=F8dByuDjOl1yl1GfhSE%3D&id2=UNN0neAo5o5r7Q%3D%3D; lgc=%5Cu9AD8%5Cu9E4F1121; uc4=nk4=0%402ZIB9BtLSyf3btQAtWpfO2ozqQ%3D%3D&id4=0%40UgQ8do8RTt%2FtvGltJbPYUnvZA4Hz; tracknick=%5Cu9AD8%5Cu9E4F1121; _cc_=VT5L2FSpdA%3D%3D; tg=0; v=0; mt=ci=-1_0; cookie2=17459d4ee48748c26ba23ed7a28bd104; _tb_token_=e6860ee763ae8; l=dB_92FIHq-xrjL2CKOfwVZO1Br791IRb4sPPhlVQNICPOlCe54KAWZIr05YwCnGVh6DeJ37NvzW0BeYBqhX_oojsq61NoCMmn; isg=BGJi3ZmOSxvM4VdFxAsiIkM4s-jOuE9L36E_caz7bVWAfwL5lUJo3Yv1r_sm795l; uc1=cookie14=UoTbnKISXqC3Bw%3D%3D';
            $descurlRes = $this->get_taobao_ihttp_request($descurl, $cookie);
//            $responsedesc = ihttp_get($descurl);
            if (empty($descurlRes)) {

                //邮件预警
//                if($_W['config']['setting']['e_waring']['on']){
//                    $subject = '预警邮件--淘宝详情抓取失败';
//                    $body = "用户：".$_W['user']['username'] . "<br/>原因：【" . $descurl . "】代理失效，或者淘宝升级了<br/>创建时间：" . date('Y-m-d H:i:s',time()) . "<br/>来源：商城淘宝抓取<br/>" ;
//                    $toUser = $_W['config']['setting']['e_waring']['to'];
//                    ihttp_email_for_ewaring($toUser,$subject,$body);
//                }
            }
            $descContent = $descurlRes;
            if(function_exists('mb_convert_encoding')){
                $descContent = mb_convert_encoding($descContent,'UTF-8','GBK');
            }
            $descContent = str_replace( "\n", '',$descContent);
            preg_match("/^var desc='(.*)';$/", $descContent ,$desc);
            $descContent = $desc[1];
            $descContent = str_replace("\\","",$descContent);
            $item['content'] = $descContent;
        }
        print_r($item);die;
        return $this->save_taobao_goods($item, $taobaourl);
    }

    /**
     * 淘宝排序
     * @param $arrays
     * @param $sort_key
     * @param int $sort_order
     * @param int $sort_type
     * @return bool
     */
    public function my_sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
    {
        if (is_array($arrays))
        {
            foreach ($arrays as $array )
            {
                if (is_array($array))
                {
                    $key_arrays[] = $array[$sort_key];
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }
   /**
    * 京东抓取
    */
    public function jingdong() {
        set_time_limit(0);
        $ret = array();
        $url = 'https://item.jd.com/57609101732.html';
//        $cates = $_GPC['cate'];

        if (is_numeric($url)) {
            $itemid = $url;
        }
        else {
            preg_match('/(\\d+).html/i', $url, $matches);

            if (isset($matches[1])) {
                $itemid = $matches[1];
            }
        }

        if (empty($itemid)) {
            exit(json_encode(array('result' => 0, 'error' => '未获取到 itemid!')));
        }

        $this->get_item_jingdong($itemid, $url, $cates='');
    }

    /**
     * 京东
     * @param string $itemid
     * @param string $jingdongurl
     * @param string $cates
     * @param int $merchid
     * @return array
     */
    public function get_item_jingdong($itemid = '', $jingdongurl = '', $cates = '', $merchid = 0)
    {
        error_reporting(0);
        global $_W;
//        $g = pdo_fetch('select * from ' . tablename('ewei_shop_goods') . ' where uniacid=:uniacid and merchid=:merchid and catch_id=:catch_id and catch_source=\'jingdong\' limit 1', array(':uniacid' => $_W['uniacid'], ':catch_id' => $itemid, ':merchid' => $merchid));
        $item = array();
//        $item['id'] = $g['id'];
        $item['merchid'] = $merchid;
        if (!(empty($merchid)))
        {
            if (empty($_W['merch_user']['goodschecked']))
            {
                $item['checked'] = 1;
            }
            else
            {
                $item['checked'] = 0;
            }
        }
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
        $item['itemId'] = $itemid;
        $item['total'] = 10;
        $item['sales'] = 0;
        $priceurl = $this->get_jingdong_price_url($itemid);
        $responsePrice = ihttp_get($priceurl);
        $contentePrice = $responsePrice['content'];
        if (empty($contentePrice))
        {
            return array('result' => '0', 'error' => '未从京东获取到商品信息!');
        }
        $price = json_decode($contentePrice, true);
        $item['marketprice'] = $price[0]['p'];
        $url = $this->get_jingdong_detail_url($itemid);
        $responseDetail = ihttp_get($url);
        $contenteDetail = $responseDetail['content'];
        $details = json_decode($contenteDetail, true);
        $item['title'] = $details['ware']['wname'];
        $pics = array();
        $imgurls = $details['ware']['images'];
        foreach ($imgurls as $imgurl )
        {
            if (count($pics) < 4)
            {
                if (count($pics) == 0)
                {
                    $iurl = $imgurl['bigpath'];
                    if (stripos($iurl, '//') == 0)
                    {
                        $iurl .= 'http:' . $iurl;
                    }
                    $pics[] = $iurl;
                }
                else
                {
                    $iurl = $imgurl['bigpath'];
                    if (stripos($iurl, '//') == 0)
                    {
                        $iurl .= 'http:' . $iurl;
                    }
                    $pics[] = $iurl;
                }
            }
        }
        $item['pics'] = $pics;
        $specs = array();
        $prodectContent = $details['wdis'];
        $prodectContent = strval($prodectContent);
        $prodectContent = $this->contentpasswh($prodectContent);
        $item['content'] = $prodectContent;
        $params = array();
        $pr = $details['ware']['wi']['code'];
        $pr = json_decode($pr, 1);
        foreach ($pr as $value )
        {
            foreach ($value as $key => $val )
            {
                if (is_array($val))
                {
                    $paramsValue = '';
                    foreach ($val as $v )
                    {
                        foreach ($v as $k1 => $v1 )
                        {
                            if (!(empty($v1)))
                            {
                                $params[] = array('title' => $k1, 'value' => $v1);
                            }
                        }
                    }
                }
                else if (!(empty($val)))
                {
                    $params[] = array('title' => $key, 'value' => $val);
                }
            }
        }
        $item['params'] = $params;


        $item = array_merge($item,$this->get_jd_detail($jingdongurl));
        var_dump($item);die;
        return $this->save_jingdong_goods($item, $jingdongurl);
    }

    /**
     * 京东
     * @param $content
     * @return string|string[]|null
     */
    public function contentpasswh($content)
    {
        $content = preg_replace('/(?:width)=(\'|").*?\\1/', ' width="100%"', $content);
        $content = preg_replace('/(?:height)=(\'|").*?\\1/', ' ', $content);
        $content = preg_replace('/(?:max-width:\\s*\\d*\\.?\\d*(px|rem|em))/', '', $content);
        $content = preg_replace('/(?:max-height:\\s*\\d*\\.?\\d*(px|rem|em))/', '', $content);
        $content = preg_replace('/(?:min-width:\\s*\\d*\\.?\\d*(px|rem|em))/', ' ', $content);
        $content = preg_replace('/(?:min-height:\\s*\\d*\\.?\\d*(px|rem|em))/', ' ', $content);
        return $content;
    }

    /**
     * 京东
     * @param $url
     * @return array|bool
     */
    public function get_jd_detail($url){


        $url_components = parse_url($url);

        $response = ihttp_get($url);
        $html = $response['content'];
        if(function_exists("mb_convert_encoding")){
            $html = mb_convert_encoding($html,"UTF-8","UTF-8,GBK,GB2312,BIG5");
        }

        if (strexists($response['content'], 'ERRCODE_QUERY_DETAIL_FAIL'))
        {
            return false;
        }

        $dom = new DOMDocument();
        $dom -> loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' . $html);
        $xml = simplexml_import_dom($dom);


        //商品标题抓取
        $_title = $xml->xpath(self::JD_xpath_title);

        $_title = $_title[0];
        $_title = $_title->__toString();


        $title = trim($_title);

        //商品规格抓取
        $_params_dom_arr = array();
        //不同站点存在不同层级结构
        foreach (self::JD_xpath_params as $JD_xpath_param){
            if(!empty($_params_dom_arr = $xml->xpath($JD_xpath_param))){
                break;
            }
        }
        $_params = [];
        while(list(,$node) = each($_params_dom_arr)){
            if(!empty($node)){
                $paramitem = trim(str_replace('：', ':', $node->__toString() ));
                $p1 = mb_strpos($paramitem, ':');
                $ptitle = mb_substr($paramitem, 0, $p1);
                $pvalue = mb_substr($paramitem, $p1 + 1, mb_strlen($paramitem));
                $_params[] = array('title' => $ptitle, 'value' => $pvalue);
            }
        }
        $params = $_params;

        //商品图片抓取
        if(strpos($url_components['host'],'.hk') !== false){
            $_pics = [];
            $_pics_dom = $xml->xpath(self::JD_xpath_hk_img);
            foreach ($_pics_dom as $_pic_dom){
                $_pic_src = $this->_tag_attribute($_pic_dom->img,'data-url');
                $_pic_src = $this->get_jd_image_url($_pic_src);
                $_pics[] = $_pic_src;
            }

        }else{
            preg_match(self::JD_pattern_img,$html,$_pics_);
            $_pics_arr = explode(',',$_pics_[1]);
            $_pics = $this->get_jd_image_url($_pics_arr);
        }

        $pics = $_pics;

        //商品内容抓取一
//        preg_match(self::JD_pattern_content,$html,$_content);
//        $_content = str_replace("data-lazyload",'src',$_content[0]);
//        //切换图片地址
//        $_content = str_replace('background-image:url(','background-image:url(https:',$_content);
//        $content  = htmlentities($_content,ENT_QUOTES,"UTF-8");


        //商品内容抓取二
        preg_match("/desc:[^']*'([^\']*)'.*,?/",$html,$_desc);
        $_content = '';
        if(!empty($_desc[1]) && !is_array($_desc[1]))
        {
            $_desc_url = $url_components['scheme'].':'.$_desc[1];

            $_desc_response = ihttp_get($_desc_url);

            if($_desc_response['code'] == 200)
            {
                $_content = $_desc_response['content'];

                if(function_exists('mb_convert_encoding')){
                    $_content = mb_convert_encoding($_content,'UTF-8','GBK');
                }
                preg_match("/^showdesc\((.*)\)$/",$_content,$_desc_content) ;

                if(!empty($_desc_content[1]) && !is_array($_desc_content[1]))
                {
                    $_content = $_desc_content[1];
                }
                //加载json对象
                $contentObj = json_decode($_content);

                if(is_object($contentObj) && property_exists($contentObj, 'content'))
                {
                    $_content = $contentObj->content;
                    //切换图片地址和加载方式
                    $_content = str_replace("data-lazyload",'src',$_content);
//                    $_content = str_replace('background-image:url(','background-image:url(https:',$_content);


                    //切换内部样式到行内样式
                    preg_match_all("/(\.M[0-9]+){(.*)}/", $_content,$_matchStyle);
                    if(!empty($_matchStyle[1]) && !empty($_matchStyle[2]) ){
                        $_content =preg_replace("/data-id=['    \"](M[0-9]+)[\'\"]/", "style='.\\1'", $_content);
                        $_content =preg_replace("/animate-(M[0-9]+)[\'\"]/", "\" style='.\\1'", $_content);
                        //剔除多余的内部样式
                        $_content = preg_replace("/<style>[\s\S]*<\/style>/", '', $_content);
                        $_content =str_replace($_matchStyle[1], $_matchStyle[2], $_content);
                    }

                }

            }
        }

        $content = $this->contentpasswh($_content);

        //商品sku抓取
        $_specsDom = $xml->xpath(self::JD_xpath_specs);
        $_specs = [];
        //attr层计数
        $_i_attr = 0;
        //商品规格层
        foreach ($_specsDom[0]->children() as $i => $_attr){
            //非规格层忽略
            if(empty($this->_tag_attribute($_attr,'data-type'))){
                continue;
            }
            //规格名称
            $_specs[$_i_attr]['title'] =  $this->_tag_attribute($_attr,'data-type');
            $_specs[$_i_attr]['itemcount'] = $_attr->div[1]->count();
            //商品同种属性层
            foreach($_attr->children() as $_items) {
                //具体商品计数
                $_i_item = 0;
                //具体商品层
                foreach ($_items->children() as $_item) {
                    $_specs[$_i_attr]['items'][$_i_item]['valueid'] = $this->_tag_attribute($_item, 'data-sku');

                    //图片是否存在
                    if(!empty($this->_tag_attribute($_item->a->img,'src'))){
                        $_specs[$_i_attr]['items'][$_i_item]['thumb'] = "https:".$this->_tag_attribute($_item->a->img, 'src');
                    }
                    $_specs[$_i_attr]['items'][$_i_item]['title'] = $this->_tag_attribute($_item, 'data-value');
                    $_i_item++;
                }
            }
            $_i_attr++;
        }
        $specs = $_specs;


        if(count($specs)>0){
            $hasoption = true;
        }



        return compact('title','params','content','pics','specs','hasoption','weight');
    }

    /**
     * 京东
     * @param $imgurls
     * @param string $prefix
     * @return array|string
     */
    private function get_jd_image_url($imgurls, $prefix = 'http://img12.360buyimg.com/n1/s450x450_'){

        if(is_array($imgurls)){
            $resurl = array();
            foreach ($imgurls as $imgurl){
                $resurl[] = $prefix.trim($imgurl,'"');
            }
            return $resurl;
        }else{
            return $prefix.trim($imgurls,'"');
        }
    }
    /**
     * 京东获取标签的属性
     * @param $object 标签dom
     * @param $attribute 属性名称
     * @return string   属性值
     */
    private function _tag_attribute($object,$attribute){
        if(isset($object[$attribute]))
            return (string) $object[$attribute];
    }

    /**
     * 天猫代理
     */
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
    /**
     * 天猫url
     */
    public function get_tmall_page_url($itemid)
    {
        $url = 'https://detail.tmall.com/item.htm?id=' . $itemid;
        $url = $this->getRealURL($url);
        return $url;
    }
    /**
     * 淘宝url
     */
    public function get_taobao_page_url($itemid)
    {
        $url = 'https://item.taobao.com/item.htm?id=' . $itemid;
        $url = $this->getRealURL($url);
        return $url;
    }

    /**
     * 京东url
     * @param $itemid
     * @return mixed|string
     */
    public function get_jingdong_price_url($itemid)
    {
        $url = 'https://pe.3.cn/prices/mgets?skuids=' . $itemid;
        $url = $this->getRealURL($url);
        return $url;
    }

    /**
     * 京东详情url
     * @param $itemid
     * @return mixed|string
     */
    public function get_jingdong_detail_url($itemid)
    {
        $url = 'http://item.m.jd.com/ware/detail.json?wareId=' . $itemid;
        $url = $this->getRealURL($url);
        return $url;
    }
    /**
     * 天猫url校验
     */
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