<?php

namespace engine;

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use Dotenv\Dotenv;

/**
 * Эмуляция полноценного фреймворка
 */
class engine
{

    //Группы (типы) сообщений пользователю
    const MESSAGE_TYPE = [self::MESSAGE_TYPE_ERROR, self::MESSAGE_TYPE_SUCCESS];
    const MESSAGE_TYPE_ERROR = 'error';
    const MESSAGE_TYPE_SUCCESS = 'success';

    /**
     * Возвращает путь к директории приложения
     * @return string
     */
    public static function appPath(): string
    {
        static $path;
        if ($path === null) {
            $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
        }
        return $path;
    }

    /**
     * Возвращает путь директории кеша
     * @return string
     */
    public static function cachePath(): string
    {
        static $path;
        if ($path === null) {
            $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        }
        return $path;
    }

    /**
     * Кеширует шаблон, заменяя в нём теги
     * @param string $template Имя шаблона
     */
    public static function cacheTemplate(string $template): void
    {
        if (file_exists(self::cachePath() . 'template_' . $template . '_top.html') === true) {
            return;
        }
        require_once __DIR__ . '/cacheTemplate.php';
        cacheTemplate($template);
    }

    /**
     * Добавляет сообщение, которое должно быть опубликовано на странице
     * @param string $type    Группа сообщения
     * @param string $message Текст сообщения
     * @throws InvalidArgumentException
     * @see self::MESSAGE_TYPE
     */
    public static function addMessage(string $type, string $message): void
    {
        if (in_array($type, static::MESSAGE_TYPE) === false) {
            throw new InvalidArgumentException('Unknown message type');
        }
        if (isset($_SESSION['__messages']) === false) {
            $_SESSION['__messages'] = [];
        }
        if (isset($_SESSION['__messages'][$type]) === false) {
            $_SESSION['__messages'][$type] = [];
        }
        $_SESSION['__messages'][$type][] = $message;
    }

    /**
     * Проверяет есть ли сообщения
     * @param string $type Группа сообщений
     * @return bool
     */
    public static function issetMessage(string $type): bool
    {
        return isset($_SESSION['__messages'][$type]);
    }

    /**
     * Возвращает добавленные ранее сообщения
     * @param string $type
     * @param bool   $clear Удалить сообщения
     * @return array
     */
    public static function getMessage(string $type, bool $clear = false): array
    {
        $message = $_SESSION['__messages'][$type] ?? [];
        if ($clear === true) {
            static::clearMessage($type);
        }
        return $message;
    }

    /**
     * Очищает сообщения
     * @param string $type Группа сообщений
     */
    public static function clearMessage(string $type): void
    {
        unset($_SESSION['__messages'][$type]);
        if (isset($_SESSION['__messages']) === true && empty($_SESSION['__messages']) === true) {
            unset($_SESSION['__messages']);
        }
    }

    /**
     * Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес
     * @param string      $route   URL в формате "controller/etc"
     * @param string|null $message Если задан, то установит текст сообщения об успешно выполненной операции
     * @param int         $code    HTTP-код ответа
     * @see self::success()
     */
    public static function redirect(string $route, ?string $message = null, int $code = 302): void
    {
        if ($message !== null) {
            self::addMessage(self::MESSAGE_TYPE_SUCCESS, $message);
        }
        if ($route) {
            $route = '/index.php?r=' . $route;
        }
        header('Location: /' . $route, true, $code);
        exit;
    }

    /**
     * Запускает выполнение приложения
     * @param string $route Маршрут запрошенной страницы
     * @throws HTTPException
     */
    public static function execute(string $route): void
    {
        session_start();
        $routes = include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'route.php';
        if (isset($routes[$route]) === false) {
            throw new HTTPException(404);
        }
        if (isset($route[1]) === false) {
            $route[1] = 'Index';
        }
        $route = explode('/', $routes[$route] ?? $routes[null]);
        $controller = '\app\controller\\' . ucfirst($route[0]) . 'Controller';
        /** @var Controller $controller */
        $controller = new $controller();

        //Вызов действия для POST...
        if (isset($_POST[$route[0]]) === true) {
            $method = 'action' . ucfirst($route[1] . 'Submit');
            $post = $_POST[$route[0]];
            if (is_callable([$controller, $method]) === true) {
                $view = $controller->$method($post);
                if ($view !== null) {
                    $controller->render($view);
                    return;
                }
            }
        } else {
            $post = null;
        }

        //Вызов действия для GET...
        $method = 'action' . ucfirst($route[1]);
        if (is_callable([$controller, $method]) === false) {
            throw new HTTPException(404);
        }
        $controller->render($controller->$method($post));
    }

}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

class HTTPException extends RuntimeException
{

    public function __construct(int $code, string $message = 'Requested page not found', Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}