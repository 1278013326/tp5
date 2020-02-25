var data = [];
var dataStr = '1、美人鱼<br>\
<br>\
导演：周星驰<br>\
编剧：邓超 / 罗志祥 / 张雨绮 / 林允 / 吴亦凡 / 何问起<br>\
主演：邓超 / 罗志祥 / 张雨绮 / 林允 / 吴亦凡<br>\
类型：动作 / 爱情 / 喜剧<br>\
制片国家/地区：中国 <br>\
语言：普通话<br>\
上映日期：2016-02-08(中国) <br>\
片长：120分钟<br>\
<br>\
<br>\
2、何问起<br>\
<br>\
导演：周星驰<br>\
编剧：邓超 / 罗志祥 / 张雨绮 / 林允 / 吴亦凡 / 何问起<br>\
主演：何问起 / 邓超 / 罗志祥 / 张雨绮 / 林允 / 吴亦凡<br>\
类型：动作 / 爱情 / 喜剧<br>\
制片国家/地区：中国 <br>\
语言：普通话<br>\
上映日期：2016-02-08(中国大陆) <br>\
片长：120分钟<br>\
<br>\
<br>\
3、HoverTree<br>\
<br>\
导演：周星驰<br>\
编剧：HoverTree / 罗志祥 / 张雨绮 / 林允 / 吴亦凡 / 何问起<br>\
主演：何问起 / 邓超 / 罗志祥 / 张雨绮 / 林允 / 吴亦凡<br>\
类型：动作 / 爱情 / 喜剧<br>\
制片国家/地区：中国 <br>\
语言：普通话<br>\
上映日期：2016-02-08(中国大陆) <br>\
片长：120分钟<br>\
片长：120分钟<br>\
<br>\
<br>\
4、因为遇见你<br>\
<br>\
导演：周星驰<br>\
编剧：HoverTree / 罗志祥 / 张雨绮 / 林允 / 吴亦凡 / 何问起<br>\
主演：何问起 / 邓超 / 罗志祥 / 张雨绮 / 林允 / 吴亦凡<br>\
类型：动作 / 爱情 / 喜剧<br>\
制片国家/地区：中国 <br>\
语言：普通话<br>\
上映日期：2016-02-08(中国大陆) <br>\
片长：120分钟';
var h_hewenqiImgPath = "http://hovertree.com/texiao/js/15/images/";
var d = dataStr.split('<br><br><br>');
for (var i = 0; i < d.length; i++) {
    var c = d[i].split('<br><br>');
    data.push({
        img: h_hewenqiImgPath+c[0].replace('、', ' ') + '.jpg',
        caption: c[0].split('、')[1],
        desc: c[1]
    });
    //console.log(c[0].replace('、', ' ') + '.jpg');
};