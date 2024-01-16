<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch;

    use Coco\xunsearch\components\XSComponent;
    use Coco\xunsearch\components\XSServer;
    use Coco\xunsearch\exception\XSException;

    /**
     * XS 搜索项目主类
     *
     * @property XSFieldScheme    $scheme         当前在用的字段方案
     * @property string           $defaultCharset 默认字符集编码
     * @property-read string      $name           项目名称
     * @property-read XSIndex     $index          索引操作对象
     * @property-read XSSearch    $search         搜索操作对象
     * @property-read XSFieldMeta $idField        主键字段
     * @author  hightman <hightman@twomice.net>
     * @version 1.0.0
     * @package XS
     */
class XS extends XSComponent
{
    /**
     * @var ?XSIndex 索引操作对象
     */
    private ?XSIndex $_index = null;

    /**
     * @var ?XSSearch 搜索操作对象
     */
    private ?XSSearch $_search = null;

    /**
     * @var ?XSServer scws 分词服务器
     */
    private ?XSServer $_scws = null;

    /**
     * @var ?XSFieldScheme 当前字段方案
     */
    private ?XSFieldScheme $_scheme = null, $_bindScheme = null;

    private array $_config = [];

    /**
     * @var ?XS 最近创建的 XS 对象
     */
    private static ?XS $_lastXS = null;

    /**
     * 构造函数
     *
     *
     * @param array $arr 配置数组
     *
     * @throws XSException
     */
    protected function __construct(array $arr)
    {
        $this->initConfig($arr);

        self::$_lastXS = $this;
    }

    /**
     * 析构函数
     * 由于对象交叉引用, 如需提前销毁对象, 请强制调用该函数
     */
    public function __destruct()
    {
        $this->_index  = null;
        $this->_search = null;
    }

    /**
     * 获取最新的 XS 实例
     *
     * @return XS|null 最近创建的 XS 对象
     */
    public static function getLastXS(): ?XS
    {
        return self::$_lastXS;
    }

    /**
     * 获取当前在用的字段方案
     * 通用于搜索结果文档和修改、添加的索引文档
     *
     * @return XSFieldScheme 当前字段方案
     */
    public function getScheme(): XSFieldScheme
    {
        return $this->_scheme;
    }

    /**
     * 设置当前在用的字段方案
     *
     * @param XSFieldScheme $fs 一个有效的字段方案对象
     *
     * @throw XSException 无效方案则直接抛出异常
     * @throws XSException
     */
    public function setScheme(XSFieldScheme $fs): static
    {
        $fs->checkValid(true);

        $this->_scheme = $fs;

        if ($this->_search !== null) {
            $this->_search->markResetScheme();
        }

        return $this;
    }

    /**
     * 还原字段方案为项目绑定方案
     */
    public function restoreScheme(): static
    {
        if ($this->_scheme !== $this->_bindScheme) {
            $this->_scheme = $this->_bindScheme;
            if ($this->_search !== null) {
                $this->_search->markResetScheme(true);
            }
        }

        return $this;
    }

    /**
     * @return array 获取配置原始数据
     */
    public function getConfig(): array
    {
        return $this->_config;
    }

    /**
     * 获取当前项目名称
     *
     * @return string 当前项目名称
     */
    public function getName(): string
    {
        return $this->_config['project.name'];
    }

    /**
     * 修改当前项目名称
     * 注意，必须在 {@link getSearch} 和 {@link getIndex} 前调用才能起作用
     *
     * @param string $name 项目名称
     */
    public function setName(string $name): static
    {
        $this->_config['project.name'] = $name;

        return $this;
    }

    /**
     * 获取项目的默认字符集
     *
     * @return string 默认字符集(已大写)
     */
    public function getDefaultCharset(): string
    {
        return isset($this->_config['project.default_charset']) ? strtoupper($this->_config['project.default_charset']) : 'UTF-8';
    }

    /**
     * 改变项目的默认字符集
     *
     * @param string $charset 修改后的字符集
     */
    public function setDefaultCharset(string $charset): static
    {
        $this->_config['project.default_charset'] = strtoupper($charset);

        return $this;
    }

    /**
     * 获取索引操作对象
     *
     * @return XSIndex 索引操作对象
     */
    public function getIndex(): XSIndex
    {
        if ($this->_index === null) {
            $adds = [];

            $conn = $this->_config['server.index'] ?? 8383;

            if (($pos = strpos($conn, ';')) !== false) {
                $adds = explode(';', substr($conn, $pos + 1));
                $conn = substr($conn, 0, $pos);
            }

            $this->_index = new XSIndex($conn, $this);
            $this->_index->setTimeout(0);
            foreach ($adds as $conn) {
                $conn = trim($conn);
                if ($conn !== '') {
                    $this->_index->addServer($conn)->setTimeout(0);
                }
            }
        }

        return $this->_index;
    }

