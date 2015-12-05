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

    public function testAttributeTranspilerWithEmtpyInput(){
        $markup = <<<EOF
        <form method="POST" id="custom-form">
            <input type="password"/>
        </form>
EOF;
        $crawler  = new Crawler();
        $transpiler = new FormMarkupTranspiler($crawler, $markup);

        $fields = $transpiler->findFields();
        $expectedField = new FormField(FormField::TYPE_PASSWORD);
        $this->assertEquals($expectedField, $fields['']);
    }

    public function testAddTextFieldToFormFromMarkup(){
        $exampleFormClassName = 'Evista\Perform\Form\ExampleForm';
        $markup = <<<EOF
        <form method="POST" id="custom-form">
            <input type="text" name="your-name" id="your-name" value="Sera Balint" placeholder="Your name"/>
        </form>
EOF;
        $crawler  = new Crawler();
        $transpiler = new FormMarkupTranspiler($crawler, $markup);

        $fields = $transpiler->findFields();

        $expectedTextField = new FormField('text');
        $expectedTextField
            ->setName("your-name")
            ->setDefault("Sera Balint")
            ->setAttributes(['id' => 'your-name', 'placeholder' => "Your name"]);

        $this->assertEquals($expectedTextField, $fields['your-name']);
    }

}
