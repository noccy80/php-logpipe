<?php

namespace NoccyLabs\LogPipe\Application\LogDumper;

use Symfony\Component\EventDispatcher\Event;

class DumperEvent extends Event
{
    const DUMPING           = "dumper.dumping";
    const TERMINATING       = "dumper.terminating";
    const BEFORE_BATCH      = "dumper.batch.before";
    const AFTER_BATCH       = "dumper.batch.after";
    const SUSPEND           = "dumper.suspend";
}