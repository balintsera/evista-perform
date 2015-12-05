<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 12. 05.
 * Time: 12:30
 */

namespace Evista\Perform;


class MarkupTransformer
{
    private $crawler;
    private $markup;

    public function __construct($crawler, $markup = false)
    {
        $this->crawler = $crawler;
        $this->markup = $markup;
        if($markup){
            $this->initCrawler();
        }

    }

    public function findFormTag(){
        return $this->crawler->filter('form');
    }



    private function initCrawler(){
        $this->crawler->addContent($this->markup);
    }
}