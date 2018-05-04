<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Validator;

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Validation\Validator\AbstractValidator;

class AbstractTranslationValidator extends AbstractValidator
{

    /**
     * @var \Neos\Flow\Validation\ValidatorResolver
     * @Flow\Inject
     */
    protected $validatorResolver;

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to Result.
     *
     * @param mixed $value
     *
     * @return void
     * @throws \Neos\Flow\Validation\Exception\InvalidValidationOptionsException if invalid validation options have been specified in the constructor
     */
    protected function isValid($value)
    {
        if (!$value instanceof AbstractTranslation) {
            $this->addError('The given Object is not a translation.', 1448509134);
            return;
        }
        $translationClassName = get_class($value);
        $baseValidatorConjunction = $this->validatorResolver->getBaseValidatorConjunction($translationClassName);
        $this->result = $baseValidatorConjunction->validate($value);
    }
}