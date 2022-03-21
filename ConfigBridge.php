<?php

namespace megabike\common;

/**
 *
 * @author Rick
 */
abstract class ConfigBridge
{

    /**
     *
     * @var array
     */
    private static $callbacks = array();

    /**
     *
     * @var array|\ArrayAccess
     */
    private static $config = null;

    /**
     *
     * @var bool
     */
    private static $initialized = false;

    private static function initialize()
    {
        self::$initialized = true;
        if (defined('CONFIG_BRIDGE_CALLBACK')) {
            $callback = CONFIG_BRIDGE_CALLBACK;
            return self::initializeByCallback($callback);
        }
        if (class_exists('Yii', false)) {
            
        }
        return false;
    }

    private static function initializeByCallback($callback)
    {
        if (is_callable($callback)) {
            $array = call_user_func($callback);
            if (is_array($array)) {
                if (isset($array[''])) {
                    self::setConfigContainer($array['']);
                    unset($array['']);
                }
                self::setCallbacks($array);
                return true;
            }
        }
        return false;
    }

    public static function setConfigContainer($config)
    {
        if (is_array($config) || $config instanceof \ArrayAccess) {
            self::$config = $config;
        } else {
            throw new \InvalidArgumentException("Invalid config");
        }
    }

    public static function addCallbacks($callbacks)
    {
        foreach ((array)$callbacks as $name => $callback) {
            self::setCallback($name, $callback);
        }
    }

    public static function setCallbacks($callbacks)
    {
        self::$callbacks = array();
        self::addCallbacks($callbacks);
    }

    public static function setCallback($name, $callback)
    {
        if ($callback === null) {
            unset(self::$callbacks[$name]);
        } elseif (is_callable($callback)) {
            self::$callbacks[$name] = $callback;
        } else {
            throw new \InvalidArgumentException("Invalid callback: {$name}");
        }
    }

    public static function getCallback($name)
    {
        if (!self::$initialized) {
            self::initialize();
        }
        if (isset(self::$callbacks[$name])) {
            return self::$callbacks[$name];
        } else {
            return null;
        }
    }

    private static function getConfigValue($key, &$result = null)
    {
        if (self::$config instanceof \ArrayAccess && self::$config->offsetExists($key)) {
            $result = self::$config[$key];
            return true;
        }
        if (is_array(self::$config) && array_key_exists($key, self::$config)) {
            $result = self::$config[$key];
            return true;
        }
        return false;
    }

    private static function getValue($name, &$result = null)
    {
        $callback = self::getCallback($name);
        if ($callback !== null) {
            $result = call_user_func($callback);
            return true;
        } elseif (self::$config !== null) {
            return false;
        }
        if (self::getConfigValue($name, $result)) {
            return true;
        } elseif (substr($name, 0, 3) === 'get') {
            $key = lcfirst(substr($name, 3));
            return self::getConfigValue($key, $result);
        }
        return false;
    }

    private static function callCallback($name, $args, &$result = null)
    {
        $callback = self::getCallback($name);
        if ($callback === null) {
            return false;
        } else {
            $result = call_user_func_array($callback, $args);
            return true;
        }
    }

    public static function getAppGeneratedTime()
    {
        $result = null;
        if (self::getValue(__METHOD__, $result)) {
            return $result;
        }
        return null;
    }

    public static function getAppPath()
    {
        $result = null;
        if (self::getValue(__METHOD__, $result)) {
            return $result;
        }
        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            return $_SERVER['DOCUMENT_ROOT'];
        }
        return realpath('.');
    }

    public static function getAppDataPath()
    {
        $result = null;
        if (self::getValue(__METHOD__, $result)) {
            return $result;
        }
        return self::getAppPath().'/appdata';
    }

    public static function getAppCachePath()
    {
        $result = null;
        if (self::getValue(__METHOD__, $result)) {
            return $result;
        }
        return self::getAppDataPath().'/cache';
    }

    public static function getSourcePaths()
    {
        $result = null;
        if (self::getValue(__METHOD__, $result)) {
            return $result;
        }
        return array(self::getAppPath());
    }

    public static function getComponentConfig($id)
    {
        $result = null;
        if (self::callCallback(__METHOD__, array($id), $result)) {
            return $result;
        }
        if (self::getConfigValue($id, $result)) {
            return $result;
        }
        return null;
    }

    public static function getCharset()
    {
        $result = null;
        if (self::getValue(__METHOD__, $result)) {
            return $result;
        }
        if (function_exists('mb_internal_encoding')) {
            return mb_internal_encoding();
        }
        return 'utf-8';
    }

}
