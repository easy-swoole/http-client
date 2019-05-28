<?php

namespace EasySwoole;

use Swoole\Coroutine\Http\Client;

class CurlResource
{
    protected $options = [];

    protected $result = [];

    public function setOption(int $option,$val)
    {
        $this->options[$option] = $val;
    }

    public function __getResult():array
    {
        return $this->result;
    }

    public function __exec():?string
    {
        if(empty($this->options[CURLOPT_URL])){
            trigger_error('URL is empty');
            return null;
        }
        $url = $this->options[CURLOPT_URL];
        $urlInfo = parse_url($url);
        $ssl = false;
        if(isset($urlInfo['port'])){
            $port = $urlInfo['port'];
        }else{
            if($urlInfo['scheme'] == 'https'){
                $ssl = true;
                $port = 443;
            }else{
                $port = 80;
            }
        }

        $client = new Client($urlInfo['host'],$port,$ssl);

        if(!empty($this->options[CURLOPT_TIMEOUT])){
            $client->set([
                'timeout' => $this->options[CURLOPT_TIMEOUT]
            ]);
        }

        // eg:'tool=curl; fun=yes;'
        if(!empty($this->options[CURLOPT_COOKIE])){
            $list = explode(';',$this->options[CURLOPT_COOKIE]);
            $ret = [];
            foreach ($list as $item){
                if(!empty($item)){
                    $item = explode('=',trim($item));
                    $ret[trim($item[0],' ')] = trim($item[1],' ');
                }
            }
            if(!empty($ret)){
                $client->setCookies($ret);
            }
        }

        if(!empty($this->options[CURLOPT_HTTPHEADER])){
            $client->setHeaders($this->options[CURLOPT_HTTPHEADER]);
        }

        if(!empty($this->options[CURLOPT_CUSTOMREQUEST])){
            $client->setMethod($this->options[CURLOPT_CUSTOMREQUEST]);
        }

        if(!empty($this->options[CURLOPT_POSTFIELDS])){
            //文件与表单混合
            if(is_array($this->options[CURLOPT_POSTFIELDS])){
                $temp = [];
                foreach ($this->options[CURLOPT_POSTFIELDS] as $key => $item){
                    if($item instanceof \CURLFile){
                        $client->addFile($item->getFilename(),$item->getPostFilename());
                    }else{
                        $temp[$key] = $item;
                    }
                }
                $client->post($urlInfo['path'],$temp);
            }else{
                $client->post($urlInfo['path'],$this->options[CURLOPT_POSTFIELDS]);
            }
        }else{
            $client->get($urlInfo['path']);
        }

        $this->result = (array)$client;
        $client->close();
        return $this->result['body'] ?: null;
    }

}

function curl_init(?string $url = null): CurlResource
{
    $ch =  new CurlResource();
    if($url){
        curl_setopt($ch, CURLOPT_URL, $url);
    }
    return $ch;
}

function curl_setopt (CurlResource $ch, int $option, $value):bool
{
    $ch->setOption($option,$value);
    return true;
}

function curl_setopt_array(CurlResource $ch, array $options):bool
{
    foreach ($options as $option => $val){
        curl_setopt($ch,$option,$val);
    }
    return true;
}

function curl_exec(CurlResource $ch)
{
    return $ch->__exec();
}

function curl_getinfo(CurlResource $resource):array
{
    return $resource->__getResult();
}