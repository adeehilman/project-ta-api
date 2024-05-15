<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Scheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $listId = DB::select("SELECT id
        // FROM tbl_meeting
        // WHERE
        //     DATE(meeting_date) = CURRENT_DATE
        //     AND TIMEDIFF(CONCAT(CURRENT_DATE, ' ', meeting_start), CURRENT_TIMESTAMP) <= '00:15:00'
        //     AND TIMEDIFF(CONCAT(CURRENT_DATE, ' ', meeting_start), CURRENT_TIMESTAMP) >= '00:00:00';
        //     AND statusmeeting_id IN (2,3)
        // ");

        $this->info('Custom task executed successfully!');
    }
}
