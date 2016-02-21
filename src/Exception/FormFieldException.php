<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2016. 02. 21.
 * Time: 8:45
 */

namespace Evista\Perform\Exception;


class FormFieldException extends \Exception
{
    public static function noSuchAttribute($attributeName, $fieldTagName)
    {
        $message = 'No such attribute ('. $attributeName . ') in field: '. $fieldTagName;
        return new static($message);
    }
}