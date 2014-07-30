<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 7/29/14
 * Time: 9:26 AM
 */

namespace BaseClasses\Events;

use Illuminate\Queue\Capsule\Manager;
use Silex\Application;
use Symfony\Component\EventDispatcher\Event;

class FilteredQueueEventError extends FilteredBase {

    protected $message;
    protected $app;
    protected $worker;

    public function __construct(Manager $manager, $message, Application $app)
    {
        $this->message = $message;
        $this->manager = $manager;
        $this->app = $app;
    }

    public function getMessage()
    {
        return $this->message;
    }
} 