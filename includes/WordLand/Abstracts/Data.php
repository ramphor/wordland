<?php
namespace WordLand\Abstracts;

use JsonSerializable;
use ReflectionObject;
use ReflectionProperty;

abstract class Data implements JsonSerializable
{
    protected $dbFields = array();
    protected $dbFieldFormats = array();

    public function jsonSerialize()
    {
        $data        = array();
        $propertyRef = new ReflectionObject($this);
        $properties  = $propertyRef->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $propertyName = $property->name;
            $key = preg_replace_callback('/([a-z0-9])([A-Z]{1,})/', function ($matches) {
                return sprintf('%s_%s', $matches[1], $matches[2]);
            }, $propertyName);
            if ($key !== 'ID') {
                $key = strtolower($key);
            }
            $data[$key] = $this->$propertyName;
        }

        $hook_prefix = strtolower(str_replace('\\', '_', static::class));

        // Sample hooks: wordland_property_supported_json_fields, wordland_agent_supported_json_fields
        return apply_filters("{$hook_prefix}_supported_json_fields", $data, $this);
    }

    public function save() {
    }
}
