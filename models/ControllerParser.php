<?php

namespace pahanini\restdoc\models;

use pahanini\restdoc\helpers\DocBlockHelper;
use phpDocumentor\Reflection\DocBlock;
use Yii;
use yii\helpers\Inflector;

class ControllerParser extends ObjectParser
{
    /**
     * @var \pahanini\restdoc\models\ModelDoc
     */
    public $model;

    /**
     * @var array Controller constructor's params
     */
    public $objectArgs = [null, null];

    /**
     * @var string Path to controllers (part of url).
     */
    public $path;

    /**
     * @var array of query tags
     */
    public $query;

    /**
     * @param \pahanini\restdoc\models\Doc
     * @return void
     */
    public function parse(Doc $doc)
    {
        if ($this->reflection->isAbstract()) {
            $this->error = $this->reflection->name . " is abstract";
            return false;
        }

        $this->parseClass($doc);

        if ($doc->getTagsByName('ignore')) {
            $this->error = $this->reflection->name . " has ignore tag";
            return false;
        }

        $module = preg_replace('/^.+\\\([\w]+)\\\controllers/', '\1', $this->reflection->getNamespaceName());
        $doc->path = Inflector::camel2id(substr($this->reflection->getShortName(), 0, -strlen('Controller')));
        
        $object = $this->getObject();
        
        $router = $module.'/'.$doc->path;
        
        $actionsAvailable = [];
        
        foreach (Yii::$app->getUrlManager()->rules as $urlRule) {
            $ref = new \ReflectionObject($urlRule);
            $p = $ref->getProperty('rules');
            $p->setAccessible(true); // <--- you set the property to public before you read the value
            
            $controllerRules = $p->getValue($urlRule);
            
            if (isset($controllerRules[$router])) {
                $actionsAvailable = array_merge($actionsAvailable, $controllerRules[$router]);
            }
        }

        foreach ($actionsAvailable as $key => &$action) {
            if (!isset($action->verb[0]) || $action->verb[0] == 'OPTIONS') {
                unset($actionsAvailable[$key]);
            }
        }
        
        /**
         * 
         */
        $actionInline = $this->reflection->getMethods();
        
        // Todas as ações inline
        foreach ($actionInline as $key => &$method) {
            if (preg_match('/^action[0-9-A-Z]/', $method->getName()) == 0) {
                unset($actionInline[$key]);
            }
        }
        
        foreach ($actionInline as $method) {
            $this->parseActionInline($doc, $method, $actionsAvailable);
        }
        
        foreach ($object->actions() as $action) {
            $action = new \ReflectionClass($action['class']);
            $this->parseAction($doc, $action, $actionsAvailable);
        }

        print_r($doc->actions);die();
//        
//        print_r($object->actions()); die();
//        
//        
//        foreach ($doc->actions as $action) {
//            print_r(implode($action->verb, '|')."\n");
//            print_r($action->name."\n");
//            print_r($action->route."\n");
//            print_r("----\n");
//        }
//               
//        
//        
//        die();
        //v1/user
        //print_r($object->module->className());die();
        
        //$doc->actions = array_keys($object->actions());

        // Parse model
        $modelParser = Yii::createObject(
            [
                'class' => '\pahanini\restdoc\models\ModelParser',
                'reflection' => new \ReflectionClass($this->getObject()->modelClass),
            ]
        );
        $doc->model = new ModelDoc();
        $modelParser->parse($doc->model);
        print_r($doc); die();
        return true;
    }

    /**
     * @param $doc
     * @return bool
     */
    public function parseClass(ControllerDoc $doc)
    {
        if (!$docBlock = new DocBlock($this->reflection)) {
            return false;
        }

        $doc->longDescription = $docBlock->getLongDescription()->getContents();
        $doc->shortDescription = $docBlock->getShortDescription();

        $doc->populateTags($docBlock);

        if (DocBlockHelper::isInherit($docBlock)) {
            $parentParser = $this->getParentParser();
            $parentParser->parseClass($doc);
        }
    }
    
    /**
     * @param $doc
     * @return bool
     */
    public function parseActionInline(ControllerDoc $doc, $methodReflection, $actionsAvailable)
    {
        if (!$docBlock = new DocBlock($methodReflection)) {
            return false;
        }
        
        $actionName = Inflector::camel2id(preg_replace('/^action/', '', $methodReflection->getName() ) );
        
        $rule = reset(array_filter($actionsAvailable, function($action) use ($actionName) {
            if (preg_match('/\/'.$actionName.'$/', $action->route)) {
                return true;
            }            
        }));
        
        $action = $doc->addAction(
                $actionName,
                $docBlock->getShortDescription(),
                $docBlock->getLongDescription()->getContents(),
                $rule->route,
                $rule->verb,
                $rule->name);
        
        foreach($docBlock->getTagsByName('param') as $tag) {
            $action->addParameter($tag->getVariableName(), $tag->getType(), $tag->getDescription());            
        }
                
        //$doc->populateTags($docBlock);

//        if (DocBlockHelper::isInherit($docBlock)) {
//            $parentParser = $this->getParentParser();
//            $parentParser->parseAction($doc, $parentParser);
//        }
    }
    
    public function parseAction(ControllerDoc $doc, $actionReflection, $actionsAvailable)
    {
        if (!$docBlock = new DocBlock($actionReflection)) {
            return false;
        }
        
//        $actionName = Inflector::camel2id(preg_replace('/^action/', '', $actionReflection->getName() ) );
//        
//        $rule = reset(array_filter($actionsAvailable, function($action) use ($actionName) {
//            if (preg_match('/\/'.$actionName.'$/', $action->route)) {
//                return true;
//            }            
//        }));
//        
//        $action = $doc->addAction(
//                $actionName,
//                $docBlock->getShortDescription(),
//                $docBlock->getLongDescription()->getContents(),
//                $rule->route,
//                $rule->verb,
//                $rule->name);
//        
//        foreach($docBlock->getTagsByName('param') as $tag) {
//            $action->addParameter($tag->getVariableName(), $tag->getType(), $tag->getDescription());            
//        }
                
        //$doc->populateTags($docBlock);

//        if (DocBlockHelper::isInherit($docBlock)) {
//            $parentParser = $this->getParentParser();
//            $parentParser->parseAction($doc, $parentParser);
//        }
    }
}
