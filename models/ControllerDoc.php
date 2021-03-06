<?php

namespace pahanini\restdoc\models;

use phpDocumentor\Reflection\DocBlock;

/**
 * Class ControllerDoc
 *
 * @property string $shortDescription
 * @property string $longDescription
 * @property string $category
 * @property string $module
 * @property \pahanini\restdoc\models\ActionDoc[] $actions
 */
class ControllerDoc extends Doc
{
    /**
     * @var \pahanini\restdoc\models\ActionDoc[]
     */
    private $_actions = [];

    /**
     * @var \pahanini\restdoc\models\ModelDoc
     */
    public $model;

    /**
     * @var
     */
    public $path;

    /**
     * @var
     */
    public $query = [];

    /**
     * @var array Keeps attached labels.
     */
    private $_labels = [];

    /**
     * @var string Long description
     */
    private $_longDescription;

    /**
     * @var string Short description of controller
     */
    private $_shortDescription;
    
    /**
     * @var string Category
     */
    private $_category;
    
    /**
     * @var string Module
     */
    private $_module;

    /**
     * @return string
     */
    public function getLongDescription()
    {
        return $this->_longDescription;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->_shortDescription;
    }

    /**
     * @param $value
     * @return bool If label attached to doc
     */
    public function hasLabel($value)
    {
        return isset($this->_labels[$value]);
    }

    /**
     * Prepares doc
     */
    public function prepare()
    {
        parent::prepare();

        foreach ($this->getTagsByName('label') as $tag) {
            $this->_labels[$tag->getContent()] = true;
        }

        $this->query = $this->getTagsByName('query');

        if ($this->model) {
            $this->model->prepare();
        }
    }

    /**
     * @param $value
     */
    public function setShortDescription($value)
    {
        if (!$this->_shortDescription && $value) {
            $this->_shortDescription = $value;
        }
    }

    /**
     * @param $value
     */
    public function setLongDescription($value)
    {
        if (!$this->_longDescription && $value) {
            $this->_longDescription = $value;
        }
    }
    
    /**
     * @param \pahanini\restdoc\models\ActionDoc $doc
     */
    public function addAction($doc)
    {
        $this->_actions[] = $doc;
    }    
    
//    public function addAction($name, $shortDescription = '', $longDescription = '', $controller = '', $verb = [], $route = '')
//    {
//        if (!isset($this->_actions[$name])) {
//            $action = new ActionDoc();
//            $action->setName($name);
//            $action->setParent($action);
//            $this->_actions[$name] = $action;
//        }
//        $this->_actions[$name]->setController($controller);
//        $this->_actions[$name]->setVerb($verb);
//        $this->_actions[$name]->setRoute($route);
//        $this->_actions[$name]->setShortDescription($shortDescription);
//        $this->_actions[$name]->setLongDescription($longDescription);
//        
//        return $this->_actions[$name];
//    }
    
    /**
     * 
     */
    public function getActions()
    {
        return $this->_actions;
    }
    
    function getCategory() {
        return $this->_category;
    }

    function setCategory($_category) {
        $this->_category = $_category;
    }
    
    function getModule() {
        return $this->_module;
    }

    function setModule($module) {
        $this->_module = $module;
    }
    
}
