<?php

namespace app\controller;

use app\helper\NalogRu;
use engine\Controller;
use engine\engine;

/**
 * @property int $inn    ИНН (только actionIndex)
 * `GET /index.php` - self::actionIndex
 * `POST /index.php` - self::actionIndexSubmit
 */
class DefaultController extends Controller
{

    public function actionIndex(array $data = null)
    {
        $this->inn = $data['inn'] ?? '';
        $this->inn = strip_tags($this->inn);
        $this->metaTitle = $this->pageTitle = 'Проверка ИНН';
        return 'form'; //имя файла представления
    }

    public function actionIndexSubmit(array $data)
    {
        $nalog = new NalogRu($data['inn'] ?? null);
        $status = $nalog->status();
        if ($status === null) {
            engine::addMessage(engine::MESSAGE_TYPE_ERROR, $nalog->getError());
            return;
        }
        engine::addMessage('success', $nalog->inn . ' ' . ($status === true ? 'является' : 'не является') . ' плательщиком налога на профессиональный доход');
        engine::redirect('');
    }

}