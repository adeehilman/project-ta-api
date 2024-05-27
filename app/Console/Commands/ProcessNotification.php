<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class ProcessNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notif:queue';

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

            $getBadge = DB::table('tbl_queuenotif')->where('is_send', 0)->where('is_log', 1)->get();

            if($getBadge){
                foreach ($getBadge as $key => $value) {
                    $client = new Client();
                    $dataOS = [
                        'badge_id' => $value->badge_id,
                        'message' => $value->message,
                        'sub_message' => $value->sub_message,
                        'category' => $value->category,
                        'tag' => $value->tag,
                        'dynamic_id' => "$value->dynamic_id",
                    ];
                    $response = $client->post(ENV('BASE_URL').'/api/notifikasi/send', [


                        'json' => $dataOS,
                    ]);

                    if ($response->getStatusCode() === 200) {
                        DB::table('tbl_queuenotif')->where('id', $value->id)->update([
                            'is_send' => 1
                        ]);
                    }

                }
                $this->info('Notification processing completed.');
            }
        } catch (\Throwable $th) {
            $this->error('An error occurred while processing notifications: ' . $th->getMessage());
        }

    }
}
