<?php

namespace Akaunting\Firewall\Listeners;

use Akaunting\Firewall\Events\AttackDetected;
use Illuminate\Http\Exceptions\HttpException;
use Illuminate\Http\JsonResponse;
use Akaunting\Firewall\Traits\Helper;
use Akaunting\Firewall\Models\Ip;
use Illuminate\Auth\Events\Failed as Event;
use Illuminate\Http\Response; // Tambahkan use statement untuk Response

class CheckLogin
{
    use Helper;

    public function handle(Event $event): void
    {
        $this->request = request();
        $this->middleware = 'login';
        $this->user_id = 0;

        if ($this->skip($event)) {
            return;
        }

        $this->request['password'] = '******';

        $log = $this->log();

        // Pengecekan apakah IP terblokir
        $ipBlocked = Ip::blocked($this->request->ip())->exists();

      

        event(new AttackDetected($log));
        if ($ipBlocked) {
            // IP terblokir, Anda dapat mengambil langkah-langkah respons yang sesuai
            // Misalnya, meneruskan pesan ke view atau melempar pengecualian
            // ...

            $response = new JsonResponse(['message' => 'Access Denied', 'blocked_ip' => $this->request->ip()], Response::HTTP_FORBIDDEN);

            // // Kirim response dengan pesan khusus dan informasi IP yang diblokir
            throw new \Illuminate\Http\Exceptions\HttpResponseException($response);
            // abort(403, 'Access Denied', ['blocked_ip' => $this->request->ip()], 0);


        }
    }

    public function skip($event): bool
    {
        if ($this->isDisabled()) {
            return true;
        }

        if ($this->isWhitelist()) {
            return true;
        }

        return false;
    }
}
