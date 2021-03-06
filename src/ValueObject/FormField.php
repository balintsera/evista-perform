<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 10. 14.
 * Time: 10:21
 */

namespace Evista\Perform\ValueObject;

use Evista\Perform\Exception\FormFieldException;
use Evista\Perform\ValueObject\UploadedFile;
use Evista\Perform\Exception\NoFileUploadedException;
use Evista\Perform\ValueObject\SpecialFormFieldValidatorFactory;
use Evista\Perform\ValueObject\ValidationError;

class FormField
{
    const TYPE_TEXT_INPUT = 'input';
    const TYPE_PASSWORD = 'password';
    const TYPE_SUBMIT = 'submit';
    const TYPE_HIDDEN = 'hidden';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT = 'select';
    const TYPE_BUTTON = 'button';
    const TYPE_FILE = 'file';
    const TYPE_OPTION = 'option';


    private $type;
    private $attributes = [];
    private $name;
    private $value;
    private $default;
    private $tagName = 'input';
    private $mandatory = false;
    private $sanitizationCallback;
    private $validationCallback;
    private $label; // only checkboxes self::TYPE_CHECKBOX
    private $options = []; // only select self::TYPE_SELECT
    private $errors = [];
    private $files = []; // only for file types

    public function __construct($type)
    {
        $this->type = $type;

        // Default callbacks do nothing, just returns the original
        $this->sanitizationCallback = function ($value) {
            return $value;
        };
        $this->validationCallback = function ($value) {
            return false;
        };

        switch ($this->type) {
            case self::TYPE_TEXTAREA:
                $this->tagName = 'textarea';
                break;
            case self::TYPE_SELECT:
                $this->tagName = 'select';
                break;
            case self::TYPE_BUTTON:
                $this->tagName = 'button';
                break;
            case self::TYPE_OPTION:
                $this->tagName = 'option';
                break;
        }

        $this->options = [];
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return FormField
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return FormField
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }


    /**
     * @param $attributeName
     * @return mixed|null
     * @throws FormFieldException;
     */
    public function getAttribute($attributeName)
    {
        if (!$this->hasAttribute($attributeName)) {
            throw FormFieldException::NoSuchAttribute($attributeName, $this->getName());
        }

        return $this->attributes[$attributeName];
    }

    public function hasAttribute($attributeName)
    {
        return array_key_exists($attributeName, $this->attributes);
    }

    /**
     * @param $options
     * @return $this
     */
    public function addOption($options)
    {
        $this->options[] = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return FormField
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return FormField
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return FormField
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     * @return FormField
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @param boolean $mandatory
     * @return FormField
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * @param $inputValue
     * @return closure's return value
     */
    public function getSanitizationCallback($inputValue)
    {
        return $this->sanitizationCallback;
    }

    /**
     * @param \Closure $sanitizationCallback
     * @return FormField
     */
    public function setSanitizationCallback(\Closure $sanitizationCallback)
    {
        $this->sanitizationCallback = $sanitizationCallback;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     * @return FormField
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getTagName()
    {
        return $this->tagName;
    }

    /**
     * @param string $tagName
     * @return FormField
     */
    public function setTagName($tagName)
    {
        $this->tagName = $tagName;

        return $this;
    }


    public function sanitize($inputValue)
    {
        $function = $this->sanitizationCallback;

        return $function($inputValue);
    }

    /**
     * @return mixed
     */
    public function getValidationCallback()
    {
        return $this->validationCallback;
    }

    /**
     * @param mixed $validationCallback
     * @return FormField
     */
    public function setValidationCallback($validationCallback)
    {
        $this->validationCallback = $validationCallback;

        return $this;
    }


    public function validate()
    {
        // not mandatory, but empty
        if (strlen($this->getValue()) == 0) {
            return false;
        }
        // it has a pattern attribute
        if ($this->hasAttribute('pattern')) {
            $validateFunction = $this->validationCallback;
            $validationResult = $validateFunction($this->getValue());

            return $validationResult;
        }

        // it has not 'pattern' attrib, but its maybe a php validatable field with filter_var
        return $this->validateSpecialFields();

        // Can't validate??? It's not good
        return false;
    }

    /**
     * Validate field based on its special type
     *
     * @return bool
     */
    public function validateSpecialFields()
    {
        try {
            $validator = SpecialFormFieldValidatorFactory::create($this, $this->getValue());
            return $validator->validate();
        } catch (FormFieldException $exception) {
            // @TODO this is very suboptimal. do not return? Can not validate this type, not validator
            return false;
        }
    }

    private function getDebugInfo($line)
    {
        return $this->getName(). ' | '. $this->getValue() . ' | ' . __CLASS__ . ':' .$line ;
    }

    public function getDefaultSelectedOption()
    {
        if ($this->type !== self::TYPE_SELECT) {
            throw FormFieldException::notASelect($this->tagName);
        }

        $selecteds = [];
        foreach ($this->options as $option) {
            try {
                // If the option has a select  attribute its seleted
                $optionSelected = $option->getAttribute('selected');
                $selecteds[] = $option;
            } catch (FormFieldException $exception) {
                continue;
            }
        }

        // Send back the first
        if (count($selecteds) > 0) {
            return $selecteds[0];
        }

        return false;
    }

    /**
     * @return array
     *
     * @param bool rich (for backward compatibilty, will be @deprecated
     */
    public function getErrors($rich = false)
    {
        if (! $rich) {
            // trigger_error('getErrors is deprecated use getErrorObjets instead', E_USER_DEPRECATED);
            return $this->getErrorsAsStrings();
        }
        return $this->errors;
    }

    public function getErrorsAsStrings()
    {
        return array_map(
            function (ValidationError $errorMessage) {
                return $errorMessage->getErrorMessage();
            },
            $this->errors
        );
    }

    /**
     * @param array $errors
     * @return FormField
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param $error
     */
    public function addError(ValidationError $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (count($this->errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Compact files from uploaded files array - usually $_FILES
     *
     * @param    array     $files
     * @param    $uploadDir
     * @throws   FormFieldException
     * @throws   NoFileUploadedException
     * @throws   NoNameParam
     * @internal param $ [type] $files [description]
     */
    public function compactFiles(array $files, $uploadDir)
    {
        if ($this->type !== self::TYPE_FILE) {
            throw FormFieldException::notAFileUpload($this->tagName);
        }

        if (empty($files)) {
            throw new NoFileUploadedException("Files are missing from payload");
        }

        foreach (UploadedFile::create($this->name, $files, $uploadDir) as $uploadedFile) {
            $this->files[] = $uploadedFile;
        }
    }

    /**
     * add a new uploaded file to the field
     *
     * @param [type] $file [description]
     */
    public function addFile($file)
    {
        $this->files[] = $file;
    }

    public function getFiles()
    {
        return $this->files;
    }
}
