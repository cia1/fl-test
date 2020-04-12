<?php

namespace app\helper;

use devsergeev\validators\InnValidator;
use InvalidArgumentException;
use Memcached;

/**
 * Помощник в работе с ФНС
 */
class NalogRu
{
    private const END_POINT = 'https://statusnpd.nalog.ru:443/api/v1/tracker/taxpayer_status';
    private const CACHE_TIMEOUT = 60 * 60 * 24; //Время актуальности кэша (24 часа)

    /** @var string ИНН */
    public string $inn;
    private ?string $_error;

    /**
     * @param string $inn ИНН
     */
    public function __construct(string $inn)
    {
        $this->inn = $inn;
    }

    /**
     * Выполняет проверку корректности ИНН
     * @return bool
     */
    public function validate()
    {
        InnValidator::$messageInvalidLenght = str_replace('символов', 'цифр', InnValidator::$messageInvalidLenght);
        try {
            InnValidator::check($this->inn);
        } catch (InvalidArgumentException $e) {
            $this->_error = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Выполняет запрос статуса налогоплательщика
     * @return bool|null Статус: null - не удалось выполнить запрос, возникла ошибка, true - является самозанятым, false - не является
     */
    public function status(): ?bool
    {
        if ($this->validate() === false) {
            return null;
        }
        $cache = new Memcached();
        $cache->addServer(getenv('MEMCACHED_HOST'), getenv('MEMCACHED_PORT'));
        $key = 'fns-status:' . $this->inn;
        $status = $cache->get($key);
        if ($cache->getResultCode() === Memcached::RES_SUCCESS) {
            return $status;
        }
        $status = $this->_request();
        $cache->set($key, $status, self::CACHE_TIMEOUT);
        return $status;
    }

    /**
     * Возвращает текст ошибки
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->_error;
    }

    private function _request(): ?bool
    {
        $ch = curl_init(self::END_POINT);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type:application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                'inn' => $this->inn,
                'requestDate' => date('Y-m-d')
            ])
        ]);

        $response = json_decode(curl_exec($ch), true);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            $this->_error = '#' . $code;
            if (is_array($response) === false) {
                $this->_error .= ': Сервер ФНС недоступен';
                return null;
            }
            if (isset($response['message']) === true) {
                $this->_error .= ': ' . $response['message'];
                return null;
            }
        }
        return $response['status'];
    }
}