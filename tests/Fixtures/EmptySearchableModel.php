<?php declare(strict_types=1);

namespace Tests\Fixtures;

class EmptySearchableModel extends SearchableModel
{
    public function toSearchableArray()
    {
        return [];
    }
}
