<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/25
 * Time: 1:28 PM
 */

namespace EasySwoole\HttpClient\Bean;


use EasySwoole\Spl\SplBean;

class Response extends SplBean
{
    protected $headers;
    protected $body;
    protected $errCode;
    protected $errMsg;
    protected $statusCode;
    protected $set_cookie_headers;
    protected $cookies;

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getErrCode()
    {
        return $this->errCode;
    }

    /**
     * @return mixed
     */
    public function getErrMsg()
    {
        return $this->errMsg;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return mixed
     */
    public function getCookiesRaw()
    {
        return $this->set_cookie_headers;
    }

    /**
     * @return mixed
     */
    public function getCookies()
    {
        return $this->cookies;
    }
}