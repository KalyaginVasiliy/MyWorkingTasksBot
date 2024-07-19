<?php

namespace App\Http\Controllers\Telegram;

class TimerController extends BaseController
{
    public function startTimer($chatId)
    {
        $text = "Время пошло.";
        return $this->sendMessageWithKeyboard($chatId, $text);
    }

    public function stopTimer($chatId)
    {
        $text = "Время зафиксировано.";
        return $this->sendMessageWithKeyboard($chatId, $text);
    }
}
