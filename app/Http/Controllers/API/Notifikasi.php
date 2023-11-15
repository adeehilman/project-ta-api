<?php
namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;

class Notifikasi {

    protected $title, $message, $category_notif, $badge_id, $dynamic_Id;

    public function __construct($title, $message, $category_notif, $badgeId, $dynamic_Id)
    {
        $this->title = $title;
        $this->message = $message;
        $this->category_notif = $category_notif;
        $this->badge_id = $badgeId;
        $this->dynamic_Id = $dynamic_Id;
    }
    
    public function insertNotifikasi(){
        DB::table('tbl_notification')
            ->insert([
                'title' => $this->title,
                'description' => $this->message,
                'category'  => $this->category_notif,
                'badge_id'  => $this->badge_id,
                'dynamic_id'  => $this->dynamic_Id,
                'createdate' => date('Y-m-d H:i:s')
            ]);
    }

}