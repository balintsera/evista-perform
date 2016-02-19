<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 12. 05.
 * Time: 12:30
 */

namespace Evista\Perform;


use Evista\Perform\Form\Form;
use Evista\Perform\ValueObject\ExtendedDOMNode;
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
            $form = new Form();
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
        $this->findFormTag()->filter('input, select, textarea')->each(function (Crawler $node, $i){

            // If it has a type attr, use as type
            if(null !== $node->attr('type')){
                $type = $node->attr('type');
            }
            else{
                $type = $node->nodeName();
            }

            $field = new FormField(strtolower($type));

            // get predifined attributes like id
            $attributes = $this->transpileAttributes($node->getNode(0), ['type', 'name', 'value']);
            $field->setAttributes($attributes);

            $field
                ->setDefault($node->attr('value'))
                ->setName(str_replace('\"', '', $node->attr('name')));

            // Pattern validation callback
            if(array_key_exists('pattern',$attributes)){
                $pattern = $attributes['pattern'];
                $field->setValidationCallback(function($value) use ($pattern){
                    if(preg_match('/'.$pattern.'/', $value)){
                        return false; // it's valid!
                    }
                    return true;
                });
            }

            // Set madatory if required
            if(array_key_exists('required', $attributes)) $field->setMandatory(true);

            $this->fields[$field->getName()] = $field;

        });;

        return $this->fields;
    }

    /**
     * @param $node
     * @param array $ignoreList
     * @return array
     * @throws \Exception
     */
    public function transpileAttributes(\DOMNode $node, $ignoreList = []){
        $transpiled = [];

        $attributes = $node->attributes;

        foreach($attributes as $attribute){
            // Jump to next if it's on the ignore list
            if(in_array($attribute->nodeName, $ignoreList)){
                continue;
            };
            $transpiled[$attribute->nodeName] = $attribute->nodeValue;
        }
        return $transpiled;
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
