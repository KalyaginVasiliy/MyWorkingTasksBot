<?php

namespace App\Http\Controllers\Telegram;

class EventController extends BaseController
{
    public function createEvent($chatId, $text)
    {
        $responseText = "Событие '$text' создано.";
        return $this->sendMessageWithKeyboard($chatId, $responseText);
    }
}
