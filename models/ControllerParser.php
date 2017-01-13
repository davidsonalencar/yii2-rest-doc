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
        
        $controllerRoute = $module.'/'.$doc->path;
        
        $routeRulesAvailable = $this->getRouteRulesAvailable($controllerRoute);
                
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
            //$this->parseActionInline($doc, $method, $actionsAvailable);
            
            // Parse model
            $actionParser = Yii::createObject(
                [
                    'class' => '\pahanini\restdoc\models\ActionParser',
                    'reflection' => $method,
                ]
            );
            $actionDoc = new ActionDoc();
            $actionParser->parseActionInline($actionDoc, $routeRulesAvailable);
            $doc->addAction($actionDoc);
        }
                
        foreach ($object->actions() as $action) {
            
            if ($action['class'] == 'yii\rest\OptionsAction') {
                continue;
            }
            
            $action = new \ReflectionClass($action['class']);
            
            // Parse model
            $actionParser = Yii::createObject(
                [
                    'class' => '\pahanini\restdoc\models\ActionParser',
                    'reflection' => $action,
                ]
            );
            $actionDoc = new ActionDoc();
            $actionParser->parseActionClass($actionDoc, $routeRulesAvailable);
            $doc->addAction($actionDoc);
            
        }

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
        
        //$doc->model->prepare();
        //print_r($doc->model); die();
        
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
    
    public function getRouteRulesAvailable($controllerRoute) {
        $actionsAvailable = [];
        
        foreach (Yii::$app->getUrlManager()->rules as $urlRule) {
            $ref = new \ReflectionObject($urlRule);
            $p = $ref->getProperty('rules');
            $p->setAccessible(true); // <--- you set the property to public before you read the value
            
            $controllerRules = $p->getValue($urlRule);
            
            if (isset($controllerRules[$controllerRoute])) {
                $actionsAvailable = array_merge($actionsAvailable, $controllerRules[$controllerRoute]);
            }
        }

        foreach ($actionsAvailable as $key => &$action) {
            if (!isset($action->verb[0]) || $action->verb[0] == 'OPTIONS') {
                unset($actionsAvailable[$key]);
            }
        }
        
        return $actionsAvailable;

    }
}