    /**
     * 获取搜索操作对象
     *
     * @return XSSearch 搜索操作对象
     * @throws XSException
     */
    public function getSearch(): XSSearch
    {
        if ($this->_search === null) {
            $conns = [];

            if (!isset($this->_config['server.search'])) {
                $conns[] = 8384;
            } else {
                foreach (explode(';', $this->_config['server.search']) as $conn) {
                    $conn = trim($conn);
                    if ($conn !== '') {
                        $conns[] = $conn;
                    }
                }
            }
            if (count($conns) > 1) {
                shuffle($conns);
            }

            for ($i = 0; $i < count($conns); $i++) {
                try {
                    $this->_search = new XSSearch($conns[$i], $this);
                    $this->_search->setCharset($this->getDefaultCharset());

                    return $this->_search;
                } catch (XSException $e) {
                    if (($i + 1) === count($conns)) {
                        throw $e;
                    }
                }
            }
        }

        return $this->_search;
    }

    /**
     * 创建 scws 分词连接
     *
     * @return XSServer 分词服务器
     */
    public function getScwsServer(): XSServer
    {
        if ($this->_scws === null) {
            $conn = $this->_config['server.search'] ?? 8384;

            $this->_scws = new XSServer($conn, $this);
        }

        return $this->_scws;
    }

    /**
     * 获取当前主键字段
     *
     * @return XSFieldMeta 类型为 ID 的字段
     * @see XSFieldScheme::getFieldId
     */
    public function getFieldId(): XSFieldMeta
    {
        return $this->_scheme->getFieldId();
    }

    /**
     * 获取当前标题字段
     *
     * @return XSFieldMeta 类型为 TITLE 的字段
     * @see XSFieldScheme::getFieldTitle
     */
    public function getFieldTitle(): XSFieldMeta
    {
        return $this->_scheme->getFieldTitle();
    }

    /**
     * 获取当前内容字段
     *
     * @return XSFieldMeta 类型为 BODY 的字段
     * @see XSFieldScheme::getFieldBody
     */
    public function getFieldBody(): XSFieldMeta
    {
        return $this->_scheme->getFieldBody();
    }

    /**
     * 获取项目字段元数据
     *
     * @param mixed $name  字段名称(string) 或字段序号(vno, int)
     * @param bool  $throw 当字段不存在时是否抛出异常, 默认为 true
     *
     * @return XSFieldMeta 字段元数据对象
     * @throw XSException 当字段不存在并且参数 throw 为 true 时抛出异常
     * @throws XSException
     * @see   XSFieldScheme::getField
     */
    public function getField(mixed $name, bool $throw = true): XSFieldMeta
    {
        return $this->_scheme->getField($name, $throw);
    }

    /**
     * 获取项目所有字段结构设置
     *
     * @return XSFieldMeta[]
     */
    public function getAllFields(): array
    {
        return $this->_scheme->getAllFields();
    }

    /**
     * 字符集转换
     * 要求安装有 mbstring, iconv 中的一种
     *
     * @param mixed  $data 需要转换的数据, 支持 string 和 array, 数组会自动递归转换
     * @param string $to   转换后的字符集
     * @param string $from 转换前的字符集
     *
     * @return mixed 转换后的数据
     * @throw XSEXception 如果没有合适的转换函数抛出异常
     * @throws XSException
     */
    public static function convert($data, $to, $from): mixed
    {

        // need not convert
        if ($to == $from) {
            return $data;
        }

        // array traverse
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::convert($value, $to, $from);
            }

            return $data;
        }

        // string contain 8bit characters
        if (is_string($data) && preg_match('/[\x81-\xfe]/', $data)) {
            // mbstring, iconv, throw ...
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($data, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to . '//TRANSLIT', $data);
            } else {
                throw new XSException('Cann\'t find the mbstring or iconv extension to convert encoding');
            }
        }

        return $data;
    }

    /**
     * 计算经纬度距离
     *
     * @param float $lon1 原点经度
     * @param float $lat1 原点纬度
     * @param float $lon2 目标点经度
     * @param float $lat2 目标点纬度
     *
     * @return float 两点大致距离，单位：米
     */
    public static function geoDistance($lon1, $lat1, $lon2, $lat2): float
    {
        $dx = $lon1 - $lon2;
        $dy = $lat1 - $lat2;
        $b  = ($lat1 + $lat2) / 2;
        $lx = 6367000.0 * deg2rad($dx) * cos(deg2rad($b));
        $ly = 6367000.0 * deg2rad($dy);

        return sqrt($lx * $lx + $ly * $ly);
    }


    /**
     * 加载项目配置文件
     *
     * @param array $arr 配置数组
     *
     * @throw XSException 出错时抛出异常
     * @throws XSException
     * @see   XSFieldMeta::fromConfig
     */
    private function initConfig(array $arr): void
    {
        $this->_config = $arr;

        $scheme = new XSFieldScheme();
        foreach ($this->_config as $key => $value) {
            if (is_array($value)) {
                $scheme->addField($key, $value);
            }
        }

        $scheme->checkValid(true);

        $this->_scheme = $this->_bindScheme = $scheme;
    }
}
