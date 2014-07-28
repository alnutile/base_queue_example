<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Pekkis\Queue\Adapter\AmazonSQSAdapter;
use Pekkis\Queue\Message;
use Pekkis\Queue\Queue;
use Simple\QueueJobResolver;
use Foo\ExampleQueueClass;

\Dotenv::load(__DIR__.'/../../');

$app = new Silex\Application();
$app['debug'] = true;
$key = $_ENV['SQS_KEY'];
$secret = $_ENV['SQS_SECRET'];
$region = $_ENV['SQS_REGION'];
$queueName = $_ENV['SQS_QUEUE_NAME'];
$visibilityTimeout = 60;
$app['queue'] = new Queue(new AmazonSQSAdapter($key, $secret, $region, $queueName, $visibilityTimeout));
$app['example.queue.class'] = new \Foo\ExampleQueueClass();
$app['queue.resolver'] = new \Simple\QueueJobResolver($app);



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

$app->get('/get_next_job', function() use ($app){
    $example_output = "No jobs";
    //1. get the queue
    $queue = $app['queue'];
    $received = $queue->dequeue();
    //  if there is a message
    if ($received != null) {
        //2. get the data from it and de-serialize it
        $data = $received->getData();
        //3. call in the queue.resolver I added to the $app earlier
        $queueResolver = $app['queue.resolver'];
        //4. set a job on the resolver for later use so we are not
        //   passing around the world of the app
        $queueResolver->setJob($received);
        $queueResolver->setQueue($queue);
        //5. finally resolve the controller and method stored in the queue and pass the data
        $resolved = $queueResolver->resolveAndFire(['job' => $data['class'], 'data' => $data['data']]);
        //6. Make some example output for this demo.
        $example_output = [
            'class_results' => $resolved
        ];
    }
    //Now that we have $app we can get the job, find the class
    // and run that with all the benefits of the $app
    return $app->json($example_output);
});

$app->get('/get_next_job_using_controller', 'Simple\\QueueJobResolver::getNextJob');


$app->get('/job_add_example', function () use ($app) {
    $uuid = date('U');
    $app['queue']->enqueue($_ENV['SQS_QUEUE_NAME'], [
        'class' => 'example.queue.class', //as registered with the Dependency Injection Component
        'data'  => "some random data here eg $uuid"
    ]);

    return $app->json("Job added");
});


$app->run();