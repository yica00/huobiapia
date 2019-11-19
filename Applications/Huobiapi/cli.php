<?php
require_once __DIR__ . '/../Config/Db.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/Notify.php';

$notify = new Notify();

// 找到所有openid
$openids = \GatewayWorker\Lib\Db::instance('db1')->select('openid')->from('tasks')->distinct('openid')->query();

$opids = [];
foreach ( $openids as $openid ){
    $opids[] =  $openid['openid'];
}


while (1){
    $hight = $notify->redis->get( 'hight' )?:9000;
    $low = $notify->redis->get( 'low' )?:8000 ;

    foreach ( $opids as $oid ){
        // 大于的情景
        $tasks = \GatewayWorker\Lib\Db::instance('db1')->select('*')->from('tasks')->where("gt='1'")
            ->where("openid='{$oid}'")->where("price<='{$hight}'")->orderByASC(['price'])->limit(1)->query();

        if( $tasks ){
            // 执行通知
            if(  notify_jianghan( $tasks[0] ) ){
                \GatewayWorker\Lib\Db::instance('db1')->delete('tasks')->where("id='{$tasks[0]['id']}'")->query();   // 执行删除
            }

        }

        //小于的情景
        $tasks2 = \GatewayWorker\Lib\Db::instance('db1')->select('*')->from('tasks')->where("gt='2'")
            ->where("openid='{$oid}'")->where("price>='{$low}'")->orderByDESC(['price'])->limit(1)->query();

        if( $tasks2 ){
            if(  notify_jianghan( $tasks2[0] ) ){
                \GatewayWorker\Lib\Db::instance('db1')->delete('tasks')->where("id='{$tasks2[0]['id']}'")->query();   // 执行删除
            }
        }
    }
    sleep(1);
}



$tasks = \GatewayWorker\Lib\Db::instance('db1')->select('*')->from('tasks')->where('gt=1')->where('')->query();
foreach ( $tasks as $task ){
    if( $task['gt'] == 1 ){
        $hight = $notify->redis->get( 'hight' );
        $low = $notify->redis->get( 'low' ) ;

    }else{
        $low = $notify->redis->get( 'low' ) ;
    }
    echo $task['price'];
}


function notify_jianghan( $task )
{
    $arr = [
        'openid' => $task['openid'],
        'gt' => ($task['gt'] ==1)? "大于" : "小于",
        'price' => $task['price'],
    ];
    try{
        $data = curl_request('http://admin.jhsywy.com/wechat/mb/notify', $arr);
//        var_dump( $data);
    }catch ( Exception $e ){
        return false;
    }
    return true;
}

function curl_request($url,$params=array(),$request_type='get',$ret_type='json',$header=[]) {
    $curl = curl_init();
    if($request_type=='get' && !empty($params) && is_array($params)){
        $url.='?'.http_build_query($params);
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt(
        $curl, CURLOPT_POSTFIELDS, $params
    );
    if(!empty($header)){
        curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
    }
    $data = curl_exec($curl);
    $errno = curl_errno($curl);
    $info=curl_getinfo($curl);
    curl_close($curl);
    if($ret_type=='json') {
        return json_decode($data,1);
    }
    return $data;
}
