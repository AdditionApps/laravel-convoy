<?php

namespace AdditionApps\Convoy\Support;

use AdditionApps\Convoy\Contracts\ConvoyContract;
use AdditionApps\Convoy\Contracts\ManifestContract;
use AdditionApps\Convoy\Fakes\MailFake;
use AdditionApps\Convoy\Fakes\QueueFake;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class Manifest implements ManifestContract
{
    /** @var @var \Illuminate\Queue\QueueManager */
    protected $originalQueue;

    /** @var @var \Illuminate\Mail\Mailer */
    protected $originalMail;

    public function make(ConvoyContract $convoy, callable $callback): array
    {
        $this->backupQueueImplementations();

        $this->fakeQueueImplementations();

        $callback($convoy);

        $manifest = $this->buildManifest();

        $this->restoreQueueImplementations();

        return $manifest;
    }

    protected function backupQueueImplementations(): void
    {
        $this->originalQueue = app()->make('queue');
        $this->originalMail = app()->make('mailer');
    }

    protected function fakeQueueImplementations(): void
    {
        Queue::swap(new QueueFake(Queue::getFacadeApplication()));
        Mail::swap(new MailFake());
    }

    protected function buildManifest(): array
    {
        $queuedJobs = Queue::queuedJobs();
        $queuedMailables = Mail::queuedMailables();

        return collect($queuedJobs)
            ->flatten(1)
            ->merge($queuedMailables)
            ->map(function () {
                return Str::uuid()->toString();
            })
            ->all();
    }

    protected function restoreQueueImplementations(): void
    {
        Queue::swap($this->originalQueue);
        Mail::swap($this->originalMail);
    }
}
