<?php

namespace engine;

/**
 * Разбивает шаблон на части и заменяет теги
 * @param string $template Имя шаблона
 */
function cacheTemplate(string $template): void
{
    $f = file_get_contents(engine::appPath() . 'template' . DIRECTORY_SEPARATOR . $template . '.php');
    $f = str_replace([
        '{{metaTitle}}',
        '{{pageTitle}}'
    ], [
        '<?=$this->metaTitle?>',
        '<?php if($this->pageTitle) echo \'<h1 class="pageTitle">\',$this->pageTitle,\'</h1>\'; ?>'
    ], $f);
    $i = strpos($f, '{{content}}');
    file_put_contents(engine::cachePath() . 'template_' . $template . '_top.html', substr($f, 0, $i));
    file_put_contents(engine::cachePath() . 'template_' . $template . '_bottom.html', substr($f, $i + 11));
}