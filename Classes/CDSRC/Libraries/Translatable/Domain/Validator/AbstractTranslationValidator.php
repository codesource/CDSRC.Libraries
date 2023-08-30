<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Validator;

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Validation\Exception\InvalidValidationConfigurationException;
use Neos\Flow\Validation\Exception\InvalidValidationOptionsException;
use Neos\Flow\Validation\Exception\NoSuchValidatorException;
use Neos\Flow\Validation\Validator\AbstractValidator;
use Neos\Flow\Validation\ValidatorResolver;

class AbstractTranslationValidator extends AbstractValidator
{

    /**
     * @var ValidatorResolver
     * @Flow\Inject
     */
    protected ValidatorResolver $validatorResolver;

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to Result.
     *
     * @param mixed $value
     *
     * @return void
     *
     * @throws InvalidValidationConfigurationException
     * @throws InvalidValidationOptionsException
     * @throws NoSuchValidatorException
     */
    protected function isValid($value): void
    {
        if (!$value instanceof AbstractTranslation) {
            $this->addError('The given Object is not a translation.', 1448509134);
            return;
        }
        $translationClassName = get_class($value);
        $baseValidatorConjunction = $this->validatorResolver->getBaseValidatorConjunction($translationClassName);
        $result = $baseValidatorConjunction->validate($value);
        if($result){
            foreach($result->getErrors() as $error){
                $this->getResult()->addError($error);
            }
            foreach($result->getNotices() as $notice){
                $this->getResult()->addNotice($notice);
            }
            foreach($result->getWarnings() as $warning){
                $this->getResult()->addWarning($warning);
            }
        }
    }
}
