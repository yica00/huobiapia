<?php
/**
 * run with command 
 * php start.php start
 */

ini_set('display_errors', 'on');
ini_set('serialize_precision',14); //防止php7.1以上浮点数json_encode精度会出问题
use Workerman\Worker;

if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    exit("start.php not support windows, please use start_for_win.bat\n");
}

// 检查扩展
if(!extension_loaded('pcntl'))
{
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if(!extension_loaded('posix'))
{
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

// 标记是全局启动
define('GLOBAL_START', 1);
define('geturl', "192.168.1.129");  //设置url ip

require_once __DIR__ . '/vendor/autoload.php';

// 加载所有Applications/*/start.php，以便启动所有服务
foreach(glob(__DIR__.'/Applications/*/start*.php') as $start_file)
{
    require_once $start_file;
}

// 将屏幕打印输出到Wor .ker::$stdoutFile指定的文件中
Worker::$stdoutFile = './stdout.log';
Worker::$logFile = './workerman.log';

// 运行所有服务orkman
