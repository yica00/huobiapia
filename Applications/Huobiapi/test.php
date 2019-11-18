<?php


$notify = new Notify();

while (1){
    echo $notify->redis->get( 'low' ) . "__". $notify->redis->set( 'hight') .'\r\n';
    sleep(1);
}