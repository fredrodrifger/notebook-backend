<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('run')) {
    /**
     * House helper: invoke a named operation's handle() synchronously.
     * It is NOT a queue dispatch — queued work goes through explicit Jobs.
     */
    function run(object $action): mixed
    {
        return $action->handle();
    }
}

if (! function_exists('db_connection_ok')) {
    function db_connection_ok(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
