<?php

namespace Evista\Perform\Test;

use Evista\Perform\Exception\FormFieldException;
use Evista\Perform\FormMarkupTranspiler;
use Evista\Perform\Service;
use Evista\Perform\ValueObject\FormField;
use League\Route\Http\Exception;
use Symfony\Component\DomCrawler\Crawler;
use Evista\Perform\Form\Form;

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
        $factory = new Service(new Crawler(), './var/uploads');
        $form = $factory->transpileForm($markup);

        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);

        $this->assertEquals('email', $form->getFields()['email']->getType());
        $this->assertEquals('password', $form->getFields()['password']->getType());
    }


    // pattern="banana|cherry"
    public function testPatternValidation(){
        $markup = <<<EOF
         <form method="post" action="/login" id="login-form">
            <input type="email" name="email" placeholder="Your email" value="" pattern="^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$">
          </form>
EOF;
        $factory = new Service(new Crawler(), './var/uploads');
        // Faking post
        $_POST['email'] = 'balint.sera@gmail.com';
        /** @var Form $form */
        $form = $factory->transpileForm($markup);
        $form->populateFields();

        $errors = $form->validate();
        $this->assertEquals(0, count($errors));

    }

    public function testSelectTranspilation()
    {
        $markup = <<<EOF
        <form method="post" action="/login" id="login-form">
           <select name="test-select">
              <option value="volvo">Volvo</option>
              <option value="saab" selected>Saab</option>
              <option value="mercedes">Mercedes</option>
              <option value="audi">Audi</option>
            </select>
        </form>
EOF;
        $factory = new Service(new Crawler(), './var/uploads');
        $form = $factory->transpileForm($markup);

        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);

        $selectField = $form->getFields()['test-select'];

        $options = $selectField->getOptions();

        $this->assertEquals('option', $options[0]->getTagName());
        $this->assertEquals('volvo', $options[0]->getDefault());
        $this->assertEquals('saab', $options[1]->getDefault());
        $this->assertEquals(["selected" => "selected"], $options[1]->getAttributes());

        $this->assertEquals('mercedes', $options[2]->getDefault());
        $this->assertEquals('audi', $options[3]->getDefault());
        $this->assertEquals('select', $form->getFields()['test-select']->getType());
    }

    public function testAttributes()
    {
        $markup = <<<EOF
        <form method="post" action="/login" id="login-form">
           <select name="test-select" id="test-id" placeholder="silly-placeholder">
              <option value="volvo">Volvo</option>
              <option value="saab" selected>Saab</option>
              <option value="mercedes">Mercedes</option>
              <option value="audi">Audi</option>
            </select>
        </form>
EOF;
        $factory = new Service(new Crawler(), './var/uploads');
        $form = $factory->transpileForm($markup);

        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);

        $selectField = $form->getFields()['test-select'];

        $this->assertEquals('test-id', $selectField->getAttribute('id'));

        try {
            $selectField->getAttribute('nosuchtag');
        } catch (\Exception $exception) {
            $this->assertInstanceOf('Evista\Perform\Exception\FormFieldException', $exception);
        }
    }

    public function testGetSelectedOption()
    {
        $markup = <<<EOF
        <form method="post" action="/login" id="login-form">
           <select name="test-select" id="test-id" placeholder="silly-placeholder">
              <option value="volvo">Volvo</option>
              <option value="saab" selected>Saab</option>
              <option value="mercedes">Mercedes</option>
              <option value="audi">Audi</option>
            </select>
        </form>
EOF;
        $factory = new Service(new Crawler(), './var/uploads');
        $form = $factory->transpileForm($markup);

        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);

        $selectField = $form->getField('test-select');

        $selected = $selectField->getDefaultSelectedOption();

        $this->assertEquals('saab', $selected->getDefault());
    }

    public function testValidation()
    {
        $markup = <<<EOF
        <form method="post" action="/login" id="login-form">
            <input
            type="email"
            name="email"
            placeholder="Your email"
            value=""
            pattern="^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$"
            >
            <input type="password" name="password" value="">
            <textarea name="test_textarea"></textarea>
            <input type="text" name="date" pattern="onlythisvalueisvalid">
            <input type="text" name="hungarian-telephone" pattern="(\+36|0036|06)?(\))?(-| )?[237]0\s\d{7}">
            <select name="test-select">
                <option value="volvo">Volvo</option>
                <option value="saab" selected>Saab</option>
                <option value="mercedes">Mercedes</option>
                <option value="audi">Audi</option>
            </select>
            <button value="login" id="login-button">Login</button>
        </form>
EOF;
        $factory = new Service(new Crawler(), './var/uploads');
        $_POST['email'] = 'baromság';
        $form = $factory->transpileForm($markup);
        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);
        $this->assertEquals(false, $form->getField('email')->isValid());

        $factory = new Service(new Crawler(), './var/uploads');
        $_POST['date'] = 'onlythisvalueisvalid';
        $form = $factory->transpileForm($markup);
        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);
        $this->assertEquals(true, $form->getField('date')->isValid());

        $factory = new Service(new Crawler(), './var/uploads');
        $_POST['date'] = 'nooo';
        $form = $factory->transpileForm($markup);
        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);
        $this->assertEquals(false, $form->getField('date')->isValid());

        $factory = new Service(new Crawler(), './var/uploads');
        $_POST['hungarian-telephone'] = '+36 70 6379022';
        $form = $factory->transpileForm($markup);
        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);
        $this->assertEquals(true, $form->getField('hungarian-telephone')->isValid());
        
        // Errors
        $errors = $form->getValidationErrors();
        $this->assertTrue(is_array($errors));
        $this->assertEquals(2, count($errors));
        $this->assertEquals('email has invalid submitted value: "baromság" but it should comply with this pattern: ^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$', $errors['email'][0]->getErrorMessage());
        $this->assertEquals("date has invalid submitted value: \"nooo\" but it should comply with this pattern: onlythisvalueisvalid", $errors['date'][0]->getErrorMessage());
        
        $factory = new Service(new Crawler(), './var/uploads');
        $_POST['hungarian-telephone'] = 'ez bztos nem 122';
        $form = $factory->transpileForm($markup);
        $this->assertInstanceOf('Evista\Perform\Form\Form', $form);
        $this->assertEquals(false, $form->getField('hungarian-telephone')->isValid());

        // all
        $this->assertFalse($form->isValid());

    }

    public function testSpecialValidation()
    {
        $markup = <<<EOF
        <form method="post" action="/login" id="login-form">
            <input
            type="email"
            name="email"
            placeholder="Your email"
            value=""
         >
        <textarea name="test_textarea"></textarea>
        </form>
EOF;
        // Fails
        $factory = new Service(new Crawler(), './var/uploads');
        $_POST['email'] = 'baromság';
        $form = $factory->transpileForm($markup);
        $this->assertEquals(false, $form->getField('email')->isValid());
        $this->assertFalse($form->isValid());

        // Validates
        $factory = new Service(new Crawler(), './var/uploads');
        $_POST['email'] = 'balint.sera@gmail.com';
        $form = $factory->transpileForm($markup);
        $this->assertEquals(true, $form->getField('email')->isValid());
        $this->assertTrue($form->isValid());

        // pattern method, with no special field
        $markupWithPattern = <<<EOF
        <form method="post" action="/login" id="login-form">
            <input
                pattern="^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$"
                name="email"
                placeholder="Your email"
                value=""
            >
            <textarea name="test_textarea"></textarea>
        </form>
EOF;
        // Fails
        $factory = new Service(new Crawler(), './var/uploads');
        $_POST = [];
        $_POST['email'] = 'baromság';
        $_POST['test_textarea'] = 'baromság';
        $form = $factory->transpileForm($markupWithPattern);
        $this->assertEquals(false, $form->getField('email')->isValid());
        $this->assertFalse($form->isValid());

        // Validates
        $factory = new Service(new Crawler(), './var/uploads');
        $_POST['email'] = 'balint.sera@gmail.com';
        $form = $factory->transpileForm($markupWithPattern);
        $this->assertEquals(true, $form->getField('email')->isValid());
        $this->assertTrue($form->isValid());
    }

    public function testErrorMessageBC()
    {
        $markupWithPattern = <<<EOF
        <form method="post" action="/login" id="login-form">
            <input
                pattern="^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$"
                name="email"
                placeholder="Your email"
                value=""
            >
            <textarea name="test_textarea"></textarea>
        </form>
EOF;
        // Fails
        $factory = new Service(new Crawler(), './var/uploads');
        $_POST = [];
        $_POST['email'] = 'baromság';
        $_POST['test_textarea'] = 'baromság';
        $form = $factory->transpileForm($markupWithPattern);
        $validationErrors = [];
        foreach ($form->getFields() as $field) {
            if (!$field->isValid()) {
                $validationErrors[$field->getName()] = $field->getErrors();
            }
        }
        
        $expected = [
            'email' => [
                0 => 'email has invalid submitted value: "baromság" but it should comply with this pattern: ^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$'
            ]
        ];

        $this->assertEquals($expected, $validationErrors);
    }
}
