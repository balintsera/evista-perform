<?php

namespace Evista\Perform\Test;

use Evista\Perform\FormMarkupTranspiler;
use Evista\Perform\ValueObject\FormField;
use Symfony\Component\DomCrawler\Crawler;
use Evista\Perform\Factory;
class MarkupTranspilerTest extends \PHPUnit_Framework_TestCase
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

        $this->assertInstanceOf('Evista\Perform\Form\Form', $formObject);

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

    public function testTranspileSimpleLoginForm(){
        $markup = <<<EOF
        <form method="post" action="/login" id="login-form">
            <input type="email" name="email" placeholder="Your email" value="">
            <input type="password" name="password" value="">
            <button value="login" id="login-button">Login</button>
        </form>
EOF;
        $factory = new Factory(new Crawler());
        $form = $factory->transpileForm($markup);

        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);

        $this->assertEquals('email', $form->getFields()['email']->getType());
        $this->assertEquals('password', $form->getFields()['password']->getType());
    }

}
