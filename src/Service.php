<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 12. 06.
 * Time: 11:36
 */

namespace Evista\Perform;

use Evista\Perform\Form\TranspiledForm;

class Service
{
    private $crawler;

    public function __construct($crawler)
    {
        $this->crawler = $crawler;
    }


    /**
     * Init transpilation
     * @param $markup
     * @return mixed
     */
    public function transpileForm($markup)
    {
        $transpiledForm = new TranspiledForm($markup, $this->crawler);

        // Return the result form object
        return $transpiledForm->getForm();
    }
}