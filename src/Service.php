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
    private $uploadDir;

    public function __construct($crawler, $uploadDir = false)
    {
        require_once __DIR__.'/../vendor/autoload.php';
        $this->crawler = $crawler;
        $this->uploadDir = $uploadDir;
    }


    /**
     * Init transpilation
     * @param $markup
     * @return mixed
     */
    public function transpileForm($markup)
    {
        $transpiledForm = new TranspiledForm($markup, $this->crawler, false, $this->uploadDir);

        // Return the result form object
        return $transpiledForm->getForm();
    }
}
