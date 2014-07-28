<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 7/28/14
 * Time: 12:49 PM
 */

namespace Foo;


class ExampleQueueClass {

    public function fire($job, $payload, $queue)
    {
        var_dump("Resolved and fired here");
        var_dump($job);
        //If all goes well we should clean up
        // but this could really be for events leaving us to trigger
        // SUCCESS or ERROR
        // Release the job here for now
        $queue->ack($job);
        return "This class was fired " . print_r($payload, 1);
    }
} 