<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2016. 03. 11.
 * Time: 6:59
 */

namespace Evista\Perform\ValueObject;

class SpecialEmailValidator implements SpecialValidator
{
    private $field;
    private $submittedData;

    public function __construct(FormField $field, $submittedData)
    {
        $this->field = $field;
        $this->submittedData = $submittedData;
    }

    public function validate()
    {
        $result = filter_var($this->submittedData, FILTER_VALIDATE_EMAIL) ? false : true;
        return $result;
    }
}
