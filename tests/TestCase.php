<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Runs a test while displaying stacktrace and protecting database with a transaction
     * @param callable $toRun the test to run
     * @throws Throwable
     */
    protected function runSafeTest(callable $toRun) {
        DB::beginTransaction();
        try {
            $toRun();
        } catch (Throwable $exception) {
            print_r($exception->getMessage());
            print_r($exception->getTraceAsString());
            throw $exception;
        }
        DB::rollBack();
    }
}
