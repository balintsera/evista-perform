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
    /**
     * @param $attributeName
     * @param $fieldTagName
     * @return static
     */
    public static function noSuchAttribute($attributeName, $fieldTagName)
    {
        $message = 'No such attribute ('. $attributeName . ') in field: '. $fieldTagName;

        return new static($message);
    }

    /**
     * @param $fieldTagName
     * @return static
     */
    public static function notASelect($fieldTagName)
    {
        $message = 'This field is not an option type (it\'s actually a ' . $fieldTagName . ') so it has no selected option';

        return new static($message);
    }
}