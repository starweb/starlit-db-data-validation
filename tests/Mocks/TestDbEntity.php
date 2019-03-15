<?php declare(strict_types=1);

namespace Starlit\DbDataValidation\Tests\Mocks;

use Starlit\Db\AbstractDbEntity;

class TestDbEntity extends AbstractDbEntity
{
    protected static $dbTableName = 'someTable';

    protected static $dbProperties = [
        'someId' => ['type' => 'int'],
        'someName' => ['type' => 'string', 'maxLength' => 5, 'required' => true],

    ];

    protected static $primaryDbPropertyKey = 'someId';
}
