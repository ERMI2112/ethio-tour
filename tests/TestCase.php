<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Blade layouts resolve frontend assets through @vite, which requires
        // public/build/manifest.json. Tests must stay hermetic and must not
        // depend on a prior frontend build (CI runs them without one), so
        // Vite asset resolution is disabled for the whole suite.
        $this->withoutVite();
    }
}
