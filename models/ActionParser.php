<?php

namespace pahanini\restdoc\models;

use pahanini\restdoc\helpers\DocBlockHelper;
use phpDocumentor\Reflection\DocBlock;
use yii\helpers\Inflector;
use Yii;

/**
 * Parses action in line
 */
class ActionParser extends ObjectParser
{
    /**
     * @param \pahanini\restdoc\models\Doc
     * @return void
     */
//    public function parse(Doc $doc)
//    {
//        $object = $this->getObject();
//
//        //$this->parseActionInline($doc, 'extraFields');
//
//        return true;
//    }

    /**
     * @param $doc
     * @return bool
     */
//    public function parseClass($doc)
//    {
//        if (!$docBlock = new DocBlock($this->reflection)) {
//            return false;
//        }
//
//        $doc->populateProperties($docBlock);
//        $doc->populateTags($docBlock);
//
//        if (DocBlockHelper::isInherit($docBlock)) {
//            $parentParser = $this->getParentParser();
//            $parentParser->parseClass($doc);
//        }
//    }

    /**
     * @param \pahanini\restdoc\models\ActionDoc $doc
     * @param array $routeRulesAvailable
     * @return bool
     */
    public function parseActionInline(ActionDoc $doc, $routeRulesAvailable)
    {
        if (!$docBlock = new DocBlock($this->reflection)) {
            return false;
        }

        $doc->populateTags($docBlock);
        
        $actionName = Inflector::camel2id(preg_replace('/^action/', '', $this->reflection->getName() ) );
        
        $rule = reset(array_filter($routeRulesAvailable, function($action) use ($actionName) {
            if (preg_match('/\/'.$actionName.'$/', $action->route)) {
                return true;
            }            
        }));
        
        //$doc->setParent($this);
        $doc->setName($actionName);
        $doc->setShortDescription($docBlock->getShortDescription());
        $doc->setLongDescription($docBlock->getLongDescription()->getContents());
        
        if (!empty($rule)) {
            $doc->setRule($rule->name);
            $doc->setRoute($rule->route);
            $doc->setVerb($rule->verb);
        }
                
        foreach($docBlock->getTagsByName('param') as $tag) {
            $doc->addParameter($tag->getVariableName(), $tag->getType(), $tag->getDescription());            
        }

        if (DocBlockHelper::isInherit($docBlock)) {
            $parentParser = $this->getParentParser();
            $parentParser->parseActionInline($doc);
        }
    }
    
    public function parseActionClass(ActionDoc $doc, $routeRulesAvailable)
    {
        if (!$docBlock = new DocBlock($this->reflection->getMethod('run'))) {
            return false;
        }

        $doc->populateTags($docBlock);
        
        $actionName = Inflector::camel2id(substr($this->reflection->getShortName(), 0, -strlen('Action')));
        
        if ($actionName === 'options') {
            return;
        }
        
        $rule = reset(array_filter($routeRulesAvailable, function($action) use ($actionName) {
            if (preg_match('/\/'.$actionName.'$/', $action->route)) {
                return true;
            }            
        }));
       
        //$doc->setParent($this);
        $doc->setName($actionName);
        $doc->setShortDescription($docBlock->getShortDescription());
        $doc->setLongDescription($docBlock->getLongDescription()->getContents());
        
        if (!empty($rule)) {
            $doc->setRule($rule->name);
            $doc->setRoute($rule->route);
            $doc->setVerb($rule->verb);
        }
        
        foreach($docBlock->getTagsByName('param') as $tag) {
            $doc->addParameter($tag->getVariableName(), $tag->getType(), $tag->getDescription());            
        }
        
        if (DocBlockHelper::isInherit($docBlock)) {
            $parentParser = $this->getParentParser();
            $parentParser->parseActionClass($doc);
        }
    }
}
