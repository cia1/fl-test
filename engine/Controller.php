<?php
/** @noinspection PhpIncludeInspection */

namespace engine;

/**
 * Базовый WEB-контроллер
 */
class Controller
{

    /** @var string META заголовок страницы (<title>) */
    public string $metaTitle = '';
    /** @var string Отображаемый <H1> заголовок страницы */
    public string $pageTitle = '';

    /** @var string|null */
    private ?string $_template = 'default';

    /**
     * Генерирует HTML-представление страницы
     * @param string|null $view Имя файла представления
     */
    public function render(string $view = null): void
    {
        if ($this->_template !== null) {
            engine::cacheTemplate($this->_template);
            include engine::cachePath() . 'template_' . $this->_template . '_top.html';
        }
        if ($view !== null) {
            if (is_string($view) === true) {
                $controller = get_class($this);
                $controller = substr($controller, strrpos($controller, '\\') + 1, -10);
                include(engine::appPath() . 'view' . DIRECTORY_SEPARATOR . $controller . ucfirst($view) . '.php');
            } else {
                echo json_encode($view, JSON_UNESCAPED_UNICODE);
            }
        }
        if ($this->_template !== null) {
            include engine::cachePath() . 'template_' . $this->_template . '_bottom.html';
        }
    }

    /**
     * Устанавливает шаблон страницы. Если null, то шаблон использоваться не будет
     * @param string|null $template
     */
    protected function setTemplate(?string $template)
    {
        $this->_template = $template;
    }

}