<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch;

    use Coco\xunsearch\exception\XSException;
    use Coco\xunsearch\tokenizer\XSTokenizer;

    /**
     * 数据字段结构元数据
     * 每个搜索项目包含若干个字段, 字段元数据保存在项目的 ini 配置文件中
     *
     * @author  hightman <hightman@twomice.net>
     * @version 1.0.0
     * @package XS
     * @see     XS::loadIniFile()
     */
class XSFieldMeta
{
    /**
     * 词条权重最大值
     */
    const MAX_WDF = 0x3f;
    /**
     * 字段类型常量定义
     */
    const TYPE_STRING  = 0;
    const TYPE_NUMERIC = 1;
    const TYPE_DATE    = 2;
    const TYPE_ID      = 10;
    const TYPE_TITLE   = 11;
    const TYPE_BODY    = 12;
    /**
     * 索引标志常量定义
     */
    const FLAG_INDEX_SELF    = 0x01;
    const FLAG_INDEX_MIXED   = 0x02;
    const FLAG_INDEX_BOTH    = 0x03;
    const FLAG_WITH_POSITION = 0x10;
    const FLAG_NON_BOOL      = 0x80; // 强制让该字段参与权重计算 (非布尔)

    /**
     * @var string 字段名称
     * 理论上支持各种可视字符, 推荐字符范围:[0-9A-Za-z-_], 长度控制在 1~32 字节为宜
     */
    public $name;

    /**
     * @var int 剪取长度 (单位:字节)
     * 用于在返回搜索结果自动剪取较长内容的字段, 默认为 0表示不截取, body 型字段默认为 300 字节
     */
    public $cutlen = 0;

    /**
     * @var int 混合区检索时的相对权重
     * 取值范围: 1~63, title 类型的字段默认为 5, 其它字段默认为 1
     */
    public $weight = 1;

    /**
     * @var int 字段类型
     */
    public $type = 0;

    /**
     * @var int 字段序号
     * 取值为 0~255, 同一字段方案内不能重复, 由 {@link XSFieldScheme::addField} 进行确定
     */
    public $vno = 0;

    /**
     * @var string 词法分析器
     */
    private string|int $tokenizer = XSTokenizer::DFL;

    /**
     * @var integer 索引标志设置
     */
    private int $flag = 0;

    /**
     * @staticvar XSTokenizer[] 分词器实例缓存
     */
    private static array $_tokenizers = [];

    /**
     * 构造函数
     *
     * @param string $name   字段名称
     * @param array  $config 可选参数, 初始化字段各项配置
     */
    public function __construct($name, $config = null)
    {
        $this->name = strval($name);
        if (is_array($config)) {
            $this->fromConfig($config);
        }
    }

    /**
     * 将对象转换为字符串
     *
     * @return string 字段名称
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * 把给定的值转换为符合这个字段的数据格式
     *
     * @param mixed $value 原值
     *
     * @return mixed 转换后的值
     */
    public function val($value)
    {
        if ($this->type == self::TYPE_DATE) {
            // 日期类型: 转换成专用的 YYYYmmdd 格式
            if (!is_numeric($value) || strlen($value) !== 8) {
                $value = date('Ymd', is_numeric($value) ? $value : strtotime($value));
            }
        }

        return $value;
    }

    /**
     * 判断当前字段索引是否支持短语搜索
     *
     * @return bool 是返回 true, 不是返回 false
     */
    public function withPos(): bool
    {
        return ($this->flag & self::FLAG_WITH_POSITION) ? true : false;
    }

    /**
     * 判断当前字段的索引是否为布尔型
     * 目前只有内置分词器支持语法型索引, 自 1.0.1 版本起把非索引字段也视为布尔便于判断
     *
     * @return bool 是返回 true, 不是返回 false
     */
    public function isBoolIndex(): bool
    {
        if ($this->flag & self::FLAG_NON_BOOL) {
            return false;
        }

        return (!$this->hasIndex() || $this->tokenizer !== XSTokenizer::DFL);
    }

