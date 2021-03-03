<?php


namespace app\peng\controller;


use think\Request;

class RedisOperate extends Base
{
    //链接redis
    public function index() {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
//        $redis->del("tutorial-list"); //删除指定的key值    cmd中flushall清楚所有的key值
//        $arList = $redis->keys("*"); //获取所有的key值
//        print_r($arList);die;
        return $redis;
    }
    //字符串
    public function string() {
        $redis = $this->index();
        //设置键值：成功返回true，否则返回false，键值不存在则新建，否则覆盖
        $redis->set('string', 'hello world!');

//从左往右第五个字符开始替换为另一指定字符串，成功返回替换后新字符串的长度。
        $redis->setRange('string',6, '1111');//  hello world!的长度

//截取字符串里指定key对应的value里的第一个到第七个字符。
        $redis->getRange('string', 0, 6);

//添加键，返回旧键值：若key不存在则创建键值，返回false
        $redis->getSet('ad', 'hi man');

//一次设置多个键值对：成功返回true
        $redis->mset(['name' => 'jet', 'age' => 18]);

//一次获取多个key的值：返回一个键值对数组，其中不存在的key值为false。
        $redis->mget(['name', 'age']);

//创建一个具有时间限制的键值，过期则删除，秒为单位，成功返回true
        $redis->setex('name', 10, 'jetwu');

//创建一个具有时间限制的键值，过期则删除，毫秒为单位，成功返回true
        $redis->psetex('name', 10, 'jetwu');

//key的值不存在时，添加key并返回true，key存在返回false。
        $redis->setnx('name', 'boby');

//setnx命令的批量操作。只有在给定所有key都不存在的时候才能设置成功，只要其中一个key存在，所有key都无法设置成功。
        $redis->msetnx(['name' => '11', 'name1' => '22']);

//获取指定key存储的字符串的长度，key不存在返回0，不为字符串返回false。
        $redis->strlen('name');

//将指定key存储的数字值增加1。若key不存在会先初始化为0再增加1，若key存储的不是整数值则返回false。成功返回key新值。
        $redis->incr('name');

//给指定key存储的数字值增加指定增量值。
        $redis->incrBy('age', 10);

//给指定key存储的数字值增加指定浮点数增量。
        $redis->incrByFloat('age', 1.5);

//将指定key存储的数字值减一。
        $redis->decr('age');

//将指定key存储的数字值减去指定减量值。
        $redis->decrBy('age', 10);
        $a = $redis->get('name');
//        print_r($a);die;
//为指定key值尾部添加字符，返回值得长度，若key不存在则创建
        $redis->append('name', 'haha');

//获取键值：成功返回String类型键值，若key不存在或不是String类型则返回false
        $redis->get('name');
    }
    /**
     *
     * Hash操作
     * 哈希操作
     * 可理解为数据库操作
     *
     */
    public function hash() {
        $redis = $this->index();
        //为user表中的字段赋值。成功返回1，失败返回0。若user表不存在会先创建表再赋值，若字段已存在会覆盖旧值。
        $redis->hSet('user', 'name', '222');
        $redis->hSet('user', 'name1', '2223');

//获取user表中指定字段的值。若user表不存在则返回false。
        $redis->hGet('user', 'realname');

//查看user表的某个字段是否存在，存在返回true，否则返回false。
        $redis->hExists('user', 'realname');

//删除user表的一个字段，不支持删除多个字段。成功返回1，否则返回0。
        $redis->hDel('user', '222');

//同时设置某个user表的多个字段值。成功返回true。
        $redis->hMset('user', ['name' => 'jet', 'age' => 18]);

//同时获取某个user表的多个字段值。其中不存在的字段值为false。
        $redis->hMget('user', ['name', 'age']);

//获取某个user表所有的字段和值。
        $redis->hGetAll('user');

//获取某个user表所有字段名。user表不存在时返回空数组，key不为user表时返回false。
        $redis->hKeys('user');

//获取某个user表所有字段值。
        $redis->hVals('user');

//为user表中不存在的字段赋值。若user表不存在则先创建，若字段已存在则不做任何操作。设置成功返回true，否则返回false。
        $redis->hSetNx('user', 'realname', 'jetwu');

//获取某个user表的字段数量。若user表不存在返回0，若user不是hash表则返回false。
        $redis->hLen('user');

//为user表中的指定字段加上指定的数值，若user表不存在则先创建，若字段不存在则先初始化值为0再进行操作，若字段值为字符串则返回false。设置成功返回字段新值。
        $redis->hIncrBy('user', 'age', 10);

//为user表中的指定字段加上指定浮点数值。
        $redis->hIncrBy('user', 'age', 1.5);
    }
    //列表
    public function list() {
        //存储数据到列表中

        $redis = $this->index();

        //在列表头部插入一个值one，当列表不存在时自动创建一个列表，key1为列表名
        $redis->lpush("key1", "one");
        $arList = $redis->lrange("key1", -1 ,-1); //获取key1列表所有的值

        //在列表尾部插入一个值two，当列表不存在时自动创建一个列表，key1为列表名
        $redis->rPush("key1","two");

        //将一个插入已存在的列表头部，列表不存在时操作无效
        $redis->rPushx("key1","1");

        //删除列表的第一个元素并返回列表和列表的第一个元素，当key1不存在或不是列表则返回false
        $redis->lPop('key1');

        //删除列表的最后一个元素并返回列表和列表的最后一个元素，当key1不存在或不是列表则返回false
        $redis->rPop('key1');

        //删除并或取列表的第一个元素，如果没有元素则会阻塞直到等待超时
        $redis->blPop('asd', 10);

        //删除并或取列表的最后一个元素，如果没有元素则会阻塞直到等待超时
        $ret = $redis->brPop('asd', 10);

        //移除列表key1中最后一个元素，将其插入另一个列表asd头部，并返回这个元素。若源列表没有元素则返回false
        $redis->rpoplpush('key1', 'asd');

        //移除列表key1中最后一个元素，将其插入另一个列表asd头部，并返回这个元素。如果列表没有元素则会阻塞列表直到超时,超时返回false。
        $ret = $redis->brpoplpush('key1', 'asd', 10);

        //返回列表长度
        $redis->lLen('key1');

        $arList = $redis->lrange("key1", 0 ,10); //获取key1值队列里面的数据（start=0,end=-1获取全部的）

        //通过索引 (也就是下标key) 获取列表中的元素，如果没有该索引，则返回false。
        $redis->lindex('key1', 0);//0为数据下标

        //通过索引修改列表中元素的值，如果没有该索引，则返回false。
        $redis->lSet('key1', 2, '1');


        //在列表key1中指定元素six前面或后面插入元素。若指定元素不在列表中，或列表不存在时，不执行任何操作
        //Redis::AFTER插入元素后面    Redis::BEFORE插入元素前面         亲测试：去掉(Redis::)就好使了
        //返回值：插入成功返回插入后列表元素个数，若key1不存在返回0，若key1不是列表返回false
        $redis->lInsert('key1', 'BEFORE', 'one', '1');

        //根据第三个参数（count），删除掉相对的value
        //count > 0 : 从表头开始向表尾搜索，移除与value相等的元素，数量为count。
        //count < 0 : 从表尾开始向表头搜索，移除与value相等的元素，数量为count的绝对值。
        //count = 0 : 移除表中所有与value相等的值。
        //返回实际删除元素个数
        $redis->lrem('key1', '1', -2);

        //对一个列表进行截取，只保留指定区间 (如：下标1到10) 的元素，其他元素都删除。成功返回true。
        $redis->ltrim('key1', 5, 10);die;

        // 获取存储的数据并输出列表下标0到5的数据
        // 0为开始查询的列表里的第一个元素，-1则为最后一个元素
        // 5代表查询5条数据，当5为-1时则查看所有数据,
        $redis->lrange("key1", 0 ,5);
    }
    /**
     *
     * Set操作
     * 集合命令
     * 保证数据的唯一
     * 不保证顺序
     *
     */
    public function set() {
        $redis = $this->index();
//将一个元素加入集合，已经存在集合中的元素则忽略。若集合不存在则先创建，若key不是集合类型则返回false，若元素已存在返回0，插入成功返回1。
        $redis->sAdd('set', '11');


//返回集合中所有成员。
        $redis->sMembers('set');

//判断集合里是否存在指定元素，是返回true，否则返回false。
        $redis->sismember('set', 'hello');

//返回集合中元素的数量。
        $redis->scard('set');

//随机删除并返回集合里的一个元素。
        $redis->sPop('set');

//随机返回（n）个集合内的元素，由第二个参数决定返回多少个
//如果 n 大于集合内元素的个数则返回整个集合
//如果 n 是负数时随机返回 n 的绝对值，数组内的元素会重复出现
        $redis->sRandMember('set', -20);

//删除集合中指定的一个元素，元素不存在返回0。删除成功返回1，否则返回0。
        $redis->srem('set', 'hello');

//模糊搜索相对的元素，
//参数：key，迭代器变量，匹配值，每次返回元素数量（默认为10个）
        $redis->sscan('set', $it, 's*', 5);

//将指定一个源集合里的值移动到一个目的集合。成功返回true，失败或者源集合值不存在时返回false。
//参数：源集合，目标集合，移动的元素
        $redis->sMove('set', 'set2', 'sdf4');

//以第一个集合为标准，后面的集合对比，返回差集
        $redis->sDiff('set', 'set2','set3');

//参数：第一个参数为目标集合，存储缺少的值（三个集合相加，同样字段覆盖，组合成一个新的集合）返回第一个参数所增加的值的个数。
        $redis->sDiffStore('set', 'set3', 'set2');

//返回所有集合的相同值，必须所有集合都有，不存在的集合视为空集。
        $redis->sInter('set', 'set3', 'set2');

//参数：第一个参数为目标集合，存储后面集合的交集
//若目的集合已存在则覆盖它。返回交集元素个数，否则返回储存的交集
        $redis->sInterStore('set4', 'set', 'set3');

//把所有集合合并在一起并返回
        $redis->sUnion('set', 'set2', 'set3');

//以第一个集合为目标，把后面的集合合并在一起，存储到第一个集合里面，如果已经存在则覆盖掉，并返回并集的个数
        $redis->sUnionStore('set4', 'set', 'set2', 'set3');

    }
    /**
     *
     * Zset操作
     * sorted set操作
     * 有序集合
     * sorted set 它在set的基础上增加了一个顺序属性，这一属性在修改添加元素的时候可以指定，每次指定后，zset会自动从新按新的值调整顺序
     *
     */
    public function sortedSet() {
        $redis = $this->index();
         //将一个或多个元素插入到集合里面，默认从尾部开始插入
         //如果要在头部插入，则找一个元素，在元素后面添加一个你需要插入的元素即可
         $redis->zAdd('sorted1',100,'坑啊',98.999,99,90,90,80,80,60,60,70,70);
         // $redis->zAdd('集合',浮点数（元素）,'key'，(插入头部的数据)，key);

         //返回有序集中指定区间内的成员。成员按分数值递增排序，分数值相同的则按字典序来排序。
         //参数：第四个参数表示是否返回各个元素的分数值，默认为false。
         $redis->zRange('sorted', 0, -1, true);

         //返回有序集中指定区间内的成员。成员按分数值递减排序，分数值相同的则按字典序的倒序来排序。
         $redis->zReverseRange('sorted', 0, -1, true);

         //返回有序集中指定分数区间的成员列表，按分数值递增排序
         $redis->zRangeByScore('sorted', 10, 99);
         //自定义返回的序集返回起始位置及条数
         $redis->zRangeByScore('sorted', 0,90,['limit' =>[0,2]]);

//        返回有序集中指定分数区间的成员列表，按分数值递减排序，分数值相同的则按字典序的倒序来排序。注意，区间表示的时候大值在前，小值在后。
         $redis->zRevRangeByScore('sorted', 100, 90);

         //迭代有序集合中的元素。
         //可理解为查找指定的值，将元素修改为float类型
         //返回值：[元素名=>分数值,,..]
         $redis->zscan('sorted', $it, 100, 10);

         //返回指定有序集的元素数量,序集的长度。
         $redis->zCard('sorted');

         //返回有序集中指定分数区间的成员数量。
         $redis->zCount('sorted', 90, 100);

         //返回有序集中指定成员的分数值。若成员不存在则返回false。
         $redis->zScore('sorted', 'math');

         //返回有序集中指定成员元素的大小排名，按分数值递增排序。分数值最小者排名为0。
         $redis->zRank('sorted', 60);

         //返回有序集中指定成员元素的排名，按分数值递减排序。分数值最大者排名为0。
         $redis->zRevRank('sorted', 70);

         //删除有序集中的一个或多个成员，忽略不存在的成员。返回删除的元素个数。
         $redis->zRem('sorted', 'chemistry', 'English');

         //删除有序集中指定排名区间的所有成员,返回删除元素个数
         $redis->zRemRangeByRank('sorted', 0, 2);

         //删除有序集中指定分数值区间的所有成员，返回删除元素的个数
         $redis->zRemRangeByScore('sorted', 80, 90);

         //对有序集中指定成员的分数值增加指定增量值。若为负数则做减法，若有序集不存在则先创建，若有序集中没有对应成员则先添加，最后再操作。
         $redis->zIncrBy('sorted', 2, 'Chinese');

         //计算给定一个或多个有序集的交集，元素相加，并将其存储到目的有序集中
         $redis->zinterstore('zset3',['sorted','sorted1']);


         //计算给定一个或多个有序集的并集，元素相加，并将其存储到目的有序集中
         $redis->zunionstore('zset3',['sorted', 'sorted1']);
    }
}