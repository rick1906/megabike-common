<?php

namespace megabike\common;

class ConfigContainer implements \ArrayAccess
{

    protected $_config;

    public static function merge($config1, $config2)
    {
        $key1 = key($config1);
        $key2 = key($config2);
        if ($key1 === 0 || $key2 === 0) {
            return $config2;
        }
        foreach ($config2 as $key => $value) {
            if (isset($config1[$key]) && is_array($config1[$key]) && is_array($value)) {
                $config1[$key] = static::merge($config1[$key], $value);
            } else {
                $config1[$key] = $value;
            }
        }
        return $config1;
    }

    public final function __construct($config = null)
    {
        if ($config !== null) {
            $this->setConfig($config);
        } else {
            $this->setDefaultConfig();
        }
    }

    protected function setDefaultConfig()
    {
        $this->_config = array();
    }

    public function setConfig($config)
    {
        $this->setDefaultConfig();
        foreach ($config as $key => $value) {
            if (property_exists($this, $key) && $key !== '_config') {
                if (is_array($this->$key) && is_array($value)) {
                    $this->$key = static::merge($this->$key, $value);
                } else {
                    $this->$key = $value;
                }
            } else {
                $this->_config[$key] = $value;
            }
        }
    }

    public function offsetExists($offset)
    {
        if (property_exists($this, $offset) && $offset !== '_config') {
            return true;
        } elseif ($this->_config !== null && array_key_exists($offset, $this->_config)) {
            return true;
        } else {
            return false;
        }
    }

    public function &offsetGet($offset)
    {
        if (property_exists($this, $offset) && $offset !== '_config') {
            return $this->$offset;
        } elseif ($this->_config !== null && array_key_exists($offset, $this->_config)) {
            return $this->_config[$offset];
        } else {
            throw new \OutOfBoundsException("Property '{$offset}' does not exist");
        }
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            throw new \InvalidArgumentException("Using '[] =' is unsupported for class '".get_class($this)."'");
        } elseif (property_exists($this, $offset) && $offset !== '_config') {
            $this->$offset = $value;
        } else {
            $this->_config[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset) && $offset !== '_config') {
            unset($this->$offset);
        } else {
            unset($this->_config[$offset]);
        }
    }

}
