<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 7/29/14
 * Time: 9:26 AM
 */

namespace BaseClasses\Events;


use Illuminate\Queue\Capsule\Manager;
use Illuminate\Queue\Worker;
use Silex\Application;
use Symfony\Component\EventDispatcher\Event;

class FilteredQueueGetEvent extends Event {

    protected $job;
    protected $app;
    protected $worker;

    public function __construct(Manager $manager, $job, Application $app)
    {
        $this->job = $job;
        $this->manager = $manager;
        $this->app = $app;
    }


    public function getTest()
    {
        return "You are here";
    }

    public function getJob()
    {
        return $this->job;
    }

    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * @return \Silex\Application
     */
    public function getApp()
    {
        return $this->app;
    }
} 