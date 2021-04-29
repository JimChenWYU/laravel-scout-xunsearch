<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Tokenizers\Results;

class Top
{
    /** @var int */
    protected $times;

    /** @var string */
    protected $attr;

    /** @var string */
    protected $word;

    /**
     * Top constructor.
     * @param int    $times
     * @param string $attr
     * @param string $word
     */
    public function __construct(int $times, string $attr, string $word)
    {
        $this->times = $times;
        $this->attr = $attr;
        $this->word = $word;
    }

    /**
     * @return int
     */
    public function getTimes(): int
    {
        return $this->times;
    }

    /**
     * @param int $times
     */
    public function setTimes(int $times): void
    {
        $this->times = $times;
    }

    /**
     * @return string
     */
    public function getAttr(): string
    {
        return $this->attr;
    }

    /**
     * @param string $attr
     */
    public function setAttr(string $attr): void
    {
        $this->attr = $attr;
    }

    /**
     * @return string
     */
    public function getWord(): string
    {
        return $this->word;
    }

    /**
     * @param string $word
     */
    public function setWord(string $word): void
    {
        $this->word = $word;
    }
}
