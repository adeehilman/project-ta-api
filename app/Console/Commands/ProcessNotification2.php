<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class ProcessNotification2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notif:queue2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send one signal to api';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $getId = DB::select("SELECT id
            FROM tbl_meeting
            WHERE
                CONCAT(meeting_date, ' ', meeting_start) = DATE_FORMAT(NOW() + INTERVAL 10 MINUTE, '%Y-%m-%d %H:%i:00');
            ");

            $dummy = '651';
            if ($dummy) {
                foreach ($dummy as $key => $value) {
                    $client = new Client();
                    $url = env('BASE_URL') . '/api/reminder-meeting?id_meeting=' . $value->id;
                }
                $this->info('Notification processing completed.');
            } else {
                $this->info('No meetings to send notifications for at this time.');
            }

            $this->info('Notification processing completed.');
        } catch (\Throwable $th) {
            $this->error('An error occurred while processing notifications: ' . $th->getMessage());
        }
    }
}
