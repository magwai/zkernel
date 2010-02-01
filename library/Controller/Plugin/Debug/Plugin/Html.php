<?php
/**
 * Zkernel Zend Additions
 *
 * @category   Zkernel
 * @package    Zkernel_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/Zkernel)
 * @license    http://code.google.com/p/Zkernel/wiki/License     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zkernel
 * @package    Zkernel_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/Zkernel)
 * @license    http://code.google.com/p/Zkernel/wiki/License     New BSD License
 */
class Zkernel_Controller_Plugin_Debug_Plugin_Html implements Zkernel_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'html';

    /**
     * Create Zkernel_Controller_Plugin_Debug_Plugin_Html
     *
     * @param string $tab
     * @paran string $panel
     * @return void
     */
    public function __construct()
    {

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
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return 'HTML';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $body = Zend_Controller_Front::getInstance()->getResponse()->getBody();
        $panel = '<h4>HTML Information</h4>';
        $panel .= '
        <script type="text/javascript" charset="utf-8">
            var ZFHtmlLoad = window.onload;
            window.onload = function(){
                if (ZFHtmlLoad) {
                    ZFHtmlLoad();
                }
                jQuery("#Zkernel_Html_Tagcount").html(document.getElementsByTagName("*").length);
                jQuery("#Zkernel_Html_Stylecount").html(jQuery("link[rel*=stylesheet]").length);
                jQuery("#Zkernel_Html_Scriptcount").html(jQuery("script[src]").length);
                jQuery("#Zkernel_Html_Imgcount").html(jQuery("img[src]").length);
            };
        </script>';
        $panel .= '<span id="Zkernel_Html_Tagcount"></span> Tags<br />'
                . 'HTML Size: '.round(strlen($body)/1024, 2).'K<br />'
                . '<span id="Zkernel_Html_Stylecount"></span> Stylesheet Files<br />'
                . '<span id="Zkernel_Html_Scriptcount"></span> Javascript Files<br />'
                . '<span id="Zkernel_Html_Imgcount"></span> Images<br />'
                . '<form method="POST" action="http://validator.w3.org/check" target="_blank"><input type="hidden" name="fragment" value="'.htmlentities($body).'"><input type="submit" value="Validate With W3"></form>';
        return $panel;
    }
}