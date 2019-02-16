<?php declare(strict_types=1);

namespace Starlit\Db;

use PHPUnit\Framework\TestCase;
use Starlit\DbDataValidation\DbEntityDataValidator;
use Starlit\Validation\Validator;
use Starlit\Validation\ValidatorTranslatorInterface;

class DbEntityDataValidatorTest extends TestCase
{
    public function testCreateValidator(): void
    {
        $dbEntityDataValidator = (new class extends DbEntityDataValidator {
            protected function getAdditionalFieldsRuleProperties(AbstractDbEntity $dbEntity): array
            {
                return ['foo' => ['required' => true]];
            }
        });
        $dbEntity = new TestDbEntity();
        $validator = $dbEntityDataValidator->createValidator($dbEntity);
        $rulesProperties = $validator->getFieldRuleProperties('foo');

        $this->assertInstanceOf(Validator::class, $validator);
        $this->assertArrayHasKey('required', $rulesProperties);
        $this->assertTrue($rulesProperties['required']);
    }

    public function testValidateAndSet(): void
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

    public function testValidateAndSetFail(): void
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
