<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Aspect;

use CDSRC\Libraries\Exceptions\InvalidMethodException;
use CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\FluidAdaptor\Core\Parser\SyntaxTree\TemplateObjectAccessInterface;
use Neos\Utility\ObjectAccess;
use Neos\Utility\Exception\PropertyNotAccessibleException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;

/**
 * Class ObjectAccessorAspect
 *
 * @Flow\Aspect
 */
class ObjectAccessorNodeAspect
{
    /**
     *
     * @Flow\Around("method(Neos\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode->evaluate())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     *
     * @return mixed
     *
     * @throws InvalidLocaleIdentifierException
     * @throws InvalidMethodException
     */
    public function getPropertyPathForTranslatableObject(JoinPointInterface $joinPoint)
    {
        $result = $joinPoint->getAdviceChain()->proceed($joinPoint);
        if ($result !== null) {
            return $result;
        }

        /** @var ObjectAccessorNode $objectAccessorNode */
        $objectAccessorNode = $joinPoint->getProxy();
        $propertyPathSegments = array_values(array_filter(
            explode('.', $objectAccessorNode->getObjectPath()),
            array($this, 'isValidPathSegment')
        ));
        $countPropertyPathSegments = count($propertyPathSegments);
        if ($countPropertyPathSegments < 2) {
            return $result;
        }

        $renderingContext = $joinPoint->getMethodArgument('renderingContext');
        $subject = $renderingContext->getVariableProvider();
        for ($i = 0; $i < $countPropertyPathSegments; $i++) {
            try {
                $pathSegment = $propertyPathSegments[$i];
                $subject = ObjectAccess::getProperty($subject, $pathSegment);
            } catch (\InvalidArgumentException $exception) {
                $subject = null;
            } catch (PropertyNotAccessibleException $exception) {
                $subject = null;
            }

            if ($subject === null) {
                return null;
            }

            if ($subject instanceof TranslatableInterface) {
                if (isset($propertyPathSegments[$i + 1]) && isset($propertyPathSegments[$i + 2])) {
                    $property = $propertyPathSegments[$i + 1];
                    $locale = $propertyPathSegments[$i + 2];
                    // TODO: Implement with generic translation
                    if (in_array($property, $subject->getTranslatableFields()) && $locale !== '') {
                        $getter = 'get' . ucfirst($property);
                        if(!property_exists($subject, $getter)){
                            throw new InvalidMethodException($getter . ' does not exists in ' . get_class($subject), 1525785451);
                        }
                        $subject = $subject->$getter(new Locale($locale));
                        $i += 2;
                    }
                }
            }

            if ($subject instanceof TemplateProcessorInterface) {
                $subject->setRenderingContext($renderingContext);
            }
            if ($subject instanceof TemplateObjectAccessInterface) {
                $subject = $subject->objectAccess();
            }
        }

        return $subject;
    }

    /**
     * Check if given segment path is valid
     *
     * @param mixed $segment
     *
     * @return bool
     */
    protected function isValidPathSegment($segment)
    {
        return !is_string($segment) || strlen($segment) > 0;
    }
}