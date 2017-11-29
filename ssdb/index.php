<?php
/**
 * set(key,value) //key不存在创建，存在覆盖
 * setx(key,value,expire) //比上面多了个过期时间
 * setnx(key,value) //key不存在时起作用
 * expire(key,expire)
 * ttl(key)
 * get(key)
 * getset(key,value) //更新key，返回原有值
 *
 * multi_set([key1=>value1])
 * multi_get([key1,key2])
 * multi_del([key1,key2])
 *
 * del(key)
 * exists(key)
 *
 * incr(key,deep)
 *
 * substr(key,start,[size])
 * strlen(key)
 *
 * keys(key_start,key_end,limit)   //返回key
 * scan(key_start,key_end,limit)   //返回value  *
 * scan('','',limit) //获取整个区间的值
 * rscan(key_start,key_end,limit)
 *
 *
 *
 * //hashmap
 * hset(name,key,value)
 * hget(name,key)
 * hdel(name)
 * hincr(name,key,deep)
 * hexists(name,key)
 * hsize(name)  //返回hashmap中元素个数
 *
 * hlist(name_start,name_end,limit)
 * hrlist(name_start,name_end,limit)
 * hkeys(name,key_start,key_end,limit)
 * hgetall(name)  //返回整个hashmap关联数组结构
 * hscan(name,key_start,key_end,limit)
 * hclear(name) //删除name中所有key
 *
 */
//装过扩展的就不需要引用ssdb文件
$ssdb = new SimpleSSDB('127.0.0.1',8888);

//$ssdb->hset('user','name','ding');
//$ssdb->hset('user','age',23);
//$ssdb->hset('user','sex','man');
//
//$ssdb->hset('good','cate','fruit');
//$ssdb->hset('good','weight',23);

print_r($ssdb->hgetall('user'));
