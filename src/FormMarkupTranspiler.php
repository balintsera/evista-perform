<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 12. 05.
 * Time: 12:30
 */

namespace Evista\Perform;


use Evista\Perform\Form\BaseForm;

class FormMarkupTranspiler
{
    const formClassNameAttrName = 'data-class';


    private $crawler;
    private $markup;
    private $formTag;
    private $formClassName;

    public function __construct($crawler, $markup = false)
    {
        $this->crawler = $crawler;
        $this->markup = $markup;
        if($markup){
            $this->initCrawler();
        }

    }

    /**
     * find a form tag
     * @return mixed
     */
    public function findFormTag(){
        // Caching is important: if it's not crawled already, crawl it
        $this->runIfNotCached('formTag', function(){
            return $this->crawler->filter('form');
        });

        return $this->formTag;
    }


    /**
     * Get class-name for the form
     */
    public function findFormClassName(){
        $this->runIfNotCached('formClassName', function(){
            return $this->findFormTag()->attr(self::formClassNameAttrName);
        });

        return $this->formClassName;
    }

    public function instantiateFormObject(){
        try{
            $form = new BaseForm();
        }catch (\Exception $exception){
            // Now what? This is a problem
            throw new ClassInstantiationFailed('Class instantiation failed');
        }

        return $form;
    }
    /**
     * Init crawler
     */
    private function initCrawler(){
        $this->crawler->addContent($this->markup);
    }

    /**
     * Check if a variable is empty and run function to
     * @param $variableName
     * @param callable $function
     */
    private function runIfNotCached($variableName, callable $function){
        if(null === $this->{$variableName}){
            $this->{$variableName} = $function();
        }
    }
}