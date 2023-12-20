<?php
namespace App\Helpers;
use Illuminate\Support\Facades\DB;

class AppHelper
{
    public function __construct()
    {
        $this->third = DB::connection('third');
    }
    
    public static function generateTicketNumber()
    {
        $currDay = date('Y-m-d') . ' 07:00:00';
        $nextDay = date('Y-m-d', strtotime('+1 day', strtotime($currDay))) . ' 07:00:00';

        $dataDowntime = $this->third->table('tbl_downtime')
        ->whereBetween('createdate', [$currDay, $nextDay])
        ->selectRaw('RIGHT(ticket_number, 6) as ticket_number')
        ->orderBy('ticket_number', 'DESC')
        ->first();
        
        $ticketNo = '';
        if($dataDowntime){
            $number = (int)$dataDowntime->ticket_number+1;
            $paddedNumber = str_pad($number, 6, '0', STR_PAD_LEFT);
            $ticketNo = $paddedNumber;
        }else{
            $number = 1;
            $paddedNumber = str_pad($number, 6, '0', STR_PAD_LEFT);
            $ticketNo = $paddedNumber;
        }

        $year = date('Y');
        $month = (string)date('m');
        $date = (string)date('d');
        if(strlen($month) < 2){
            $month = '0' . $month;
        }
        if(strlen($date) < 2){
            $date = '0' . $date;
        }

        $newTicket = 'DT' . $year . $month . $date . $ticketNo;
        

        return $newTicket;
    }

}


