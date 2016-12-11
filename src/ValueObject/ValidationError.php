<?php
/**
 * User: balint
 * Date: 2016. 02. 29.
 * Time: 10:21
 */

namespace Evista\Perform\ValueObject;

use Evista\Perform\ValueObject\FormField;

class ValidationError
{
    private $field;
    private $errorMessage = '';

    public function __construct(FormField $field, $errorMessage = false)
    {
        if (!$errorMessage) {
            $errorMessage = 'has invalid submitted value';
        }

        $this->field = $field;
        $this->errorMessage = $errorMessage;

        // Don't decorate mandatory messages automatically
        if ($this->field->isMandatory()) {
            return;
        }

        // Add field name to the error message
        $this->addFieldNameToMessage();

        // Add submitted value to the message with basic XSS protection
        $this->addSubmittedValueToMessage();

        // Add rule
        $this->addRuleToMessage();
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    private function addFieldNameToMessage()
    {
        $this->errorMessage = $this->field->getName() . ' ' . $this->errorMessage;
    }

    private function addSubmittedValueToMessage()
    {
        $this->errorMessage = $this->errorMessage . ': "' . htmlspecialchars($this->field->getValue()) . '"';
    }
    
    private function addRuleToMessage()
    {
        if ($this->field->hasAttribute('pattern')) {
            $this->errorMessage .= ' but it should comply with this pattern: ' . $this->field->getAttribute('pattern');
        }
    }
}