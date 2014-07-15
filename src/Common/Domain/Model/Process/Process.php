<?php

namespace SaasOvation\Common\Domain\Model\Process;

use DateTimeInterface;

interface Process
{
    public function allowableDuration();

    public function canTimeout();

    public function currentDuration();

    public function description();

    public function didProcessingComplete();

    public function informTimeout(DateTimeInterface $aTimedOutDate);

    public function isCompleted();

    public function isTimedOut();

    public function notCompleted();

    public function processCompletionType();

    public function processId();

    public function startTime();

    public function timeConstrainedProcessTracker();

    public function timedOutDate();

    public function totalAllowableDuration();

    public function totalRetriesPermitted();
}