<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 7/29/14
 * Time: 9:26 AM
 */

namespace BaseClasses\Events;


final class QueueEvents {
    const QUEUE_JOB_ADDED_SUCCESS               = 'queue.job.added.success';
    const QUEUE_JOB_ADDED_ERROR                 = 'queue.job.added.error';
    const QUEUE_JOB_REMOVED_ERROR                 = 'queue.job.removed.error';
    const QUEUE_JOB_REMOVED_SUCCESS             = 'queue.job.removed.success';
}