<?php

namespace engine;

/**
 * Контроллер для вывода HTTP-ошибок
 */
class ErrorController extends Controller
{

    /** @var string */
    public $message;

    public function action404(string $message = null): string
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', 404);
        $this->message = $message;
        return 'error';
    }

    public function action500(string $message = null): string
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        $this->message = $message;
        return 'error';
    }

}