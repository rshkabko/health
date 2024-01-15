<?php

namespace Flamix\Health\Checks;

use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Support\Facades\Redis;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

/**
 * Queue count monitor.
 */
class QueueCountMonitor extends Check
{
    private ?int $count = null;

    public function count(int $count): self
    {
        $this->count = $count;
        return $this;
    }

    public function run(): Result
    {
        $result = Result::make();
        $manager = app(QueueFactory::class);
        $connection = $manager->connection(config('queue.default'));

        if (!$this->count) {
            $this->count(config('queue.max', 500));
        }

        // Request working queue
        $running_queues = Redis::command('keys', ['queues:*']);

        // Parse queue and check size
        foreach ($running_queues as $queue) {
            preg_match('/^[^:]+:([^:]+)/', $queue, $matches);
            if (empty($matches[1])) {
                $matches[1] = 'default';
            }

            // Counting...
            $queues[$matches[1]] = $connection->size($matches[1]);
        }

        // General Size
        $queue_general_size = array_sum($queues ?? []);

        if ($queue_general_size >= $this->count / 2) {
            return $result->warning("The queue size is large, delays in the application may occur!");
        }

        if ($queue_general_size >= $this->count) {
            return $result->failed("The queue size is CRITICAL. Large delays in the application. Additional processes need to be launched to process the queues!");
        }

        return $result->ok();
    }
}