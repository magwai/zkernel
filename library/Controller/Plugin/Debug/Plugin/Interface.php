<?php
/**
 * Zkernel Zend Additions
 *
 * @category   Zkernel
 * @package    Zkernel_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/Zkernel)
 * @license    http://code.google.com/p/Zkernel/wiki/License     New BSD License
 * @version    $Id: Interface.php 13 2009-04-29 21:10:38Z andreas.pankratz@s-square.de $
 */

/**
 * @category   Zkernel
 * @package    Zkernel_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/Zkernel)
 * @license    http://code.google.com/p/Zkernel/wiki/License     New BSD License
 */
interface Zkernel_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Has to return html code for the menu tab
     *
     * @return string
     */
    public function getTab();

    /**
     * Has to return html code for the content panel
     *
     * @return string
     */
    public function getPanel();

    /**
     * Has to return a unique identifier for the specific plugin
     *
     * @return string
     */
    public function getIdentifier();
}