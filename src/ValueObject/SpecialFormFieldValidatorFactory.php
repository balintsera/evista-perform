<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2016. 03. 11.
 * Time: 6:46
 */

namespace Evista\Perform\ValueObject;

use Evista\Perform\Exception\FormFieldException;
use Evista\Perform\ValueObject\SpecialEmailValidator;

class SpecialFormFieldValidatorFactory
{
    /**
     * Factory method to create an appropriate type
     * @param FormField $field
     * @param $submittedData
     * @return SpecialEmailValidator
     * @throws FormFieldException
     */
    public static function create(FormField $field, $submittedData)
    {
        switch ($field->getType()) {
            case 'email':
                $object = new SpecialEmailValidator($field, $submittedData);
                break;
            default:
                throw FormFieldException::noSuchType($field->getType());
                break;
        }

        return $object;
    }
}