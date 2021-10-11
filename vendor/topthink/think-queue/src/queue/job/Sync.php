<?php

namespace think\queue\job;

require_once __DIR__ . '/../Job.php';

use think\queue\Job;

class Sync extends Job
{
    /**
     * The queue message data.
     *
     * @var string
     */
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Fire the job.
     * @return void
     */
    public function fire()
    {
        $this->resolveAndFire(json_decode($this->payload, true));
    }

    /**
     * Get the number of times the job has been attempted.
     * @return int
     */
    public function attempts()
    {
        return 1;
    }

    /**
     * Get the raw body string for the job.
     * @return string
     */
    public function getRawBody()
    {
        return $this->payload;
    }
}
