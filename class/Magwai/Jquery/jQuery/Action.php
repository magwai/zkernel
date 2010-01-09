<?php
/**
 * Class jQuery_Action
 *
 * Abstract class for any parameter of any action
 *
 * @author Anton Shevchuk
 * @access   public
 * @package  jQuery
 */
class jQuery_Action
{
    /**
     * add param to list
     * 
     * @param  string $param
     * @param  string $value
     * @return jQuery_Action
     */
    public function add($param, $value)
    {
        $this->$param = $value;
        return $this;
    }
}