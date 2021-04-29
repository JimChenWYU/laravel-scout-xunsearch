<?php

namespace Tests\Fixtures;

class EmptySearchableModel extends SearchableModel
{
    public function toSearchableArray()
    {
        return [];
    }
}
