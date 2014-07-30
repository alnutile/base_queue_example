<?php

require_once __DIR__.'/../../vendor/autoload.php';

use BaseClasses\Events\FilteredQueueEvent;
use BaseClasses\Events\FilteredQueueEventError;
use BaseClasses\Events\QueueEvents;
use BaseClasses\QueueWorker;
use Foo\ExampleQueueClass;
use Illuminate\Queue\Worker;

\Dotenv::load(__DIR__.'/../../');

$app = new Silex\Application();

$app['debug'] = true;
$key = $_ENV['SQS_KEY'];
$secret = $_ENV['SQS_SECRET'];
$region = $_ENV['SQS_REGION'];
$queueName = $_ENV['SQS_QUEUE_NAME'];
$queueName2 = $_ENV['SQS_QUEUE_NAME2'];
$visibilityTimeout = 60;

/**
 * Logging for seeing events in the queue happen
 * Just type
 * tail -f storage/logs/base_queue.log
 * to see the output
 */
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../../storage/logs/base_queue.log',
));



/****************************************************/
/*********Setup Amazon or Beanstalkd*****************/
/****************************************************/

//Amazon Services
//$app['container.illuminate'] = new \Illuminate\Container\Container();
//$app['queue.illuminate'] = function() use ($app, $key, $secret, $queueName2, $region) {
//    $queue = new \Illuminate\Queue\Capsule\Manager($app['container.illuminate']);
//    $queue->addConnection([
//        'driver' => 'sqs',
//        'key'    => $key,
//        'secret' => $secret,
//        'queue'  => $queueName2,
//        'region' => $region
//    ], 'default');
//    $queue->setAsGlobal();
//    return $queue;
//};
//$app['queue.illuminate.queue_name'] = $_ENV['SQS_QUEUE_NAME2'];
//$app['queue.illuminate.worker'] = new \Illuminate\Queue\Worker($app['queue.illuminate']->getQueueManager(), null, null);

//Beanstalkd (for local speed testing)

$host = $_ENV['BEAN_HOST'];
//See an error
//use this to cause an error
//$host = '1.1.1.1';

$queueName = $_ENV['BEAN_QUEUE'];


/**
 * Register Events to pass around
 */

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


/****************************************************/
/*********End Setup Amazon or Beanstalkd*************/
/****************************************************/


/**
 * BasicAuth Example for later
 */
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => [
        'users' => array(
            'pattern' => '^/users',
            'http' => true,
            'users' => array(
                'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
            ),
        ),
    ]
));

$current_user = [
    'mail' => 'user@example.com',
    'active' =>  "1",
    'uuid' => 'foo-bar-foo2-bar',
    'roles' => ['foo', 'bar', 'foobar']
];

$app->get('/', function() use ($app){
    $filename = __DIR__ .'/../../readme.md';
    $readme = fopen($filename, 'r');
    return fread($readme, filesize($filename));
});

$app->get('/event_example', function() use ($app) {
   //1 Starting event
   //  show in logger
   //2 Event Failed
   //  show in logger
   //3 Event Succeeded
   //  show in logger
});

//Illuminate Examples

//@TODO use a controller to get the job

/**
 * In this example we push our Class
 * \Foo\ExampleQueueIlluminateHandler and a message
 * info the queue. Note that Class has the fire method
 */
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

/**
 * In this example we get the next job in the Queue
 * Considering it is the job and class
 * \Foo\ExampleQueueIlluminateHandler@fire
 * you can see things there and in the logs
 */
$app->get('/get_job_illuminate', function() use ($app){
    try {
        $results = $app['queue.illuminate.worker']->pop('default', $app['queue.illuminate.queue_name'], 3, 64, 30, 0);
        return $app->json("Job Cleared " . $results);
    } catch (\Exception $e) {
        $app['dispatcher']->dispatch(QueueEvents::QUEUE_JOB_REMOVED_ERROR, new FilteredQueueEventError($app['queue.illuminate'], $results, $app));
        return $app->json($e->getMessage());
    }
});

$app->run();