<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Queries;

abstract class DistanceQuery implements Query
{
    /** @var string */
    protected $first;

    /** @var string */
    protected $second;

    /** @var int */
    protected $distance;

    /**
     * @param string $first
     * @param string $second
     * @param int    $distance
     */
    public function __construct(string $first, string $second, int $distance = 10)
    {
        $this->first = $first;
        $this->second = $second;
        $this->distance = $distance;
    }

    public function __toString()
    {
        return vsprintf('%s %s/%d %s', [
            $this->first,
            $this->kinds(),
            $this->distance,
            $this->second,
        ]);
    }

    /**
     * @return string
     */
    abstract public function kinds(): string;
}
