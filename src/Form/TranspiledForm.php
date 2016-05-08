<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 12. 06.
 * Time: 11:40
 */

namespace Evista\Perform\Form;

use Evista\Perform\FormMarkupTranspiler;
use Evista\Perform\Form\Form;

class TranspiledForm
{
    private $markup;
    private $transpiler;
    private $form;

    public function __construct($markup, $crawler, $transpiler = false, $uploadDir = false)
    {
        $this->markup = $markup;

        if (!$transpiler) {
            $this->transpiler = new FormMarkupTranspiler($crawler, $this->markup, $uploadDir);
        } else {
            $this->transpiler = $transpiler;
        }

        $this->transpile();
    }

    /**
     * Convert a markup to a BaseForm objet
     *
     * @return mixed
     */
    public function transpile()
    {
        // Form
        /**
 * @var Form form
*/
        $this->form = $this->transpiler->instantiateFormObject();

        // Fields
        $fields = $this->transpiler->findFields();
        if (!is_array($fields)) {
            throw new \Exception("No fields found");
        }


        $this->form->addFields($fields);

        // Populate
        $this->form->populateFields();

        // Validate
        $this->form->validate();

        return $this->form;
    }

    /**
     * Get transpiled form
     *
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }
}
