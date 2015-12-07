<?php

namespace CDSRC\Libraries\Translatable\Aspect;


/* **********************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 * ******************************************************************** */

use CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use TYPO3\Flow\I18n\Locale;
use TYPO3\Flow\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RenderingContextAwareInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TemplateObjectAccessInterface;

/**
 * Class ObjectAccessorAspect
 *
 * @Flow\Aspect
 */
class ObjectAccessorNodeAspect
{
    /**
     *
     * @Flow\Around("method(TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode->evaluate())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     *
     * @return mixed
     */
    public function getPropertyPathForTranslatableObject(JoinPointInterface $joinPoint)
    {
        $result = $joinPoint->getAdviceChain()->proceed($joinPoint);
        if ($result !== null) {
            return $result;
        }

        /** @var ObjectAccessorNode $objectAccessorNode */
        $objectAccessorNode = $joinPoint->getProxy();
        $propertyPathSegments = array_filter(explode('.', $objectAccessorNode->getObjectPath()));
        $countPropertyPathSegments = count($propertyPathSegments);
        if ($countPropertyPathSegments < 2) {
            return $result;
        }

        $renderingContext = $joinPoint->getMethodArgument('renderingContext');
        $subject = $renderingContext->getTemplateVariableContainer();
        for ($i = 0; $i < $countPropertyPathSegments; $i++) {
            $pathSegment = $propertyPathSegments[$i];
            try {
                $subject = ObjectAccess::getProperty($subject, $pathSegment);
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
                        $subject = $subject->$getter(new Locale($locale));
                        $i += 2;
                    }
                }
            }

            if ($subject instanceof RenderingContextAwareInterface) {
                $subject->setRenderingContext($renderingContext);
            }
            if ($subject instanceof TemplateObjectAccessInterface) {
                $subject = $subject->objectAccess();
            }
        }

        return $subject;
    }
}