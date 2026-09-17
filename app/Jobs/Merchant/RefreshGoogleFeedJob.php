<?php

namespace App\Jobs\Merchant;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class RefreshGoogleFeedJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $uniqueFor = 600;

    public function handle(): void
    {
        Artisan::call('feed:build');
    }
}
