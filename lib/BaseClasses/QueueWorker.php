<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 7/29/14
 * Time: 12:09 PM
 */

namespace BaseClasses;


use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Queue\QueueManager;
use Illuminate\Queue\Worker;
use Symfony\Component\EventDispatcher\EventDispatcher;

class QueueWorker extends Worker {

    /**
     * Create a new queue worker.
     *
     * @param  \Illuminate\Queue\QueueManager $manager
     * @param  \Illuminate\Queue\Failed\FailedJobProviderInterface $failer
     * @param  \Symfony\Component\EventDispatcher\EventDispatcher $events
     * @return \BaseClasses\QueueWorker
     */
    public function __construct(QueueManager $manager,
                                FailedJobProviderInterface $failer = null,
                                EventDispatcher $events = null)
    {
        $this->failer = $failer;
        $this->events = $events;
        $this->manager = $manager;
    }

    public function getEvents()
    {
        return $this->events;
    }
} 