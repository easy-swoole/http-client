<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-07-23
 * Time: 15:33
 */

namespace EasySwoole\HttpClient\Bean;


class CURLFile
{
    protected string $path;
    protected string $name;
    protected string $type;
    protected string $filename;
    protected int $offset = 0;
    protected int $length = 0;

    public function __construct(string $filename, string $name, ?string $mimetype = null)
    {
        $this->name = $name;
        $this->path = $filename;
        $this->type = $mimetype ?? '';
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param mixed $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return mixed
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
    }
}
