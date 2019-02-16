<?php declare(strict_types=1);
/**
 * Db data validation.
 *
 * @copyright Copyright (c) 2017 Starweb AB
 * @license   BSD 3-Clause
 */

namespace Starlit\DbDataValidation;

use Starlit\Db\AbstractDbEntity;
use Starlit\Validation\Validator;
use Starlit\Validation\ValidatorTranslatorInterface;
use Symfony\Component\Translation\TranslatorInterface as SymfonyTranslatorInterface;

/**
 * Data validator for database entities.
 */
class DbEntityDataValidator
{
    /**
     * @var ValidatorTranslatorInterface|SymfonyTranslatorInterface|null
     */
    protected $translator;

    public function __construct($translator = null)
    {
        $this->translator = $translator;
    }

    /**
     * Create a data validator for database entity.
     *
     * @param AbstractDbEntity $dbEntity
     * @return Validator
     */
    public function createValidator(AbstractDbEntity $dbEntity, ...$params): Validator
    {
        $fieldsRuleProperties = $this->getDbEntityFieldsRuleProperties($dbEntity);
        $validator = new Validator($fieldsRuleProperties, $this->translator);

        if (($additionalFieldsRuleProperties = $this->getAdditionalFieldsRuleProperties($dbEntity, ...$params))) {
            $validator->addFieldsRuleProperties($additionalFieldsRuleProperties);
        }

        return $validator;
    }

    protected function getDbEntityFieldsRuleProperties(AbstractDbEntity $dbEntity): array
    {
        $validRuleProperties = Validator::getValidRuleProperties();
        $fieldsRuleProperties = [];
        foreach ($dbEntity->getDbProperties() as $propertyName => $attributes) {
            // Always validate if validate is not explicitly set to false
            if (!isset($attributes['validate']) || $attributes['validate'] === true) {
                $fieldsRuleProperties[$propertyName] = [];
                foreach ($validRuleProperties as $ruleName) {
                    if (isset($attributes[$ruleName])) {
                        $fieldsRuleProperties[$propertyName][$ruleName] = $attributes[$ruleName];
                    }
                }
            }
        }

        return $fieldsRuleProperties;
    }

    protected function getAdditionalFieldsRuleProperties(AbstractDbEntity $dbEntity): array
    {
        return [];
    }

    /**
     * Validate and (if no error messages) set database data.
     *
     * @param array  $data The data (e.g. from a form post) to be validated and set
     * @return array An array with all (if any) of error messages
     */
    public function validateAndSet(AbstractDbEntity $dbEntity, array $data): array
    {
        // Get extra arguments this method was called with and forward
        $extraArguments = array_slice(func_get_args(), 2);
        $validator = $this->createValidator($dbEntity, ...$extraArguments);

        $preProcessedData = $this->preProcessValidationDbData($dbEntity, $data, ...$extraArguments);
        $errorMessages = $validator->validate($preProcessedData);

        if (empty($errorMessages)) {
            $this->setValidatedDbData($dbEntity, $validator->getValidatedData());
        }

        return $errorMessages;
    }

    protected function preProcessValidationDbData(AbstractDbEntity $dbEntity, array $data): array
    {
        return $data;
    }

    protected function setValidatedDbData(AbstractDbEntity $dbEntity, array $validatedData)
    {
        $propertyNames = $dbEntity->getDbProperties();
        foreach ($validatedData as $propertyName => $value) {
            if (isset($propertyNames[$propertyName])) {
                // Call individual setters to enable easy data set overriding
                $methodName = 'set' . ucfirst($propertyName);
                $dbEntity->$methodName($validatedData[$propertyName]);
            }
        }
    }
}
