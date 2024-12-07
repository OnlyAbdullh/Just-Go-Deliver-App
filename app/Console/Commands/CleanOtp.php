<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanOtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired OTPs from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('otps')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info('Expired OTPs have been cleaned.');
        return 0;
    }
}
