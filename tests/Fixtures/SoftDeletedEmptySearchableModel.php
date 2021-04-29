<?php declare(strict_types=1);

namespace Tests\Fixtures;

class SoftDeletedEmptySearchableModel extends SearchableModel
{
    public function toSearchableArray()
    {
        return [];
    }

    public function pushSoftDeleteMetadata()
    {
        //
    }

    public function scoutMetadata()
    {
        return ['__soft_deleted' => 1];
    }
}
