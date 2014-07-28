<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 7/28/14
 * Time: 11:16 AM
 */

namespace Simple;
use Pekkis\Queue\Queue;

class QueueJobResolver {

    protected $app;

    /**
     * @var $queue \Pekkis\Queue\Queue;
     */
    protected $queue;

    protected $job;



    public function __construct($app = null)
    {
        $this->app = $app;
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

} 