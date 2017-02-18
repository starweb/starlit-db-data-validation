<?php

namespace Starlit\Db;

use Starlit\DbDataValidation\DbEntityDataValidator;
use Starlit\Validation\Validator;
use Starlit\Validation\ValidatorTranslatorInterface;

class DbEntityDataValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateValidator()
    {
        $dbEntityDataValidator = new DbEntityDataValidator();
        $dbEntity = new TestDbEntity();
        $validator = $dbEntityDataValidator->createValidator($dbEntity);

        $this->assertInstanceOf(Validator::class, $validator);
    }

    public function testValidateAndSet()
    {
        $dbEntityDataValidator = new DbEntityDataValidator();
        $dbEntity = new TestDbEntity();

        $errorMsgs = $dbEntityDataValidator->validateAndSet(
            $dbEntity,
            ['someName' => 'woho']
        );

        $this->assertEmpty($errorMsgs);
        $this->assertEquals($dbEntity->__call('getSomeName'), 'woho');
    }

    public function testValidateAndSetFail()
    {
        $mockTranslator = $this->createMock(ValidatorTranslatorInterface::class);
        $mockTranslator->expects($this->atLeastOnce())
            ->method('trans')
            ->will($this->returnValue('some error msg'));
        $dbEntityDataValidator = new DbEntityDataValidator($mockTranslator);

        $dbEntity = new TestDbEntity();
        $errorMsgs = $dbEntityDataValidator->validateAndSet(
            $dbEntity,
            ['someName' => 'wohoaaaaaaa']
        );

        $this->assertNotEmpty($errorMsgs);
        $this->assertNotEquals($dbEntity->__call('getSomeName'), 'wohoaaaaaaa');
    }
}

class TestDbEntity extends AbstractDbEntity
{
    protected static $dbTableName = 'someTable';

    protected static $dbProperties = [
        'someId' => ['type' => 'int'],
        'someName' => ['type' => 'string', 'maxLength' => 5, 'required' => true],

    ];

    protected static $primaryDbPropertyKey = 'someId';
}