    /**
     * 判断当前字段是否为数字型
     *
     * @return bool 是返回 true, 不是返回 false
     */
    public function isNumeric(): bool
    {
        return ($this->type == self::TYPE_NUMERIC);
    }

    /**
     * 判断当前字段是否为特殊类型
     * 特殊类型的字段是指 id, title, body, 每个项目至多只能有一个这种类型的字段
     *
     * @return bool 是返回 true, 不是返回 false
     */
    public function isSpeical(): bool
    {
        return ($this->type == self::TYPE_ID || $this->type == self::TYPE_TITLE || $this->type == self::TYPE_BODY);
    }

    /**
     * 判断当前字段是否需要索引
     *
     * @return bool 若需要返回 true, 不需要则返回 false
     */
    public function hasIndex(): bool
    {
        return ($this->flag & self::FLAG_INDEX_BOTH) ? true : false;
    }

    /**
     * 判断当前字段是否需要在混合区索引
     *
     * @return bool 若需要返回 true, 不需要则返回 false
     */
    public function hasIndexMixed(): bool
    {
        return ($this->flag & self::FLAG_INDEX_MIXED) ? true : false;
    }

    /**
     * 判断当前字段是否需要在字段区索引
     *
     * @return bool 若需要返回 true, 不需要则返回 false
     */
    public function hasIndexSelf(): bool
    {
        return ($this->flag & self::FLAG_INDEX_SELF) ? true : false;
    }

    /**
     * 判断当前字段是否采用自定义分词器
     *
     * @return bool 是返回 true, 不是返回 false
     */
    public function hasCustomTokenizer(): bool
    {
        return ($this->tokenizer !== XSTokenizer::DFL);
    }

    /**
     * 获取自定义词法分析器
     * 自 1.4.8 起会自动加载 lib 或当前目录下的 XSTokenizer???.class.php
     *
     * @return XSTokenizer 获取当前字段的自定义词法分析器
     * @throw XSException 如果分词器不存在或有出错抛出异常
     */
    public function getCustomTokenizer(): XSTokenizer
    {
        if (isset(self::$_tokenizers[$this->tokenizer])) {
            return self::$_tokenizers[$this->tokenizer];
        } else {
            if (($pos1 = strpos($this->tokenizer, '(')) !== false && ($pos2 = strrpos($this->tokenizer, ')', $pos1 + 1))) {
                $name = 'XSTokenizer' . ucfirst(trim(substr($this->tokenizer, 0, $pos1)));
                $arg  = substr($this->tokenizer, $pos1 + 1, $pos2 - $pos1 - 1);
            } else {
                $name = 'Coco\xunsearch\tokenizer\XSTokenizer' . ucfirst($this->tokenizer);
                $arg  = null;
            }

            if (!class_exists($name)) {
                throw new XSException('Undefined custom tokenizer `' . $this->tokenizer . '\' for field `' . $this->name . '\'');
            }

            $obj = $arg === null ? new $name : new $name($arg);
            if (!$obj instanceof XSTokenizer) {
                throw new XSException($name . ' for field `' . $this->name . '\' dose not implement the interface: XSTokenizer');
            }
            self::$_tokenizers[$this->tokenizer] = $obj;

            return $obj;
        }
    }

