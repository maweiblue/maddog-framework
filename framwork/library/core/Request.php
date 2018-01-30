<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/27
 * Time: 9:20
 */

namespace core;


class Request
{
    protected static $instance;

    protected $method;

    protected $domain;
    protected $url;
    protected $baseUrl;
    protected $baseFile;

    protected $root;
    protected $pathinfo;
    protected $path;


    /**
     * @var array 当前调度信息
     */
    protected $dispatch = [];
    protected $module;
    protected $controller;
    protected $action;

    /**
    * @var array 请求参数
    */
    protected $param   = [];
    protected $get     = [];
    protected $post    = [];
    protected $request = [];
    protected $route   = [];
    protected $put;
    protected $session = [];
    protected $file    = [];
    protected $cookie  = [];
    protected $server  = [];
    protected $header  = [];
    protected $input=    [];

    protected $mimeType = [
        'xml'   => 'application/xml,text/xml,application/x-xml',
        'json'  => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js'    => 'text/javascript,application/javascript,application/x-javascript',
        'css'   => 'text/css',
        'rss'   => 'application/rss+xml',
        'yaml'  => 'application/x-yaml,text/yaml',
        'atom'  => 'application/atom+xml',
        'pdf'   => 'application/pdf',
        'text'  => 'text/plain',
        'image' => 'image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*',
        'csv'   => 'text/csv',
        'html'  => 'text/html,application/xhtml+xml,*/*',
    ];


    protected $content;
    // 全局过滤规则
    protected $filter;
    // Hook扩展方法
    protected static $hook = [];
    // 绑定的属性
    protected $bind = [];

    // 请求缓存
    protected $cache;
    // 缓存是否检查
    protected $isCheckCache;

