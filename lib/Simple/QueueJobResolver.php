<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 7/28/14
 * Time: 11:16 AM
 */

namespace Simple;
use Pekkis\Queue\Queue;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class QueueJobResolver {

    /**
     * @var \Silex\Application
     */
    protected $app;

    /**
     * @var $queue \Pekkis\Queue\Queue;
     */
    protected $queue;

    protected $job;
    protected $data;
    protected $resolved;
    protected $request;


    public function __construct(Application $app = null)
    {
        $this->app = $app;
    }

    public function getNextJob(Request $request, Application $app)
    {
        $this->setRequest($request);
        $this->setApp($app);

        $queue = $this->app['queue'];
        $received = $queue->deQueue();
        if ($received != null)
        {
            $this->data = $received->getData();
            $this->setJob($received);
            $this->setQueue($queue);
            $this->resolved = $this->resolveAndFire(['job' => $this->data['class'], 'data' => $this->data['data']]);
        }
        return $this->app->json($this->resolved);
    }

    /**
     * @return null
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param null $app
     * @return $this
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    public function resolveAndFire(array $payload)
    {
        list($class, $method) = $this->parseJob($payload['job']);

        $this->instance = $this->resolve($class);
        $this->instance->{$method}($this->getJob(), $payload['data'], $this->getQueue());
    }

    protected function resolve($class)
    {
        return $this->app[$class];
    }

    protected function parseJob($job)
    {
        $segments = explode('@', $job);

        return count($segments) > 1 ? $segments : array($segments[0], 'fire');
    }

    /**
     * @return mixed
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param mixed $job
     */
    public function setJob($job)
    {
        $this->job = $job;
    }

    /**
     * @return \Pekkis\Queue\Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param \Pekkis\Queue\Queue $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
        return $this;
    }

    private function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

} 