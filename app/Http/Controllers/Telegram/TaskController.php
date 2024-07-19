<?php

namespace App\Http\Controllers\Telegram;

class TaskController extends BaseController
{
    public function showTaskList($chatId)
    {
        $text = "На какой период?";
        $keyboard = [
            ['Сегодня', 'Завтра', 'Неделя']
        ];
        return $this->sendMessageWithKeyboard($chatId, $text, $keyboard);
    }

    public function showTasksForPeriod($chatId, $period)
    {
        $tasks = $this->getTasksForPeriod($period);
        $response = "Задачи на $period:\n\n";
        foreach ($tasks as $task) {
            $response .= "- $task\n";
        }
        return $this->sendMessageWithKeyboard($chatId, $response, [['Запустить']]);
    }

    private function getTasksForPeriod($period)
    {
        return [
            "Задача 1",
            "Задача 2",
            "Задача 3"
        ];
    }

    public function showCurrentTask($chatId)
    {
        $task = $this->getCurrentTask();
        $text = "Текущая задача: {$task['name']}\nТаймер: {$task['timer']}\nСтатус таймера: {$task['timerStatus']}";
        $keyboard = [
            [$task['timerStatus'] == 'Включен' ? 'Остановить таймер' : 'Запустить таймер'],
            ['Завершить задачу']
        ];
        return $this->sendMessageWithKeyboard($chatId, $text, $keyboard);
    }

    private function getCurrentTask()
    {
        return [
            'name' => 'Пример текущей задачи',
            'timer' => '00:15',
            'timerStatus' => 'Выключен'
        ];
    }

    public function startTask($chatId)
    {
        $text = "Задача запущена в работу.";
        return $this->showCurrentTask($chatId);
    }

    public function completeCurrentTask($chatId)
    {
        $text = "Задача 'Пример задачи' завершена.";
        return $this->sendMessageWithKeyboard($chatId, $text);
    }

    public function createTask($chatId, $text)
    {
        $responseText = "Задача '$text' создана.";
        return $this->sendMessageWithKeyboard($chatId, $responseText);
    }
}
