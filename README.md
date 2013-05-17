Hi， 是这样的。

虽然是RD，但是承担了整个项目的运维工作。
每天背个破电脑跑来跑去的累死了。
又舍不得买mac air，加上之前受qcon的github机器人的启发。

准备自己搞一个运维的app。

这里是服务端的程序。



1，进来之后，第一步肯定要list所有server
 
[root@ip-10-188-113-110 www]# curl "http://localhost/index.php?server=1&type=1"       
[{"ip":"10.62.9.31","server_id":"1","host":"ec2-184-73-79-224.compute-1.amazonaws.com","nickname":"web1"},{"ip":"10.60.58.9","server_id":"2","host":"ec2-23-20-148-33.compute-1.amazonaws.com","nickname":"web2"}]
 
你发送server=1&type=1，我就会把所有的机器列表发给你。
你需要显示的，是nickname那一列，其他属性你自己记住就可以。
 
2，点某个server，需要看看有哪些可以执行的操作
 
[root@ip-10-188-113-110 www]# curl "http://localhost/index.php?server=1&type=2"
[{"cmd_type":"1","content":"df -h","need_sudo":"0","cmd_desc":"check harddisk"}]
 
比如这个，前面那个server，你输入server_id，后面一个type输入2，就是显示这个server所有可以执行的操作。
 
3，比如要在server1上执行command1，那就发起这样的请求
 
curl http://localhost/index.php?server=1&type=3&plan=1 
 
type=3表示执行，plan显示第二步中出来的cmd_type这个值。
结果如下：
 
[root@ip-10-188-113-110 www]# curl http://localhost/index.php?server=1&type=3&plan=1
{"code":200,"content":"Filesystem            Size  Used Avail Use% Mounted on\n\/dev\/sda1             9.9G  8.6G  766M  93% \/\nnone                  1.9G     0  1.9G   0% \/dev\/shm\n\/dev\/sdf1              20G   12G  7.6G  60% \/data\n"}
 
code等于200表示执行完成。其他所有的code，都表示错误。有的是404有的是419，报错的原因会在content中给出。
content表示执行完的返回的文本。
 
出错的显示：
 
[root@ip-10-188-113-110 www]# curl "http://localhost/index.php?server=1"              
{"code":419,"content":"missing get parameter [server] or [type]!"}
 
比如这个，就表示少了参数。


目前，我们有两张表，
 
mysql> desc common_cmd
    -> ;
+-----------+--------------+------+-----+---------+----------------+
| Field     | Type         | Null | Key | Default | Extra          |
+-----------+--------------+------+-----+---------+----------------+
| cmd_type  | int(11)      | NO   | PRI | NULL    | auto_increment |
| content   | varchar(100) | NO   |     | NULL    |                |
| need_sudo | smallint(6)  | YES  |     | 0       |                |
| cmd_desc  | varchar(200) | YES  |     | NULL    |                |
+-----------+--------------+------+-----+---------+----------------+
4 rows in set (0.00 sec)
 
mysql> desc servers;
+-----------+--------------+------+-----+---------+----------------+
| Field     | Type         | Null | Key | Default | Extra          |
+-----------+--------------+------+-----+---------+----------------+
| server_id | int(11)      | NO   | PRI | NULL    | auto_increment |
| ip        | varchar(20)  | NO   |     | NULL    |                |
| host      | varchar(100) | NO   |     | NULL    |                |
| nick_name | varchar(100) | NO   |     | NULL    |                |
+-----------+--------------+------+-----+---------+----------------+
 
阿欢，你目前要做的是，做一个页面，支持往这两个表里面，插入数据。
你这边添加了server和命令，我不用添加代码，就可以完成支持的命令。
 
目前的数据如下所示：
 
mysql> select * from servers;
+-----------+------------+-------------------------------------------+-----------+
| server_id | ip         | host                                      | nick_name |
+-----------+------------+-------------------------------------------+-----------+
|         1 | 10.62.9.31 | ec2-184-73-79-224.compute-1.amazonaws.com | web1      |
|         2 | 10.60.58.9 | ec2-23-20-148-33.compute-1.amazonaws.com  | web2      |
+-----------+------------+-------------------------------------------+-----------+
2 rows in set (0.00 sec)
 
mysql> select * from common_cmd;
+----------+---------+-----------+----------------+
| cmd_type | content | need_sudo | cmd_desc       |
+----------+---------+-----------+----------------+
|        1 | df -h   |         0 | check harddisk |
+----------+---------+-----------+----------------+
1 row in set (0.00 sec)

