<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 15:40
 */
namespace app\index\model;

use think\Db;
use think\Model;
use think\cache\driver\Redis1 as Redis;
use PDO;

class Articles extends Model
{
    protected $table = 'articles';
    protected $pk = 'id';

    protected $pages = 30;

    protected static $cache_key ='pcgwNewArticle';

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    /**
     * 查询最新六条数据
     * getNewArticle
     * @author 张林
     * @datetime 2018/8/14 T15:50
     *
     * @param    Request     $type
     * @return   DataFormat
     */
    public static function getNewArticle()
    {
//        if($res = Redis::getInstance()->sGet(self::$cache_key)){
//            return json_decode($res, true);
//        }

        //手机部分 展示的数据与pc略有不同
        if (\think\Request::instance()->isMobile()) {
            $res = Db::name('articles')
                ->field('id,name')
                ->where('status', '1')
                ->where('publish_time', '<=', time())
                ->order('publish_time', 'desc')
                ->limit(6)
                ->select();

        } else {
            $config = include 'envdomain/'.get_cfg_var('yaf.environ').'.php';
            $pdo = new PDO('mysql:host=' . $config['hostname'] . ';dbname=' . $config['database'] . '',$config['username'],$config['password']);
            $sql = "select id,title,url from dr_1_news where status=9 order by inputtime desc limit 6";
            $res = $pdo->query($sql)->fetchAll();
        }
        Redis::getInstance()->sSet(self::$cache_key, $res);
        return $res;
    }

    /**
     * 查询不同分类下的前六条数据
     * getCategoryArticle
     * @author 张林
     * @datetime 2018/8/15 T10:13
     *
     * @param    Request     $type
     * @return   DataFormat
     */
    public static function getCategoryArticle($category_id, $limit = 3, $cache_key='pcgwNewArticleByCategory', $outtime=3600, $mobile_data = false)
    {
//        if($res = Redis::getInstance()->sGet($cache_key)){
//            return json_decode($res, true);
//        }

        $articlesObj =  Db::name('articles');

        if ($mobile_data) {
//            mobile
            $res = $articlesObj
                ->field('id,name,img,description,publish_time,category_id,click_num')
                ->where('img', 'neq', '')
                ->where('status', '1')
                ->where('publish_time', '<=', time())
                ->order('create_time', 'desc')
                ->limit($limit)
                ->select();
        } else {
            //pc
            $config = include 'envdomain/'.get_cfg_var('yaf.environ').'.php';
            $pdo = new PDO('mysql:host=' . $config['hostname'] . ';dbname=' . $config['database'] . '',$config['username'],$config['password']);
            $sql = "select * from dr_1_news where catid = " . $category_id . " and status=9 order by updatetime desc limit " . $limit;
            $res = $pdo->query($sql)->fetchAll();
            //首页获取第一个标题的图片
//            if ($res) {
//                $uid = $res[0]['uid'];
//                $news_id = $res[0]['id'];
//                $tableid = (int)substr((string)$uid, -1, 1);
//                $thumbSql = "select * from dr_attachment_".$tableid." where substring_index(related,'-',-1) = ".$news_id;
//                $thumbSqlRes = $pdo->query($thumbSql)->fetchAll();
//                if ($thumbSqlRes) {
//                    $thumb = 'uploadfile/' . $thumbSqlRes[0]['attachment'];
//                    $res[0]['thumb'] = $thumb;
//                }
//            }
            //pc
//            $res = $articlesObj
//                ->field('id,name,img,description,publish_time,category_id,click_num')
//                ->where('category_id', $category_id)
//                ->where('status', '1')
//                ->where('publish_time', '<=', time())
//                ->order('create_time', 'desc')
//                ->limit($limit)
//                ->select();
        }
        Redis::getInstance()->sSet($cache_key, $res,$outtime);
        return $res;
    }

    /**
     * 查询不同分类下的分页数据
     * getCategoryArticlePages
     * @author 张林
     * @datetime 2018/8/15 T14:14
     *
     * @param    Request     $type
     * @return   DataFormat
     */
    public function getCategoryArticlePages($category_id)
    {
        return Db::name('articles')
            ->field('id,name,img,description,author,publish_time,click_num,create_time')
            ->where('category_id', $category_id)
            ->where('status', '1')
            ->where('publish_time', '<=', time())
            ->order('create_time', 'desc')
            ->paginate($this->pages, true);
    }

    /**
     * 推荐文章前三
     * getRecommend
     * @author 张林
     * @datetime 2018/8/15 T18:02
     *
     * @param    Request     $type
     * @return   DataFormat
     */
    public function getRecommend()
    {
        return Db::name('articles')
            ->field('id,name,img')
            ->where('recommend', '1')
            ->where('status', '1')
            ->where('publish_time', '<=', time())
            ->order('create_time', 'desc')
            ->limit(3)
            ->select();
    }

