<?php
namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;

class Notifikasi {

    protected $title, $message, $category_notif, $badge_id;

    public function __construct($title, $message, $category_notif, $badgeId)
    {
        $this->title = $title;
        $this->message = $message;
        $this->category_notif = $category_notif;
        $this->badge_id = $badgeId;
    }
    
    public function insertNotifikasi(){
        DB::table('tbl_notification')
            ->insert([
                'title' => $this->title,
                'description' => $this->message,
                'category'  => $this->category_notif,
                'badge_id'  => $this->badge_id,
                'createdate' => date('Y-m-d H:i:s')
            ]);
    }

}