<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 12. 06.
 * Time: 11:40
 */

namespace Evista\Perform\Form;


use Evista\Perform\FormMarkupTranspiler;

class TranspiledForm
{
    private $markup;
    private $transpiler;
    private $form;

    public function __construct($markup, $crawler, $transpiler = false)
    {
        $this->markup = $markup;

        if(!$transpiler){
            $this->transpiler =  new FormMarkupTranspiler($crawler, $this->markup);
        }
        else{
            $this->transpiler = $transpiler;
        }

        $this->transpile();
    }

    /**
     * Convert a markup to a BaseForm objet
     * @return mixed
     */
    public function transpile(){
        // Form
        $this->form = $this->transpiler->instantiateFormObject();

        // Fields
        $this->form->addFields($this->transpiler->findFields());

        // Validations

        return $this->form;
    }

    /**
     * Get transpiled form
     * @return mixed
     */
    public function getForm(){
        return $this->form;
    }

}