<?php


class BloomFilter extends AbstractBloomFilter
{
    /**
     * 表示判断重复内容的过滤器
     * @var string
     */
    protected $bucket = 'rptc';

    protected $hashFunction = array('BkdRHash', 'SdbmHash', 'JsHash');
}