    protected function __construct($options = [])
    {
        foreach ($options as $name => $item) {
            if (property_exists($this, $name)) {
                $this->$name = $item;
            }
        }

        if (is_null($this->filter)) {
            $this->filter = Config::get('default_filter');
        }
    }


    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }


    public static function create($uri, $method = 'GET', $params = [], $cookie = [], $files = [], $server = [], $content = null)
    {
        $server['PATH_INFO']      = '';
        $server['REQUEST_METHOD'] = strtoupper($method);
        $info                     = parse_url($uri);
        if (isset($info['host'])) {
            $server['SERVER_NAME'] = $info['host'];
            $server['HTTP_HOST']   = $info['host'];
        }
        if (isset($info['scheme'])) {
            if ('https' === $info['scheme']) {
                $server['HTTPS']       = 'on';
                $server['SERVER_PORT'] = 443;
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = 80;
            }
        }
        if (isset($info['port'])) {
            $server['SERVER_PORT'] = $info['port'];
            $server['HTTP_HOST']   = $server['HTTP_HOST'] . ':' . $info['port'];
        }
        if (isset($info['user'])) {
            $server['PHP_AUTH_USER'] = $info['user'];
        }
        if (isset($info['pass'])) {
            $server['PHP_AUTH_PW'] = $info['pass'];
        }
        if (!isset($info['path'])) {
            $info['path'] = '/';
        }
        $options = [];
        $options[strtolower($method)] = $params;
        $queryString                  = '';
        if (isset($info['query'])) {
            parse_str(html_entity_decode($info['query']), $query);
            if (!empty($params)) {
                $params      = array_replace($query, $params);
                $queryString = http_build_query($query, '', '&');
            } else {
                $params      = $query;
                $queryString = $info['query'];
            }
        } elseif (!empty($params)) {
            $queryString = http_build_query($params, '', '&');
        }

        if ($queryString) {
            parse_str($queryString, $get);
            $options['get'] = isset($options['get']) ? array_merge($get, $options['get']) : $get;
        }

        $server['REQUEST_URI']  = $info['path'] . ('' !== $queryString ? '?' . $queryString : '');
        $server['QUERY_STRING'] = $queryString;

        $options['cookie']      = $cookie;
        $options['param']       = $params;
        $options['file']        = $files;
        $options['server']      = $server;
        $options['url']         = $server['REQUEST_URI'];
        $options['baseUrl']     = $info['path'];
        $options['pathinfo']    = '/' == $info['path'] ? '/' : ltrim($info['path'], '/');
        $options['method']      = $server['REQUEST_METHOD'];
        $options['domain']      = isset($info['scheme']) ? $info['scheme'] . '://' . $server['HTTP_HOST'] : '';
        $options['content']     = $content;

        self::$instance         = new self($options);
        return self::$instance;
    }


    public function domain($domain = null)
    {
        if (!is_null($domain)) {
            $this->domain = $domain;
            return $this;
        } elseif (!$this->domain) {
            $this->domain = $this->scheme() . '://' . $this->host();
        }
        return $this->domain;
    }


    public  function url($url=null){
        if(!is_null($url)&&true!==$url){
            $this->url=$url;
            return $this;
        }elseif (!$this->url){
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
                $this->url = $_SERVER['HTTP_X_REWRITE_URL'];
            } elseif (isset($_SERVER['REQUEST_URI'])) {
                $this->url = $_SERVER['REQUEST_URI'];
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
                $this->url = $_SERVER['ORIG_PATH_INFO'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
            } else {
                    $this->url = '';
            }
         }
         return true === $url?$this->domain().$this->url:$this->url;
    }


    public function baseUrl($url = null)
    {
        if (!is_null($url) && true !== $url) {
            $this->baseUrl = $url;
            return $this;
        } elseif (!$this->baseUrl) {
            $str           = $this->url();
            $this->baseUrl = strpos($str, '?') ? strstr($str, '?', true) : $str;
        }
        return true === $url ? $this->domain() . $this->baseUrl : $this->baseUrl;
    }


    public function baseFile($file = null)
    {
        if (!is_null($file) && true !== $file) {
            $this->baseFile = $file;
            return $this;
        } elseif (!$this->baseFile) {
                $url = '';
                $script_name = basename($_SERVER['SCRIPT_FILENAME']);
                if (basename($_SERVER['SCRIPT_NAME']) === $script_name) {
                    $url = $_SERVER['SCRIPT_NAME'];
                } elseif (basename($_SERVER['PHP_SELF']) === $script_name) {
                    $url = $_SERVER['PHP_SELF'];
                } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $script_name) {
                    $url = $_SERVER['ORIG_SCRIPT_NAME'];
                } elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $script_name)) !== false) {
                    $url = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $script_name;
                } elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
                    $url = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
                }
            $this->baseFile = $url;
        }
        return true === $file ? $this->domain() . $this->baseFile : $this->baseFile;
    }


    public function root($url = null)
    {
        if (!is_null($url) && true !== $url) {
            $this->root = $url;
            return $this;
        } elseif (!$this->root) {
            $file = $this->baseFile();
            if ($file && 0 !== strpos($this->url(), $file)) {
                $file = str_replace('\\', '/', dirname($file));
            }
            $this->root = rtrim($file, '/');
        }
        return true === $url ? $this->domain() . $this->root : $this->root;
    }

    public function pathinfo()
    {
        if (is_null($this->pathinfo)) {
            $this->pathinfo = empty($_SERVER['PATH_INFO']) ? '/' : ltrim($_SERVER['PATH_INFO'], '/');
        }
        return $this->pathinfo;
    }


    public function path()
    {
        if (is_null($this->path)) {
            $suffix   = Config::get('url_html_suffix');
            $pathinfo = $this->pathinfo();

            if (false === $suffix) {
                // 禁止伪静态访问
                $this->path = $pathinfo;
            } elseif ($suffix) {
                // 去除正常的URL后缀
                $this->path = preg_replace('/\.(' . ltrim($suffix, '.') . ')$/i', '', $pathinfo);
            } else {
                // 允许任何后缀访问
                $this->path = preg_replace('/\.' . $this->ext() . '$/i', '', $pathinfo);
            }
        }
        return $this->path;
    }


    public function routeInfo($route = [])
    {
        if (!empty($route)) {
            $this->routeInfo = $route;
        } else {
            return $this->routeInfo;
        }
    }

    public function route($name = '', $default = null, $filter = '')
    {
        if (is_array($name)) {
            $this->param        = [];
            return $this->route = array_merge($this->route, $name);
        }
        return $this->input($this->route, $name, $default, $filter);
    }


    public function ext()
    {
        return pathinfo($this->pathinfo(), PATHINFO_EXTENSION);
    }


    /**
     * 当前请求的资源类型
     * @access public
     * @return false|string
     */
    public function type()
    {
        $accept = $this->server('HTTP_ACCEPT');
        if (empty($accept)) {
            return false;
        }

        foreach ($this->mimeType as $key => $val) {
            $array = explode(',', $val);
            foreach ($array as $k => $v) {
                if (stristr($accept, $v)) {
                    return $key;
                }
            }
        }
        return false;
    }
    /**
     * 设置资源类型
     * @access public
     * @param string|array  $type 资源类型名
     * @param string        $val 资源类型
     * @return void
     */
    public function mimeType($type, $val = '')
    {
        if (is_array($type)) {
            $this->mimeType = array_merge($this->mimeType, $type);
        } else {
            $this->mimeType[$type] = $val;
        }
    }

    /**
     * 当前的请求类型
     * @access public
     * @param bool $method  true 获取原始请求类型
     * @return string
     */
    public function method($method = false)
    {
        if (!$this->method) {
            if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $this->method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            } else {
                $this->method = isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
            }
        }
        return $this->method;
    }

    /**
     * 是否为GET请求
     * @access public
     * @return bool
     */
    public function isGet()
    {
        return $this->method() == 'GET';
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isPost()
    {
        return $this->method() == 'POST';
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @access public
     * @return bool
     */
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @access public
     * @return bool
     */
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    public function param($name = '', $default = null, $filter = '')
    {
        if (empty($this->param)) {
            $method = $this->method(true);
            // 自动获取请求变量
            switch ($method) {
                case 'POST':
                    $vars = $this->post(false);
                    break;
                case 'PUT':
                case 'DELETE':
                case 'PATCH':
                    $vars = $this->put(false);
                    break;
                default:
                    $vars = [];
            }
            // 当前请求参数和URL地址中的参数合并
            $this->param = array_merge($this->get(false), $vars, $this->route(false));
        }
        if (true === $name) {
            // 获取包含文件上传信息的数组
            $file = $this->file();
            $data = is_array($file) ? array_merge($this->param, $file) : $this->param;
            return $this->input($data, '', $default, $filter);
        }
        return $this->input($this->param, $name, $default, $filter);
    }


    public function get($name = '', $default = null, $filter = '')
    {
        if (empty($this->get)) {
            $this->get = $_GET;
        }
        if (is_array($name)) {
            $this->param      = [];
            return $this->get = array_merge($this->get, $name);
        }
        return $this->input($this->get, $name, $default, $filter);
    }


    public function post($name = '', $default = null, $filter = '')
    {
        if (empty($this->post)) {
            $content = $this->input;
            if (empty($_POST) && false !== strpos($this->contentType(), 'application/json')) {
                $this->post = (array) json_decode($content, true);
            } else {
                $this->post = $_POST;
            }
        }
        if (is_array($name)) {
            $this->param       = [];
            return $this->post = array_merge($this->post, $name);
        }
        return $this->input($this->post, $name, $default, $filter);
    }

    public function put($name = '', $default = null, $filter = '')
    {
        if (is_null($this->put)) {
            $content = $this->input;
            if (false !== strpos($this->contentType(), 'application/json')) {
                $this->put = (array) json_decode($content, true);
            } else {
                parse_str($content, $this->put);
            }
        }
        if (is_array($name)) {
            $this->param      = [];
            return $this->put = is_null($this->put) ? $name : array_merge($this->put, $name);
        }

        return $this->input($this->put, $name, $default, $filter);
    }

    public function session($name = '', $default = null, $filter = '')
    {
        if (empty($this->session)) {
            $this->session = Session::get();
        }
        if (is_array($name)) {
            return $this->session = array_merge($this->session, $name);
        }
        return $this->input($this->session, $name, $default, $filter);
    }

    public function cookie($name = '', $default = null, $filter = '')
    {
        if (empty($this->cookie)) {
            $this->cookie = Cookie::get();
        }

        if (is_array($name)) {
            return $this->cookie = array_merge($this->cookie, $name);
        } elseif (!empty($name)) {
            $data = Cookie::has($name) ? Cookie::get($name) : $default;
        } else {
            $data = $this->cookie;
        }

        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
            reset($data);
        } else {
            $this->filterValue($data, $name, $filter);
        }
        return $data;
    }

    public function server($name = '', $default = null, $filter = '')
    {
        if (empty($this->server)) {
            $this->server = $_SERVER;
        }
        if (is_array($name)) {
            return $this->server = array_merge($this->server, $name);
        }
        return $this->input($this->server, false === $name ? false : strtoupper($name), $default, $filter);
    }

    public function header($name = '', $default = null)
    {
        if (empty($this->header)) {
            $header = [];
            if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
                $header = $result;
            } else {
                $server = $this->server ?: $_SERVER;
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                        $header[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $header['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $header['content-length'] = $server['CONTENT_LENGTH'];
                }
            }
            $this->header = array_change_key_case($header);
        }
        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }
        if ('' === $name) {
            return $this->header;
        }
        $name = str_replace('_', '-', strtolower($name));
        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }


    public function input($data = [], $name = '', $default = null, $filter = '')
    {
        if (false === $name) {
            // 获取原始数据
            return $data;
        }
        $name = (string) $name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                list($name, $type) = explode('/', $name);
            } else {
                $type = 's';
            }
            // 按.拆分成多维数组进行判断
            foreach (explode('.', $name) as $val) {
                if (isset($data[$val])) {
                    $data = $data[$val];
                } else {
                    // 无输入数据，返回默认值
                    return $default;
                }
            }
            if (is_object($data)) {
                return $data;
            }
        }

        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
            reset($data);
        } else {
            $this->filterValue($data, $name, $filter);
        }

        if (isset($type) && $data !== $default) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }
        return $data;
    }

    public function filter($filter = null)
    {
        if (is_null($filter)) {
            return $this->filter;
        } else {
            $this->filter = $filter;
        }
    }

    protected function getFilter($filter, $default)
    {
        if (is_null($filter)) {
            $filter = [];
        } else {
            $filter = $filter ?: $this->filter;
            if (is_string($filter) && false === strpos($filter, '/')) {
                $filter = explode(',', $filter);
            } else {
                $filter = (array) $filter;
            }
        }

        $filter[] = $default;
        return $filter;
    }

    /**
     * 递归过滤给定的值
     * @param mixed     $value 键值
     * @param mixed     $key 键名
     * @param array     $filters 过滤方法+默认值
     * @return mixed
     */
    private function filterValue(&$value, $key, $filters)
    {
        $default = array_pop($filters);
        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            } elseif (is_scalar($value)) {
                if (false !== strpos($filter, '/')) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } elseif (!empty($filter)) {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        $value = $default;
                        break;
                    }
                }
            }
        }
        return $this->filterExp($value);
    }

    /**
     * 过滤表单中的表达式
     * @param string $value
     * @return void
     */
    public function filterExp(&$value)
    {
        // 过滤查询特殊字符
        if (is_string($value) && preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
            $value .= ' ';
        }
        // TODO 其他安全过滤
    }

    /**
     * 强制类型转换
     * @param string $data
     * @param string $type
     * @return mixed
     */
    private function typeCast(&$data, $type)
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array) $data;
                break;
            // 数字
            case 'd':
                $data = (int) $data;
                break;
            // 浮点
            case 'f':
                $data = (float) $data;
                break;
            // 布尔
            case 'b':
                $data = (boolean) $data;
                break;
            // 字符串
            case 's':
            default:
                if (is_scalar($data)) {
                    $data = (string) $data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
        }
    }

    public function isAjax($ajax = false)
    {
        $value  = $this->server('HTTP_X_REQUESTED_WITH', '', 'strtolower');
        $result = ('xmlhttprequest' == $value) ? true : false;
        if (true === $ajax) {
            return $result;
        } else {
            return $this->param(Config::get('var_ajax')) ? true : $result;
        }
    }

    public function ip($type = 0, $adv = false)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }


    public function isMobile()
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    public function scheme()
    {
        return $this->isSsl() ? 'https' : 'http';
    }

    public function isSsl()
    {
        $server = array_merge($_SERVER, $this->server);
        if (isset($server['HTTPS']) && ('1' == $server['HTTPS'] || 'on' == strtolower($server['HTTPS']))) {
            return true;
        } elseif (isset($server['REQUEST_SCHEME']) && 'https' == $server['REQUEST_SCHEME']) {
            return true;
        } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
            return true;
        } elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && 'https' == $server['HTTP_X_FORWARDED_PROTO']) {
            return true;
        } elseif (Config::get('https_agent_name') && isset($server[Config::get('https_agent_name')])) {
            return true;
        }
        return false;
    }

    public function query()
    {
        return $this->server('QUERY_STRING');
    }

    public function host()
    {
        return $this->server('HTTP_HOST');
    }

    public function port()
    {
        return $this->server('SERVER_PORT');
    }

    /**
     * 当前请求 SERVER_PROTOCOL
     * @access public
     * @return integer
     */
    public function protocol()
    {
        return $this->server('SERVER_PROTOCOL');
    }

    public function remotePort()
    {
        return $this->server('REMOTE_PORT');
    }

    public function contentType()
    {
        $contentType = $this->server('CONTENT_TYPE');
        if ($contentType) {
            if (strpos($contentType, ';')) {
                list($type) = explode(';', $contentType);
            } else {
                $type = $contentType;
            }
            return trim($type);
        }
        return '';
    }

    public function dispatch($dispatch = null)
    {
        if (!is_null($dispatch)) {
            $this->dispatch = $dispatch;
        }
        return $this->dispatch;
    }

    public function module($module = null)
    {
        if (!is_null($module)) {
            $this->module = $module;
            return $this;
        } else {
            return $this->module ?: '';
        }
    }

    public function controller($controller = null)
    {
        if (!is_null($controller)) {
            $this->controller = $controller;
            return $this;
        } else {
            return $this->controller ?: '';
        }
    }

    public function action($action = null)
    {
        if (!is_null($action)) {
            $this->action = $action;
            return $this;
        } else {
            return $this->action ?: '';
        }
    }

    /**
     * 设置或者获取当前请求的content
     * @access public
     * @return string
     */
    public function getContent()
    {
        if (is_null($this->content)) {
            $this->content = $this->input;
        }
        return $this->content;
    }
    /**
     * 获取当前请求的php://input
     * @access public
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * 生成请求令牌
     * @access public
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    public function token($name = '__token__', $type = 'md5')
    {
        $type  = is_callable($type) ? $type : 'md5';
        $token = call_user_func($type, $_SERVER['REQUEST_TIME_FLOAT']);
        if ($this->isAjax()) {
            header($name . ': ' . $token);
        }
        Session::set($name, $token);
        return $token;
    }


}

