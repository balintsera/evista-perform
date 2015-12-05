<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 12. 05.
 * Time: 12:30
 */

namespace Evista\Perform;


use Evista\Perform\Form\BaseForm;
use Evista\Perform\ValueObject\FormField;
use Symfony\Component\DomCrawler\Crawler;

class FormMarkupTranspiler
{
    const formClassNameAttrName = 'data-class';


    private $crawler;
    private $markup;
    private $formTag;
    private $formClassName;
    private $fields;

    public function __construct(Crawler $crawler, $markup = false)
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
     * @return array
     */
    public function findFields(){
        $this->runIfNotCached('fields', function(){
            $this->transpileFields();
        });

        return $this->fields;
    }

    /**
     * Find fields in markup
     */
    private function transpileFields(){
        $this->findFormTag()->filter('input')->each(function (Crawler $node, $i){
            // If it has a type attr, use as type
            if(null !== $node->attr('type')){
                $type = $node->attr('type');
                $field = new FormField($type);
            }

            // Otherwise ehhh @TODO finish this

            $field
                ->setDefault($node->attr('value'))
                ->setValue($node->attr('value'))
                ->setName($node->attr('name'));
            // get attributes like id
            $attributes = [];
            $attributes['id'] = $node->attr('id');
            $attributes['class'] = $node->attr('class');
            $this->fields[$field->getName()] = $field;
        });;

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
            // Only assign that don't returns
            if(null !== $result = $function()){
                $this->{$variableName} = $result;
            }

        }
    }
}