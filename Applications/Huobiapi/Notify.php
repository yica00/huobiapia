<?php


class Notify
{
    public $redis;

    public function __construct()
    {
        $this->getRedis();
    }

    private function getRedis()
    {
        $redis  =  new \Redis();
        $redis->connect('127.0.0.1', '6379');
        $this->redis = $redis;
    }

}



