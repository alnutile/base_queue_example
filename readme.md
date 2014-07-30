## Base Queue Library


## Example

test/server/index shows an example use of this library

### Setup some variables from the .env file

~~~
$app['debug'] = true;
$key = $_ENV['SQS_KEY'];
$secret = $_ENV['SQS_SECRET'];
$region = $_ENV['SQS_REGION'];
$queueName = $_ENV['SQS_QUEUE_NAME'];
$queueName2 = $_ENV['SQS_QUEUE_NAME2'];
$visibilityTimeout = 60;
~~~

### Add some event listeners

~~~
$app['dispatcher']->addListener('queue.job.removed.success', function($event, $event_name, $eventDispatcher) use ($app) {
    //Get Event?
    $event->getApp()['monolog']->addInfo(sprintf("Event: Queue Job Removed Success %s", $event_name));
});

$app['dispatcher']->addListener('queue.job.added.error', function($event) use ($app) {
    $event->getApp()['monolog']->addInfo("Logging from queue.job.added.error message" . $event->getMessage());
});


$app['dispatcher']->addListener('queue.job.added.success', function($event) use ($app) {
    $event->getApp()['monolog']->addInfo("Logging from queue.job.added.success vai passed Dispatcher " . $event->getJob());
});

$app['dispatcher']->addListener('illuminate.queue.failed', function($event, $event_name, $eventDispatcher) use ($app) {
    //Get Event?
    $app['monolog']->addInfo(sprintf("Queue Job Failed $event->getMessage()"));
});
~~~


### Setup to the queue

In this one I use beanstalk in my homestead box but it shows amazon as well

~~~

$app['container.illuminate'] = new \Illuminate\Container\Container();
//@TODO is this one needed now that I pass it to the worker?
$app['container.illuminate']->singleton('dispatcher', $app['dispatcher'], true);
$app['queue.illuminate'] = function() use ($app, $host, $queueName) {
    $queue = new \Illuminate\Queue\Capsule\Manager($app['container.illuminate']);
    $queue->addConnection([
        'driver' => 'beanstalkd',
        'host'   => $host,
        'queue'  => $queueName
    ], 'default');
    $queue->setAsGlobal();
    return $queue;
};
$app['queue.illuminate.queue_name'] = $_ENV['BEAN_QUEUE'];
//$app['queue.illuminate.worker'] = new Worker($app['queue.illuminate']->getQueueManager(), null, null);
$app['queue.illuminate.worker'] = new QueueWorker($app['queue.illuminate']->getQueueManager(), null, $app['dispatcher']);

~~~

### Finally make some routes or commands that use it

Add to the queue

~~~
$app->get('/add_job_illuminate', function() use ($app) {
    $uuid = date('U');
    try {
        $results = $app['queue.illuminate']->push('\Foo\ExampleQueueIlluminateHandler', array('message' => "Some random info $uuid"));
        $results = "Added job $results";
        $status = 200;
        $app['dispatcher']->dispatch(QueueEvents::QUEUE_JOB_ADDED_SUCCESS, new FilteredQueueEvent($app['queue.illuminate'], $results, $app));
    } catch(\Exception $e) {
        $status = 400;
        $results = $e->getMessage();
        $app['dispatcher']->dispatch(QueueEvents::QUEUE_JOB_ADDED_ERROR, new FilteredQueueEventError($app['queue.illuminate'], $results, $app));
        $results = "Error adding job $results";
    }
    return $app->json($results, $status);
});
~~~

Get jobs out of the queue

~~~
$app->get('/get_job_illuminate', function() use ($app){
    try {
        $results = $app['queue.illuminate.worker']->pop('default', $app['queue.illuminate.queue_name'], 3, 64, 30, 0);
        return $app->json("Job Cleared " . $results);
    } catch (\Exception $e) {
        $app['dispatcher']->dispatch(QueueEvents::QUEUE_JOB_REMOVED_ERROR, new FilteredQueueEventError($app['queue.illuminate'], $results, $app));
        return $app->json($e->getMessage());
    }
});
~~~

In this example we are putting this classes fire method into the queue


~~~
<?php
//lib/Foo/ExampleQueueIlluminateHandler.php

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
~~~


# Running the Worker deamon (coming soon)


# Some key features

  * Has a worker to manage the queue
  * Has a failed queue table to track fails
  * Trigger events to notify on fail
  * Trigger events to remove from queue on x fails
  * Process component to run multi threaded jobs


# Running the local server

You can run this with

~~~
cd test/server
php -S 127.0.0.1:8080
~~~

Then setup your .env file by copying env_example to .env with the right Amazon settings

Finally visit

http://127.0.0.1:8080/job_add to add a job

and

http://127.0.0.1:8080/get_next_job to get that job
