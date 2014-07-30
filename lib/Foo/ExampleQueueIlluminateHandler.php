<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 7/28/14
 * Time: 3:20 PM
 */

namespace Foo;


use Silex\Application;

class ExampleQueueIlluminateHandler {

    protected $application;

    /**
     * @TODO ideally this would be bootstapped from our application
     * so we have all the registered events.
     *
     * @param Application $application
     */
    public function __construct(Application $application = null)
    {
        $this->application = (null === $application) ? new Application() : $application;
    }

    public function fire($job, $message)
    {
        //1. all goes well remove jobs
        //2. all goes bad put it back in the queue for another try
        //3. it max tries is met add it to failed queue
        throw new \Exception("Failed");
    }


} 