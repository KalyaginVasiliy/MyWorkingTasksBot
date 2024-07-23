<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class TelegramController extends Controller
{
    protected $telegram;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function setWebhook()
    {
        Log::info('Setting webhook');
        try {
            $url = config('services.telegram-bot-api.webhook_url');
            Log::info('Webhook URL', ['url' => $url]);
            if (!$url) {
                throw new \Exception('Webhook URL is not set in configuration');
            }
            $response = $this->telegram->setWebhook(['url' => $url]);
            Log::info('Webhook set response', ['response' => $response]);
            return response()->json(['success' => $response]);
        } catch (\Exception $e) {
            Log::error('Error setting webhook: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        Log::info('Webhook received', ['payload' => $request->all()]);
        try {
            $update = $this->telegram->getWebhookUpdate();

            if ($update->getMessage()) {
                $chatId = $update->getMessage()->getChat()->getId();
                $text = $update->getMessage()->getText();

                Log::info('Received message', ['chatId' => $chatId, 'text' => $text]);

                $reply = $this->processInput($chatId, $text);

                $response = $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $reply['text'],
                    'reply_markup' => $reply['keyboard']
                ]);

                Log::info('Sent response', ['response' => json_encode($response)]);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in webhook: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function processInput($chatId, $text)
    {
        $state = Session::get("state_{$chatId}", 'main');
        Log::info("Processing input for chat ID: $chatId, State: $state, Text: $text");

        switch ($state) {
            case 'creating_task':
                return $this->finishTaskCreation($chatId, $text);
            case 'creating_event':
                return $this->finishEventCreation($chatId, $text);
            default:
                return $this->processCommand($chatId, $text);
        }
    }

    private function processCommand($chatId, $text)
    {
        switch ($text) {
            case '/start':
                return $this->getStartMessage();
            case 'Список задач':
                return $this->showTaskListMenu();
            case 'Текущая задача':
                return $this->showCurrentTask($chatId);
            case 'Создать задачу':
                return $this->initiateTaskCreation($chatId);
            case 'Завершить текущую задачу':
                return $this->completeCurrentTask($chatId);
            case 'Запустить таймер':
                return $this->startTimer($chatId);
            case 'Остановить таймер':
                return $this->stopTimer($chatId);
            case 'Сегодня':
            case 'Завтра':
            case 'Неделя':
                return $this->showTasksForPeriod($chatId, $text);
            case 'Создать событие':
                return $this->createEvent($chatId);
            case 'Назад':
                return $this->goBack($chatId);
            case 'Запустить':
                return $this->startTask($chatId);
            case 'Отмена':
                return $this->cancelCreation($chatId);
            default:
                return $this->handleUnknownCommand($chatId, $text);
        }
    }

    private function initiateTaskCreation($chatId)
    {
        Session::put("state_{$chatId}", 'creating_task');
        $text = "Введите название новой задачи:";
        return ['text' => $text, 'keyboard' => $this->getCancelKeyboard()];
    }

    private function finishTaskCreation($chatId, $taskName)
    {
        Session::put("state_{$chatId}", 'main');
        $text = "Задача \"$taskName\" успешно создана!";
        return ['text' => $text, 'keyboard' => $this->getMainKeyboard()];
    }

    private function createEvent($chatId)
    {
        Session::put("state_{$chatId}", 'creating_event');
        $text = "Введите название нового события:";
        return ['text' => $text, 'keyboard' => $this->getCancelKeyboard()];
    }

    private function finishEventCreation($chatId, $eventName)
    {
        Session::put("state_{$chatId}", 'main');
        $text = "Событие \"$eventName\" успешно создано!";
        return ['text' => $text, 'keyboard' => $this->getMainKeyboard()];
    }

    private function cancelCreation($chatId)
    {
        Session::put("state_{$chatId}", 'main');
        $text = "Создание отменено. Выберите действие:";
        return ['text' => $text, 'keyboard' => $this->getMainKeyboard()];
    }

    private function goBack($chatId)
    {
        Session::put("state_{$chatId}", 'main');
        $text = "Выберите необходимый пункт меню:";
        return ['text' => $text, 'keyboard' => $this->getMainKeyboard()];
    }

    private function showTaskListMenu()
    {
        $text = "На какой период вы хотите посмотреть задачи?";
        return ['text' => $text, 'keyboard' => $this->getPeriodKeyboard()];
    }

    private function showTasksForPeriod($chatId, $period)
    {
        $tasks = [
            "Задача A",
            "Задача B",
            "Задача C"
        ];

        $text = "Задачи на $period:\n";
        foreach ($tasks as $index => $task) {
            $text .= ($index + 1) . ". $task\n";
        }

        return ['text' => $text, 'keyboard' => $this->getTaskListKeyboard()];
    }

    private function showCurrentTask($chatId)
    {
        $text = "Ваша текущая задача: Завершить разработку Telegram бота\n";
        $text .= "Время выполнения: 1ч 30м\n";
        $text .= "Таймер: включен";
        return ['text' => $text, 'keyboard' => $this->getCurrentTaskKeyboard()];
    }

    private function completeCurrentTask($chatId)
    {
        $text = "Текущая задача успешно завершена!";
        return ['text' => $text, 'keyboard' => $this->getMainKeyboard()];
    }

    private function startTimer($chatId)
    {
        $text = "Время пошло";
        return ['text' => $text, 'keyboard' => $this->getCurrentTaskKeyboard()];
    }

    private function stopTimer($chatId)
    {
        $text = "Время зафиксировано";
        return ['text' => $text, 'keyboard' => $this->getCurrentTaskKeyboard()];
    }

    private function handleUnknownCommand($chatId, $text)
    {
        $replyText = "Извините, я не понимаю эту команду. Выберите действие из меню.";
        return ['text' => $replyText, 'keyboard' => $this->getMainKeyboard()];
    }

    private function getStartMessage()
    {
        $text = "Привет! Я ваш бот для управления задачами. Выберите действие:";
        return ['text' => $text, 'keyboard' => $this->getMainKeyboard()];
    }

    private function getMainKeyboard()
    {
        $keyboard = [
            ['Список задач', 'Текущая задача'],
            ['Создать задачу', 'Создать событие'],
            ['Завершить текущую задачу'],
            ['Запустить таймер', 'Остановить таймер']
        ];

        return (new Keyboard)->make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);
    }

    private function getPeriodKeyboard()
    {
        $keyboard = [
            ['Сегодня', 'Завтра', 'Неделя'],
            ['Назад']
        ];

        return (new Keyboard)->make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);
    }

    private function getTaskListKeyboard()
    {
        $keyboard = [
            ['Запустить'],
            ['Назад']
        ];

        return (new Keyboard)->make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);
    }

    private function getCurrentTaskKeyboard()
    {
        $keyboard = [
            ['Завершить задачу'],
            ['Запустить таймер', 'Остановить таймер'],
            ['Назад']
        ];

        return (new Keyboard)->make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);
    }

    private function getCancelKeyboard()
    {
        $keyboard = [['Отмена']];
        return (new Keyboard)->make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);
    }

    private function startTask($chatId)
    {

        return $this->showCurrentTask($chatId);
    }
}
