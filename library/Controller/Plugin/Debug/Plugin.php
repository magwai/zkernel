<?php
/**
 * Zkernel Zend Additions
 *
 * @category   Zkernel
 * @package    Zkernel_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/Zkernel)
 * @license    http://code.google.com/p/Zkernel/wiki/License     New BSD License
 * @version    $Id: $
 */

/**
 * @category   Zkernel
 * @package    Zkernel_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/Zkernel)
 * @license    http://code.google.com/p/Zkernel/wiki/License     New BSD License
 */
class Zkernel_Controller_Plugin_Debug_Plugin
{
    /**
     * Transforms data into readable format
     *
     * @param array $values
     * @return string
     */
    protected function _cleanData($values)
    {
        if (is_array($values))
            ksort($values);

        $retVal = '<div class="pre">';
        foreach ($values as $key => $value)
        {
            $key = htmlspecialchars($key);
            if (is_numeric($value)) {
                $retVal .= $key.' => '.$value.'<br>';
            }
            else if (is_string($value)) {
                $retVal .= $key.' => \''.htmlspecialchars($value).'\'<br>';
            }
            else if (is_array($value))
            {
                $retVal .= $key.' => '.self::_cleanData($value);
            }
            else if (is_object($value))
            {
                $retVal .= $key.' => '.get_class($value).' Object()<br>';
            }
            else if (is_null($value))
            {
                $retVal .= $key.' => NULL<br>';
            }
        }
        return $retVal.'</div>';
    }
}