    /**
     * 热门文章前五
     * getHot
     * @author 张林
     * @datetime 2018/8/15 T18:04
     *
     * @param    Request     $type
     * @return   DataFormat
     */
    public function getHot()
    {
        return Db::name('articles')
            ->field('id,name,img,publish_time')
            ->where('status', '1')
            ->where('publish_time', '<=', time())
            ->order(['click_num' => 'desc','create_time' => 'desc'])
            ->limit(5)
            ->select();
    }

    /**
     * 查看文章详情
     * getInfo
     * @author 张林
     * @datetime 2018/8/15 T18:56
     *
     * @param    Request     $type
     * @return   DataFormat
     */
    public function getInfo($id)
    {
        return Db::name('articles')
            ->field('id,name,img,content,img,description,author,publish_time,create_time,click_num,seo_keywords,seo_description')
            ->where('id', $id)
            ->where('status', '1')
            ->where('publish_time', '<=', time())
            ->find();
    }

    /**
     * 查询分类id
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function categoryId($id){
        return Db::name('articles')
            ->field('category_id')
            ->where('id', $id)
            ->find();
    }



    /**
     * 获取每个分类最热的提条新闻
     */
    public static function  getBanererAct($cache_key='BanererActList'){
        if($res = Redis::getInstance()->sGet($cache_key)){
            return json_decode($res, true);
        }
        $cats = Db::name('articles')->field('category_id')->group('category_id')->select();
        $reslist = [];
        foreach ($cats as $v){
            $item = Db::name('articles')->field('category_id,click_num,id')
                ->where('category_id', $v['category_id'])
                ->order('click_num desc')
                ->find();
            $reslist[] = $item;
        }
        Redis::getInstance()->sSet($cache_key, $reslist,300);
        return $reslist;
    }

    /*
     * 新闻详情 实时热点
     */
    public function realtime(){
        $realtime_key='realtime_key';

        if($res = Redis::getInstance()->sGet($realtime_key)){
            return json_decode($res,true);
        }
        $res = Db::name('articles')
            ->field('id,name,img,click_num,create_time')
            ->where('status', '1')
            ->where('publish_time', '<=', time())
            ->order(['create_time'=> 'desc'])
            ->limit(3)
            ->select();

        Redis::getInstance()->sSet($realtime_key,$res,300);

        return $res;

    }
    /*
     * 热门新闻
     */
    public function hotArticle($click_nums){

        $hotArticle=[];
        foreach ($click_nums as $key=>$val){
            $keys = substr($key,8);
            $hotArticle[]=  Db::name('articles')
                ->field('id,name,img,create_time')
                ->where('status', '1')
                ->where('id',$keys)
                ->where('publish_time', '<=', time())
                ->find();
        }
        return $hotArticle;
    }

    /*
     * 相关推荐
     */
    public function recommend($id){

        //查询改文章的对应的关键词
        $res =Db::name('articles')
            ->field('seo_keywords')
            ->where('status', '1')
            ->where('id',$id)
            ->where('publish_time', '<=', time())
            ->find();
        if(empty($res)){
            $relevant=[];
            return $relevant;
        }
        $res=$res['seo_keywords'];//关键词
        if(empty($res)){
            $relevant= [];
            return $relevant;
        }
        $res = explode('，',$res);
        //根据关键词查询相关文章
        $map['status']=array('eq','1');
        $map['id']= array('neq',$id);

        foreach ($res as $key => $val){
            $map['seo_keywords'][] = array('like',"%{$val}%");
        }
        $map['seo_keywords'][]='or';

        $relevant = Db::name('articles')
            ->field('id,name,create_time,seo_keywords')
            ->where($map)
            //->where(['seo_keywords'=>['neq','']])
            ->order(['create_time desc'])
            ->limit(10)
            // ->fetchSql()
            ->select();

        return $relevant;

    }


    /*
     * 广告位
     */
    public function advertisement(){

        return  Db::name('advert')
            ->field('id,img,url')
            ->where(['status'=>1])
            ->limit(1)
            ->find();

    }

    /**
     * @param $id
     * 上一篇
     */
    public function prevArticle($id){
        $prevArticle =  Db::name('articles')
            ->field(['id','name'])
            ->where('status', '1')
            ->where('id','<' ,$id)
            ->order('id desc')
            ->limit(1)
            ->find();

        return $prevArticle;

    }

    /**
     * @param $id
     * 下一篇
     */
    public function nextArticle($id){
        $nextArticle = Db::name('articles')
            ->field(['id','name'])
            ->where('status', '1')
            ->where('id','>',$id)
            ->order('id asc')
            ->limit(1)
            ->find();

        return $nextArticle;

    }

    /**
     * 微尘小程序资讯
     * @param $class_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInformation($class_id){

        $information =  Db::name('articles')
            ->field(['id','name','publish_time'])
            ->where('status', '1')
            ->where('category_id',$class_id)
            ->order('publish_time desc')
            ->limit(5)
            ->select();
        return $information;

    }
}