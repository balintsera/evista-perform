<?php

namespace Evista\Perform\Test;

use Evista\Perform\FormMarkupTranspiler;
use Symfony\Component\DomCrawler\Crawler;

class MarkupTransformerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * given a form markup string find form element and properties
     */
    public function testFormFinder()
    {
        $markup = '<form method="POST" id="custom-form"></form>';
        $crawler  = new Crawler();

        $transformer = new FormMarkupTranspiler($crawler, $markup);
        $form = $transformer->findFormTag();
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Crawler', $form);
    }

    /**
     * Get class from form
     */
    public function testFormClassNameFinder()
    {
        $markup = '<form method="POST" id="custom-form" '.FormMarkupTranspiler::formClassNameAttrName.'="Form\LoginForm"></form>';
        $crawler  = new Crawler();

        $transformer = new FormMarkupTranspiler($crawler, $markup);
        $className = $transformer->findFormClassName();
        $this->assertEquals('Form\LoginForm', $className);
    }


}
