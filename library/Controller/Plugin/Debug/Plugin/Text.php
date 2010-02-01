<?php
/**
 * Zkernel Zend Additions
 *
 * @category   Zkernel
 * @package    Zkernel_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/Zkernel)
 * @license    http://code.google.com/p/Zkernel/wiki/License     New BSD License
 * @version    $Id: Text.php 62 2009-05-14 09:44:38Z gugakfugl $
 */

/**
 * @category   Zkernel
 * @package    Zkernel_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/Zkernel)
 * @license    http://code.google.com/p/Zkernel/wiki/License     New BSD License
 */
class Zkernel_Controller_Plugin_Debug_Plugin_Text implements Zkernel_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * @var string
     */
    protected $_tab = '';

    /**
     * @var string
     */
    protected $_panel = '';

    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'text';

    /**
     * Create Zkernel_Controller_Plugin_Debug_Plugin_Text
     *
     * @param string $tab
     * @paran string $panel
     * @return void
     */
    public function __construct(array $options = array())
    {
        if (isset($options['tab'])) {
            $this->setTab($tab);
        }
        if (isset($options['panel'])) {
            $this->setPanel($panel);
        }
    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Sets identifier for this plugin
     *
     * @param string $name
     * @return Zkernel_Controller_Plugin_Debug_Plugin_Text Provides a fluent interface
     */
    public function setIdentifier($name)
    {
        $this->_identifier = $name;
        return $this;
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return $this->_tab;
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        return $this->_panel;
    }

    /**
     * Sets tab content
     *
     * @param string $tab
     * @return Zkernel_Controller_Plugin_Debug_Plugin_Text Provides a fluent interface
     */
    public function setTab($tab)
    {
        $this->_tab = $tab;
        return $this;
    }

    /**
     * Sets panel content
     *
     * @param string $panel
     * @return Zkernel_Controller_Plugin_Debug_Plugin_Text Provides a fluent interface
     */
    public function setPanel($panel)
    {
        $this->_panel = $panel;
        return $this;
    }
}