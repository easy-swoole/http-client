<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/25
 * Time: 10:49 AM
 */

namespace EasySwoole\HttpClient\Bean;


use EasySwoole\Spl\SplBean;

class Url extends SplBean
{
    protected string $scheme = 'http';
    protected string $host = '';
    protected int $port = 0;
    protected ?string $path = null;
    protected ?string $query = null;
    protected bool $isSsl = false;
    protected string $fullPath = '/';

    /**
     * @return mixed
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param mixed $scheme
     */
    public function setScheme(string $scheme): void
    {
        $this->scheme = $scheme;
    }

    /**
     * @return mixed
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @param mixed $query
     */
    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    /**
     * IsSsl Getter
     * @return mixed
     */
    public function getIsSsl(): bool
    {
        return $this->isSsl;
    }

    /**
     * IsSsl Setter
     * @param mixed $isSsl
     */
    public function setIsSsl(bool $isSsl): void
    {
        $this->isSsl = $isSsl;
    }

    /**
     * FullPath Getter
     * @return mixed
     */
    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    /**
     * FullPath Setter
     * @param mixed $fullPath
     */
    public function setFullPath(string $fullPath): void
    {
        $this->fullPath = $fullPath;
    }
}
