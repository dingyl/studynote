<?php
function createOrder($product_id, $num, $opt = [])
{
    //根据商品id生成具体的key,key中存的是商品的库存数量
    $product_key = "product_stock_num:" . $product_id;
    $script = <<<PRODUCT_SCRIPT
local key = ARGV[1]
local num = ARGV[2]
local current_stock = redis.call('get', key)
if (current_stock >= num ) then
    local new_stock = current_stock - num
    redis.call('set', key, new_stock)
    return new_stock
else
    return -1
end
PRODUCT_SCRIPT;
    $redis = new \Redis();
    $redis->connect("127.0.0.1");
    $script_hash = $redis->script("load", $script);
    //当脚本执行失败会返回false，失败原因一般为key不存在
    $flag = $redis->evalSha($script_hash, [$product_key, $num], 0);
    if ($flag !== false && $flag !== -1) {
        return true;
    }
    return false;
}
createOrder(1, 1);