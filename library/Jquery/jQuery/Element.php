<?php
/**
 * jQuery_Element - class for work with jQuery framework
 *
 * @author Anton Shevchuk
 * @access   public
 * @package  jQuery
 */
class jQuery_Element
{
    /**
     * selector path
     * @var string
     */
    public $s;
    
    /**
     * methods
     * @var array
     */
    public $m = array();
    
    /**
     * args
     * @var array
     */
    public $a = array();
    
    /**
     * __construct
     * contructor of jQuery
     *
     * @return jQuery_Element
     */
    public function __construct($selector)
    {
        jQuery::addElement($this); 
        $this->s = $selector;
    }
    
    /**
     * __call
     *
     * @return jQuery_Element
     */
    public function __call($method, $args)
    {
        array_push($this->m, $method);
        array_push($this->a, $args);
        
        return $this;
    }
    
    /**
     * end
     * need to create new jQuery
     *
     * @return jQuery_Element
     */
    public function end()
    {
        return new jQuery_Element($this->s);
    }
}