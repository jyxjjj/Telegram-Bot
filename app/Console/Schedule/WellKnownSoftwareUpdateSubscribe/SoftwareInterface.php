<?php

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe;

interface SoftwareInterface
{
    public function getVersion(): string;
}
