<?php

namespace App\Console\Commands;

use App\Models\TokenBlacklist;
use Illuminate\Console\Command;

class CleanTokenBlacklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-token-blacklist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired tokens from the blacklist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        TokenBlacklist::where('expires_at', '<', now())->delete();
        $this->info('Expired tokens removed from blacklist.');
    }
}
