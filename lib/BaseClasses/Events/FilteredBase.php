<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 7/29/14
 * Time: 11:38 AM
 */

namespace BaseClasses\Events;


use Symfony\Component\EventDispatcher\Event;

class FilteredBase extends Event {

    protected $app;

    /**
     * @return \Silex\Application
     */
    public function getApp()
    {
        return $this->app;
    }

} 