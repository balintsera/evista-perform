<?php

namespace Evista\Perform\Form;

use Evista\Perform\Exception\FormFieldException;
use Evista\Perform\ValueObject\FormField;
use Evista\Perform\ValueObject\ValidationError;

/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 10. 14.
 * Time: 9:56
 */
class Form
{
    private $nonceKey = 'djlKJdlkjei877798a7lskdjf';
    private $nonceValue;
    private $submittedData;
    private $postData;
    private $validationErrors = [];

    protected $formFields = [];
    protected $templateVars = ['form_fields' => []];
    protected $templateName;
    protected $onSubmitCallable;


    public function __construct()
    {
        $this->formErrors = [];


        // Important: add csrf token to every form
        $this->addCSRFTokenField();

        // Set up form class (after submitting we need to know what class to initialize
        $classSelf = new \ReflectionClass($this);
        $className = $classSelf->getShortName();
        $fieldName = 'class';
        $classNameField = new FormField(FormField::TYPE_HIDDEN);
        $classNameField
            ->setName($fieldName)
            ->setValue($className)
            ->setMandatory(true);

        $this->addField($fieldName, $classNameField);


        // Setup submission
        $this->setSubmittedDatasFromPost();

        // Add fields to template variables
        $this->addFieldsToTemplateVars();

        // Populate if we are after submission
        $this->populateFields();

    }


    /**
     * Handles submission - it's better not to call automatically
     *
     * @throws \NoCallbackSetException
     */
    public function handleSubmission()
    {
        // When posted
        if (null !== $this->getSubmittedData()['nonce']) {
            $this->runOnSubmit();
        }
    }

    public function onSubmit(callable $callable)
    {
        $this->onSubmitCallable = $callable;
    }

    private function runOnSubmit()
    {
        if (null === $this->onSubmitCallable) {
            throw new \NoCallbackSetException('onSubmit callable is not set');
        }
        $callable = $this->onSubmitCallable;
        $callable();
    }


