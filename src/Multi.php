<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/25
 * Time: 2:12 PM
 */

namespace EasySwoole\HttpClient;


use Swoole\Coroutine\Channel;

class Multi
{
    protected $list = [];

    function addTask($name,HttpClient $client):Multi
    {
        $this->list[$name] = $client;
        return $this;
    }

    function exec(float $timeout = 1.0):array
    {
        $channel = new Channel(count($this->list)+1);
        foreach ($this->list as $name => $client)
        {
            go(function ()use($channel,$name,$client){
                $channel->push([
                    $name=>$client->exec()
                ]);
            });
        }
        $ret = [];
        $all = count($this->list);
        $start = microtime(true);
        while (1){
            if(round(microtime(true) - $start,3) > $timeout ){
                break;
            }
            if(count($ret) == $all){
                break;
            }
            $temp = $channel->pop(0.01);
            if(is_array($temp)){
                $ret = $ret+$temp;
            }
        }
        return $ret;
    }
}