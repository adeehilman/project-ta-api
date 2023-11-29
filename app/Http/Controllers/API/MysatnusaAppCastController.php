<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MysatnusaAppCastController extends Controller
{
    public function getAppcast()
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <rss version="2.0" xmlns:sparkle="http://www.satnusa.com">
            <channel>
                <title>MySatnusa App - Appcast</title>
                <item>
                    <title>Version 1.6.5</title>
                    <description>Testing update dari appcast ini bro </description>
                    <pubDate>Thu, 30 Nov 2023 12:59:30 +0000</pubDate>
                    <enclosure url="https://play.google.com/store/apps/details?id=com.satnusa.karyaone_mobile" sparkle:version="1.6.5" sparkle:os="android" />
                </item>
            </channel>
        </rss>
        ';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
