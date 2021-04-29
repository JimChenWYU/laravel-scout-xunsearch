<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Queries;

class FieldQuery extends MixedQuery
{
    /** @var string */
    protected $field;

    /**
     * @param string $query
     * @param string $field
     */
    public function __construct(string $query, string $field)
    {
        $this->field = $field;
        parent::__construct($query);
    }

    public function __toString()
    {
        if (empty($this->field)) {
            return parent::__toString();
        }

        return vsprintf('%s:%s', [
            $this->field,
            $this->query
        ]);
    }
}
