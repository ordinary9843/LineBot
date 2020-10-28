<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Exception;

class LineHookController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $httpClient = new CurlHTTPClient(env('LINE_CHANNEL_TOKEN'));
            $bot = new LINEBot($httpClient, [
                'channelSecret' => env('LINE_CHANNEL_SECRET')
            ]);

            $signature = $request->header(LINEBot\Constant\HTTPHeader::LINE_SIGNATURE);

            if (!$signature) {
                throw new Exception('簽名無效');
            }

            $bot->parseEventRequest($request->getContent(), $signature);

            $events = $request->events;

            foreach ($events as $event) {
                if ($event['type'] !== 'message') {
                    continue;
                }

                $messageType = $event['message']['type'];
                $message = $event['message']['text'];

                if ($messageType !== 'text') {
                    continue;
                }

                if ($message === '+1') {
                    $response = $bot->replyText($event['replyToken'], '已收到您的訊息');
                    if ($response->isSucceeded()) {
                        logger('訊息已接收');
                    }
                }
            }

            return response('訊息已接收', 200);
        } catch (Exception $e) {
            return response($e->getMessage(), 403);
        }
    }
}