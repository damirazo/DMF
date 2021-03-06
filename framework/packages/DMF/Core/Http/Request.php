<?php

    /**
     * Этот файл часть фреймворка DM Framework
     * Любое использование в коммерческих целях допустимо лишь при разрешении автора.
     *
     * @author damirazo <me@damirazo.ru>
     */

    namespace DMF\Core\Http;

    use DMF\Core\Storage\Config;

    /**
     * Class Request
     * Класс для работы с входящими запросами
     *
     * @package DMF\Core\Http
     */
    class Request
    {

        /** @var null|Request Инстанс объекта */
        private static $_instance = null;

        /** Запрет на создание объекта */
        private function __construct()
        {
        }

        /**
         * Возвращает инстанс объекта
         * @return Request|null
         */
        public static function get_instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new Request();
            }
            return self::$_instance;
        }

        /**
         * Текущий полный URL сайта
         *
         * @return string
         */
        public function url()
        {
            return $this->base_url() . '/' . substr($this->request_uri(), 1);
        }

        /**
         * Базовый URI сайта
         *
         * @return string
         */
        public function base_url()
        {
            // если базовый адрес указан в конфигурации сайта,
            // то используем его
            if (Config::get('base_url')) {
                return Config::get('base_url');
            }
            // в противном случае высчитываем сами
            $protocol = (isset($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] != 'off'
                && !empty($_SERVER['HTTPS'])) ? 'https' : 'http';

            return $protocol . '://' . $_SERVER['HTTP_HOST'];
        }

        /**
         * Строка запроса
         * @return string
         */
        public function request_uri()
        {
            // Если URI отсутствует, то указываем его как корень сайта
            $route = isset($_GET['route']) ? $_GET['route'] : '/';

            // Разбиваем URI на сегменты
            $segments = explode('/', $route);
            $new_segments = [];
            // Обходим массив сегментов
            foreach ($segments as $segment) {
                // Проверяем, что:
                // Сегмент существует
                // Сегмент не пустой
                // Сегмент не содержит символ "?"
                if (!is_null($segment) && $segment != '' && strpos($segment, '?') == false) {
                    $new_segments[] = $segment;
                }
            }
            // Если сегменты отсутствуют, то считаем текущий URI корнем сайта
            if (count($new_segments) < 1) {
                return '/';
            } // В противном случае собираем сегменты в строку и возвращаем
            else {
                $uri = implode('/', $new_segments);
                return '/' . $uri . '/';
            }
        }

        /**
         * Путь до директории со статичными файлами
         * @return mixed|string
         */
        public function static_url()
        {
            if (Config::get('static_url')) {
                return Config::get('static_url');
            }
            return $this->base_url() . 'static/';
        }

        /**
         * Получение клиентского IP адреса
         *
         * @return bool|string
         */
        public function client_ip()
        {
            $ip = false;
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
                $ip = $_SERVER['HTTP_X_REAL_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            if (strpos($ip, ';') != -1) {
                $ip = explode(';', $ip)[0];
            }
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
            return '0.0.0.0';
        }

        /**
         * Возвращение значения из $_POST массива
         * @param string     $name    Имя значения
         * @param bool|mixed $default Значение по умолчанию
         * @return bool
         */
        public function POST($name, $default = null)
        {
            if (isset($_POST[$name])) {
                return $_POST[$name];
            }
            return $default;
        }

        /**
         * Возвращение значения из $_GET массива
         * @param string     $name    Имя значения
         * @param bool|mixed $default Значение по умолчанию
         * @return bool
         */
        public function GET($name, $default = null)
        {
            if (isset($_GET[$name])) {
                return $_GET[$name];
            }
            return $default;
        }

        /**
         * Возвращение значения из $_REQUEST массива
         * @param string     $name    Имя значения
         * @param bool|mixed $default Значение по умолчанию
         * @return bool
         */
        public function REQUEST($name, $default = null)
        {
            if (isset($_REQUEST[$name])) {
                return $_REQUEST[$name];
            }
            return $default;
        }

        /**
         * Возвращает название метода, которым выполнен запрос
         * @return string
         */
        public function get_method()
        {
            return (isset($_SERVER['REQUEST_METHOD'])) ? strtolower($_SERVER['REQUEST_METHOD']) : 'unknown';
        }

        /**
         * Проверка был ли отправлен запрос через AJAX
         *
         * @return bool
         */
        public function is_ajax()
        {
            return !!(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }

        /** Запрет на копирование объекта */
        private function __clone()
        {
        }

    }
