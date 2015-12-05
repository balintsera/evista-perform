<?php

namespace Evista\Perform\Test;

use Evista\Perform\FormMarkupTranspiler;
use Evista\Perform\ValueObject\FormField;
use Symfony\Component\DomCrawler\Crawler;

class MarkuptranspilerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * given a form markup string find form element and properties
     */
    public function testFormFinder()
    {
        $markup = '<form method="POST" id="custom-form"></form>';
        $crawler  = new Crawler();

        $transpiler = new FormMarkupTranspiler($crawler, $markup);
        $form = $transpiler->findFormTag();
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Crawler', $form);
    }



    /**
     * Instantiate a form class from class name
     */
    public function testInstantiateFormClassFromMarkup(){
        $exampleFormClassName = 'Evista\Perform\Form\ExampleForm'; 
        $markup = '<form method="POST" id="custom-form"></form>';

        $crawler  = new Crawler();
        $transpiler = new FormMarkupTranspiler($crawler, $markup);
        
        $formObject = $transpiler->instantiateFormObject();

        $this->assertInstanceOf('Evista\Perform\Form\BaseForm', $formObject);

    }

    public function testAddTextFieldToFormFromMarkup(){
        $exampleFormClassName = 'Evista\Perform\Form\ExampleForm';
        $markup = '<form method="POST" id="custom-form"></form>';

        $crawler  = new Crawler();
        $transpiler = new FormMarkupTranspiler($crawler, $markup);

        $formObject = $transpiler->instantiateFormObject();

        $fields = $formObject->findFields();

        $expected = new FormField();

        $this->assertEquals([new FormField()])
    }

}
