<?php

namespace App\Console\Commands\Merchant;

use Illuminate\Console\Command;

class GenerateGoogleFeedCommand extends Command
{
    protected $signature = 'merchant:google-feed {--limit= : Override config(feed.target_items)}';

    protected $description = 'Génère le flux Google Merchant Center (alias de feed:build)';

    public function handle(): int
    {
        return $this->call('feed:build', array_filter([
            '--limit' => $this->option('limit'),
        ], fn ($v) => $v !== null && $v !== false && $v !== ''));
    }
}
