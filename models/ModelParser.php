<?php

namespace pahanini\restdoc\models;

use pahanini\restdoc\helpers\DocBlockHelper;
use phpDocumentor\Reflection\DocBlock;
use Yii;

/**
 * Parses Yii2 active record.
 */
class ModelParser extends ObjectParser
{
    /**
     * @param \pahanini\restdoc\models\Doc
     * @return void
     */
    public function parse(Doc $doc)
    {
        $object = $this->getObject();
        
        $this->parseClass($doc);
        
        foreach ($object->scenarios() as $key => $fields) {
            $doc->addScenario($key, $fields);
        }

        foreach ($object->extraFields() as $key => $value) {
            $doc->addExtraField(is_numeric($key) ? $value : $key);
        }

//        foreach ($object->fields() as $key => $value) {
//            $doc->addField(is_numeric($key) ? $value : $key);
//        }
        
        foreach ($object->safeAttributes() as $key => $value) {
            $name = is_numeric($key) ? $value : $key;
            $field = $doc->addField($name);
            
            $property = $doc->getProperty($name);
            if ($property) {
                $field->setType( $property->getType() );
                $field->setDescription( $property->getDescription() );
            }
            $validators = $object->getActiveValidators($value);
            foreach ($validators as $validator) {
                if ($validator->className() == 'yii\validators\RequiredValidator') {
                    $field->setRequired(true);
                }
            }
            //$validators
//            $field->setRequired();
//            print_r($validators);
//            die();
            //$doc->addField(is_numeric($key) ? $value : $key);
        }
        
        
        
        //print_r($object->safeAttributes());
        //getActiveValidators

        $this->parseFields($doc, 'fields');
        $this->parseFields($doc, 'extraFields');

        return true;
    }

    /**
     * @param $doc
     * @return bool
     */
    public function parseClass($doc)
    {
        if (!$docBlock = new DocBlock($this->reflection)) {
            return false;
        }

        $doc->populateProperties($docBlock);
        $doc->populateTags($docBlock);

        if (DocBlockHelper::isInherit($docBlock)) {
            $parentParser = $this->getParentParser();
            $parentParser->parseClass($doc);
        }
    }

    /**
     * @param \pahanini\restdoc\models\ModelDoc $doc
     * @param string $methodName
     * @return bool
     */
    public function parseFields(ModelDoc $doc, $methodName)
    {
        if (!$docBlock = new DocBlock($this->reflection->getMethod($methodName))) {
            return false;
        }

        $doc->populateTags($docBlock);

        if (DocBlockHelper::isInherit($docBlock)) {
            $parentParser = $this->getParentParser();
            $parentParser->parseFields($doc, $methodName);
        }
    }
}
