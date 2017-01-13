<?php

namespace pahanini\restdoc\models;

use phpDocumentor\Reflection\DocBlock;
use Yii;

/**
 * Class ActionDoc
 */
class ActionDoc extends Doc
{
    private $_name;
    
    private $_verb = [];
    
    private $_route = null;

    private $_rule = null;
    
    private $_parameters = [];
    
    private $_longDescription;

    private $_shortDescription;

    /**
     * @param string $name
     * @param string $type
     * @param string $description
     */
    public function addParameter($name, $type = '', $description = '')
    {
        if (!isset($this->_parameters[$name])) {
            $field = new FieldDoc();
            $field->setName($name);
            $field->setParent($this);
            $this->_parameters[$name] = $field;
        }
        $this->_parameters[$name]->setDescription($description);
        $this->_parameters[$name]->setType($type);
    }

    /**
     * @return \pahanini\restdoc\models\FieldDoc[]
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * @return bool If model has parameters
     */
    public function hasParameters()
    {
        return !empty($this->_parameters);
    }
    
    function getVerb() {
        return $this->_verb;
    }

    function getRoute() {
        return $this->_route;
    }
    
    function setVerb($verb) {
        $this->_verb = $verb;
    }

    function setRoute($route) {
        $this->_route = $route;
    }
    
    function getName() {
        return $this->_name;
    }

    function setName($name) {
        $this->_name = $name;
    }

    function getLongDescription() {
        return $this->_longDescription;
    }

    function setLongDescription($longDescription) {
        $this->_longDescription = $longDescription;
    }
    
    function getShortDescription() {
        return $this->_shortDescription;
    }

    function setShortDescription($shortDescription) {
        $this->_shortDescription = $shortDescription;
    }
    
    function getRule() {
        return $this->_rule;
    }

    function setRule($rule) {
        $this->_rule = $rule;
    }
   
}
