<?php

namespace Evista\Perform\Test;

use Evista\Perform\MarkupTransformer;
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

        $transformer = new MarkupTransformer($crawler, $markup);
        $form = $transformer->findFormTag();
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Crawler', $form);
    }

    /**
     *
     */


}
