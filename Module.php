<?php

namespace megabike\common;

abstract class Module
{

    private static $_instances = array();
    private static $_configs = array();

    /**
     * @return static
     */
    public static function instance()
    {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            $object = new $class();
            self::$_instances[$class] = $object;
            $id = $object->getModuleId();
            if (isset(self::$_configs[$id])) {
                $config = self::$_configs[$id];
            } else {
                $config = ConfigBridge::getComponentConfig($id);
            }
            $object->init($config);
        }
        return self::$_instances[$class];
    }

    public static function config($key = null)
    {
        if ($key === null) {
            return static::instance()->config;
        } else {
            return static::instance()->config[$key];
        }
    }

    public static function setModuleConfig($moduleId, $config)
    {
        self::$_configs[$moduleId] = $config;
    }

    protected static function parseClassConfig($classConfig)
    {
        if (is_string($classConfig)) {
            return array($classConfig, array());
        } elseif (is_array($classConfig)) {
            if (isset($classConfig['__class']) || array_key_exists('__class', $classConfig)) {
                $class = $classConfig['__class'];
                unset($classConfig['__class']);
            } elseif (isset($classConfig['class']) || array_key_exists('class', $classConfig)) {
                $class = $classConfig['class'];
                unset($classConfig['class']);
            } elseif (array_key_exists(0, $classConfig) && isset($classConfig[1]) && is_array($classConfig[1]) && count($classConfig) == 2) {
                $class = $classConfig[0];
                $classConfig = $classConfig[1];
            } else {
                $class = null;
            }
            return array($class, $classConfig);
        } else {
            throw new \InvalidArgumentException("Unexpected class configuration type: ".gettype($classConfig));
        }
    }

    protected $config = null;

    protected final function __construct()
    {
        
    }

    protected function init($config)
    {
        $this->config = $this->createConfig($config);
    }

    public abstract function getModuleId();

    public function getConfig()
    {
        return $this->config;
    }

    protected function createConfig($config)
    {
        if (is_object($config)) {
            return $config;
        } else {
            $classConfig = $config !== null ? $config : $this->defaultConfig();
            list($class, $params) = static::parseClassConfig($classConfig);
            if ($class === null) {
                return new ConfigContainer($params);
            } else {
                return new $class($params);
            }
        }
    }

    protected function defaultConfig()
    {
        return null;
    }

}