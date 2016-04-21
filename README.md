# monster
## Monster为爱码士开源后的项目：[http://www.2ma4.com][1] ##


----------


## 建设爱码士的初衷 ##
  爱码士是我去年一个偶然的想法，想要架设一个全新的面向极客和码农的论坛，一直觉得中国极客和码农众多，却难以找到一个彼此能够相互学习交流的地方，于是，我花了半年的时间编写了爱码士这个论坛。我希望这个论坛就如它的名字一样，聚集了所有爱代码的人士，一起学习，一起交流，一起分享，一起发现，一起进步。但毕竟我是一个只会写代码和搞设计的宅男，在运营推广上肯定是弱项，如果你热爱爱码士，可以向周围同事宣传本站:)。
  为达到一种帖子内热情交流的氛围，我参考了很多种盖楼设计，比如经典的网易盖楼模式，以及disqus的缩进式子回复等等，这些都有一个缺点，就是逐渐向内缩进会塌楼，印象中网易在盖到七十楼时就塌楼了，因此我设计了一种新颖的盖楼模式。


----------


## 爱码士的技术层面 ##
  从mockup构想到页面psd设计，再到HTML+css的编写，以及后端php mvc框架编写和数据逻辑的实现，爱码士的前后端都由我一个人编写，因此代码上难免有疏忽之处，希望能予以指出。
  
**前端：**
为设计一种美观和简洁的前端UI，爱码士的前端摒弃了任何UI框架，只引用了jquery，纯手写了一个公共的global.js和base.css，系统图标采用了icomoon的web font，因此前端修改起来非常简单，同时前端引用的库有：

 - cropbox.js：用于头像上传时裁剪，[https://plugins.jquery.com/cropbox/][2]
 - jquery.devbridge-autocomplete.min.js，用于生成搜索候选下拉框[https://github.com/devbridge/jQuery-Autocomplete][3]
 - jquery.jscrollbar-2.0.0.min.js，私信页面时的滚动条替换[https://github.com/daiying-zhang/jquery.jscrollbar][4]
 - jquery.timeago.js，私信页面时间友好化显示，[http://timeago.yarp.com][5]
 - switchery.min.js，注册登录时的checkbox替换[http://abpetkov.github.io/switchery/][6]
 - tipsy，用于鼠标hover时的提示[http://onehackoranother.com/projects/jquery/tipsy/][12]

**后端：**
  架设一个常规的简单论坛，其实大家第一反应也就是MySQL中建立4个表，user表存储用户信息，thread表存储帖子信息，reply表存储回复信息，notification表存储用户通知信息，一个论坛后端数据库就做完了，配合php写点基础验证逻辑，加上个memcache缓存帖子、回复、用户等信息，一个最简单的论坛就架设成了。我这次便想尝试一次全新的数据架构：用NoSQL作为主存储去编写一个论坛，最终选中了SSDB作为全站的主存储，SSDB（[http://ssdb.io/][7]）简单的说就是一个存储在硬盘上的redis，由于其和redis有相同的使用协议，但内核基于Google的leveldb进行封装，支持十亿级别的数据存储，因而相对于redis这样的内存性数据库而言，其优势不言而喻。
  
  爱码士使用SSDB的hset存储用户信息、帖子信息、回复信息，用kv存储一些较为简单的数据结构如通知信息以及一些自增id如reply_id和msg_id等等，使用list控制私信发送频率，使用zset进行全局各类id索引。
  
  相对于mysql，nosql从来都是以性能彪悍著称，受限于作者本人电脑SSD硬盘容量的限制，没能测试千万级帖子的表现，测试中系统生成随机汉字的帖子，每个帖子随机分配3至5个充满随机汉字的回复，在灌满了约150W个帖子和600W个回复后，其性能还是非常彪悍，帖子和回复都是秒开。但在一些特定功能上，nosql还是暴露出了其缺陷，如帖子模糊搜索和根据用户名获取用户uid等功能，这方面还是要借助于MySQL，因此爱码士建立了两个MySQL表：user表用于存储用户信息，以便在注册时验证用户名存在、验证邮箱是否存在、根据用户名获取uid，thread表用于帖子标题分词，以便于站内的MATCH AGAINST搜索
  
  在站内搜索方面上，部分网友觉得站内搜索太慢，是不是数据库的问题，在这里说明一下，爱码士的分词采用的是[http://www.pullword.com/][8]的在线分词结果，相对于SCWS这类分词，pullword每天爬行各种预料，在分词结果上可以说是与时俱进，时刻保持着最新的词语结果，可以参见作者这篇博客[http://blog.sina.com.cn/s/blog_593af2a70102uw55.html][9]，因此爱码士抛弃了SCWS转向pullword，发帖时，系统先进行分词处理，在站内搜索时，系统要分两步进行，先Ajax后端调用pullword api对搜索词进行分词，分词后再对mysql thread表进行MATCH AGAINST处理，因此，时间就耗在远程调用pullword api上，当然也可以换成SCWS时间更快，不过感觉站内搜索还是很鸡肋吧，大家都用site Google命令是不是
  
  自己认为自己写的php框架才是全世界最好的php框架，爱码士也是基于自己写的一个很tiny的php框架搭建而成的，系统全局用index.php作为唯一入口，system文件夹中common.php作为全局公用函数，config.php为全局公共配置文件，controller.php为全局公用所继承的父controller，model.php为全局公用所继承的父model，public文件夹存放全站静态文件，application文件夹中controllers文件夹包含全局各种控制文件，db文件夹包含mysql操作类和官方的ssdb类，models文件夹包含全局各种model文件，thumb文件夹用于头像的裁剪的类，views文件夹存放全站模板
  


----------
## 安装Monster ##

 - 由于采用nosql作为主存储，因此Monster不支持虚拟主机，在VPS或服务器上，请先对mysql设定好ft_min_word_len=2，这样方便mysql全文检索。
 - 在MySQL中导入monster.sql
 - 按照ssdb官方文档[http://ssdb.io/docs/zh_cn/install.html][10]对服务器安装好ssdb
 - 由于Monster全局采用贴图库作为全站公用图片外链，需在[贴图库][11]申请贴图库的token，申请完毕后，按照system/config.php中的注释配置好config.php，Monster便配置完成了。
 - 请不要用windows主机运行爱码士，系统伪静态规则，Apache下直接使用系统自带的.htaccess文件，nginx下请在nginx配置文件中写入：
 location / {
 
 if (!-e $request_filename) {

   rewrite  ^(.*)$  /index.php?s=$1  last;
   
   break;
   
    }
    
}


  [1]: http://www.2ma4.com
  [2]: https://plugins.jquery.com/cropbox/
  [3]: https://github.com/devbridge/jQuery-Autocomplete
  [4]: https://github.com/daiying-zhang/jquery.jscrollbar
  [5]: http://timeago.yarp.com
  [6]: http://abpetkov.github.io/switchery/
  [7]: http://ssdb.io/
  [8]: http://www.pullword.com/
  [9]: http://blog.sina.com.cn/s/blog_593af2a70102uw55.html
  [10]: http://ssdb.io/docs/zh_cn/install.html
  [11]: http://www.kekaoyun.com
  [12]: http://onehackoranother.com/projects/jquery/tipsy/