    /**
     * Get submitted values (independently of submission method eg. ajax / simple)
     */
    private function setSubmittedDatasFromPost()
    {
        if (null !== $_POST) {
            // If ajax, check formData parameter
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
            ) {
                $keyValuePairs = explode('&', $_POST['formData']);
                array_walk(
                    $keyValuePairs,
                    function ($value) {
                        list($key, $postValue) = explode('=', $value);
                        $this->submittedData[$key] = urldecode($postValue);
                    }
                );
            } else {
                $this->submittedData = $_POST;
            }
        }
    }

    /**
     * Add CSRF token hidden input field
     */
    private function addCSRFTokenField()
    {
        $this->nonceValue = $this->createNonce();
        $nonce = new FormField(FormField::TYPE_HIDDEN);
        $nonce
            ->setName('nonce')
            ->setValue($this->createNonce())
            ->setValidationCallback(
                function ($value) {
                    if (function_exists('wp_verify_nonce')) {
                        if (!wp_verify_nonce($value, $this->nonceKey)) {
                            throw new \Exception('Unauthorized request');
                        }
                    } // Use own csrf token
                    else {
                        if (!isset($_SESSION['csrf_tokens'][$value])) {
                            throw new \Exception('Unauthorized request');
                        } else {
                            unset($_SESSION['csrf_tokens'][$value]);
                        }
                    }

                    return false;
                }
            )
            ->setMandatory(true);
        $key = 'nonce';

        $this->addField($key, $nonce);
    }


    /**
     * Create nonce
     *
     * @return string
     */
    private function createNonce()
    {
        if (function_exists('wp_create_nonce')) {
            return wp_create_nonce($this->nonceKey);
        }

        $nonce = md5(microtime(true).$this->nonceKey);
        if (empty($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = array();
        }
        $_SESSION['csrf_tokens'][$nonce] = true;

        return $nonce;
    }

    /**
     * populates form from POST after submission
     */

    public function populateFields()
    {
        if (count($this->submittedData) < 1) {
            return;
        }
        array_map(
            function (FormField &$field) {
                if (isset($this->submittedData[$field->getName()])) {
                    $raw = $this->submittedData[$field->getName()];

                    $sanitized = $field->sanitize($raw);
                    $field->setValue($sanitized);
                } else {
                    // Unset value (see: checkboxes where value only sent when checkbox was checked
                    if ($field->getType() == FormField::TYPE_CHECKBOX) {
                        $field->setValue(null);
                    }
                }
            },
            $this->getFields()
        );
    }

    /**
     * Validate form input: call validate in every field and set a validation Error if not valid
     *
     * @return mixed
     */
    public function validate()
    {
        foreach ($this->getFields() as $field) {
            if (! isset($this->submittedData[$field->getName()])) {
                continue;
            }
            
            // is it mandatory and empty?
            if ($field->isMandatory() && strlen($this->submittedData[$field->getName()]) < 1) {
                $mandatory = new ValidationError($field, 'is mandatory');
                $field->addError($mandatory);

                // Go to the next field, no need to validate
                continue;
            }

            $validationResult = $field->validate();

            if ($validationResult) {
                $validationError = new ValidationError($field);
                $field->addError($validationError);
            }
        }
    }

    /**
     * Validate field based on its special type
     *
     * @return array
     */
    public function getValidationErrors()
    {
        if ($this->isValid()) {
            return [];
        }
        
        // if it's already cached, just return it
        if (count($this->validationErrors) > 0) {
            return $this->validationErrors;
        }


        // All errors can be spotted in the fields
        foreach ($this->getFields() as $field) {
            if (!$field->isValid()) {
                $this->validationErrors[$field->getName()] = $field->getErrors(true);
            }
        }

        return $this->validationErrors;
    }

    /**
     * @return mixed
     */
    public function setSubmittedData(array $postDatas)
    {
        return $this->submittedData = $postDatas;
    }

    /**
     * @return mixed
     */
    public function getSubmittedData()
    {
        return $this->submittedData;
    }


    /**
     * Get templateVars
     *
     * @return array
     */
    public function getTemplateVars()
    {
        return $this->templateVars;
    }

    /**
     * Set templateVars
     *
     * @param  array $templateVars
     * @return $this
     */
    public function setTemplateVars(array $templateVars)
    {
        $this->templateVars = $templateVars;

        return $this;
    }

    /**
     * Add element to template vars
     *
     * @param  $element
     * @param  null    $key
     * @return $this
     */
    public function addToTemplateVars($element, $key = null)
    {
        $this->templateVars[$key] = $element;

        return $this;
    }

    /**
     * Get fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->formFields;
    }

    /**
     * Add a new field to the form
     *
     * @param  $key
     * @param  $field
     * @return $this
     */
    public function addField($key, $field)
    {
        $this->formFields[$key] = $field;

        return $this;
    }

    /**
     * Get a field
     *
     * @param  $key
     * @throws FormFieldException
     * @return mixed
     */
    public function getField($key)
    {
        if (!in_array($key, array_keys($this->formFields))) {
            throw FormFieldException::noSuchFieldName($key);
        }
        return $this->formFields[$key];
    }

    /**
     * Add fields to the form
     *
     * @param array $fields
     */
    public function addFields(array $fields)
    {
        array_walk(
            $fields,
            function ($field, $key) {
                $this->addField($key, $field);
            }
        );
    }


    /**
     * Adds fields to template vars
     */
    private function addFieldsToTemplateVars()
    {
        $this->templateVars['form_fields'] = array_merge($this->formFields, $this->templateVars['form_fields']);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        // Iterate trough all fields and return false if any error found in any of them
        foreach ($this->getFields() as $field) {
            if (!$field->isValid()) {
                return false;
            }
        }

        // No errors found
        return true;
    }

    /**
     * @return mixed
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @param mixed $postData
     * @return Form
     */
    public function setPostData($postData)
    {
        $this->postData = $postData;

        return $this;
    }
}