    /**
     * 将对象转换为配置文件字符串
     *
     * @return string 转换后的配置文件字符串
     */
    public function toConfig(): string
    {
        // type
        $str = "[" . $this->name . "]\n";
        if ($this->type === self::TYPE_NUMERIC) {
            $str .= "type = numeric\n";
        } elseif ($this->type === self::TYPE_DATE) {
            $str .= "type = date\n";
        } elseif ($this->type === self::TYPE_ID) {
            $str .= "type = id\n";
        } elseif ($this->type === self::TYPE_TITLE) {
            $str .= "type = title\n";
        } elseif ($this->type === self::TYPE_BODY) {
            $str .= "type = body\n";
        }
        // index
        if ($this->type !== self::TYPE_BODY && ($index = ($this->flag & self::FLAG_INDEX_BOTH))) {
            if ($index === self::FLAG_INDEX_BOTH) {
                if ($this->type !== self::TYPE_TITLE) {
                    $str .= "index = both\n";
                }
            } elseif ($index === self::FLAG_INDEX_MIXED) {
                $str .= "index = mixed\n";
            } else {
                if ($this->type !== self::TYPE_ID) {
                    $str .= "index = self\n";
                }
            }
        }
        // tokenizer
        if ($this->type !== self::TYPE_ID && $this->tokenizer !== XSTokenizer::DFL) {
            $str .= "tokenizer = " . $this->tokenizer . "\n";
        }
        // cutlen
        if ($this->cutlen > 0 && !($this->cutlen === 300 && $this->type === self::TYPE_BODY)) {
            $str .= "cutlen = " . $this->cutlen . "\n";
        }
        // weight
        if ($this->weight !== 1 && !($this->weight === 5 && $this->type === self::TYPE_TITLE)) {
            $str .= "weight = " . $this->weight . "\n";
        }
        // phrase
        if ($this->flag & self::FLAG_WITH_POSITION) {
            if ($this->type !== self::TYPE_BODY && $this->type !== self::TYPE_TITLE) {
                $str .= "phrase = yes\n";
            }
        } else {
            if ($this->type === self::TYPE_BODY || $this->type === self::TYPE_TITLE) {
                $str .= "phrase = no\n";
            }
        }
        // non-bool
        if ($this->flag & self::FLAG_NON_BOOL) {
            $str .= "non_bool = yes\n";
        }

        return $str;
    }

    /**
     * 解析字段对象属性
     *
     * @param array $config 原始配置属性数组
     */
    public function fromConfig($config): static
    {
        // type & default setting
        if (isset($config['type'])) {
            $predef = 'self::TYPE_' . strtoupper($config['type']);
            if (defined($predef)) {
                $this->type = constant($predef);
                if ($this->type == self::TYPE_ID) {
                    $this->flag      = self::FLAG_INDEX_SELF;
                    $this->tokenizer = 'full';
                } elseif ($this->type == self::TYPE_TITLE) {
                    $this->flag   = self::FLAG_INDEX_BOTH | self::FLAG_WITH_POSITION;
                    $this->weight = 5;
                } elseif ($this->type == self::TYPE_BODY) {
                    $this->vno    = XSFieldScheme::MIXED_VNO;
                    $this->flag   = self::FLAG_INDEX_SELF | self::FLAG_WITH_POSITION;
                    $this->cutlen = 300;
                }
            }
        }
        // index flag
        if (isset($config['index']) && $this->type != self::TYPE_BODY) {
            $predef = 'self::FLAG_INDEX_' . strtoupper($config['index']);
            if (defined($predef)) {
                $this->flag &= ~self::FLAG_INDEX_BOTH;
                $this->flag |= constant($predef);
            }
            if ($this->type == self::TYPE_ID) {
                $this->flag |= self::FLAG_INDEX_SELF;
            }
        }
        // others
        if (isset($config['cutlen'])) {
            $this->cutlen = intval($config['cutlen']);
        }
        if (isset($config['weight']) && $this->type != self::TYPE_BODY) {
            $this->weight = intval($config['weight']) & self::MAX_WDF;
        }
        if (isset($config['phrase'])) {
            if (!strcasecmp($config['phrase'], 'yes')) {
                $this->flag |= self::FLAG_WITH_POSITION;
            } elseif (!strcasecmp($config['phrase'], 'no')) {
                $this->flag &= ~self::FLAG_WITH_POSITION;
            }
        }
        if (isset($config['non_bool'])) {
            if (!strcasecmp($config['non_bool'], 'yes')) {
                $this->flag |= self::FLAG_NON_BOOL;
            } elseif (!strcasecmp($config['non_bool'], 'no')) {
                $this->flag &= ~self::FLAG_NON_BOOL;
            }
        }
        if (isset($config['tokenizer']) && $this->type != self::TYPE_ID && $config['tokenizer'] != 'default') {
            $this->tokenizer = $config['tokenizer'];
        }

        return $this;
    }
}
