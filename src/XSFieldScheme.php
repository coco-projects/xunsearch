<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch;

    use Coco\xunsearch\exception\XSException;

    /**
     * XS 数据字段方案
     * 每个方案包含若干个字段结构对象 {@link XSFieldMeta}
     * 每个方案必须并且只能包含一个类型为 ID 的字段, 支持 foreach 遍历所有字段
     *
     * @author  hightman <hightman@twomice.net>
     * @version 1.0.0
     * @package XS
     */
class XSFieldScheme implements \IteratorAggregate
{
    const MIXED_VNO = 255;

    private $_fields  = [];
    private $_typeMap = [];
    private $_vnoMap  = [];
    private static $_logger;

    /**
     * 将对象转换为配置文件字符串
     */
    public function __toString()
    {
        $str = '';
        foreach ($this->_fields as $field) {
            $str .= $field->toConfig() . "\n";
        }

        return $str;
    }

    /**
     * 获取主键字段元数据
     *
     * @return bool|XSFieldMeta 类型为 ID 的字段
     */
    public function getFieldId(): bool|XSFieldMeta
    {
        if (isset($this->_typeMap[XSFieldMeta::TYPE_ID])) {
            $name = $this->_typeMap[XSFieldMeta::TYPE_ID];

            return $this->_fields[$name];
        }

        return false;
    }

    /**
     * 获取标题字段元数据
     *
     * @return bool|XSFieldMeta 类型为 TITLE 的字段
     */
    public function getFieldTitle(): bool|XSFieldMeta
    {
        if (isset($this->_typeMap[XSFieldMeta::TYPE_TITLE])) {
            $name = $this->_typeMap[XSFieldMeta::TYPE_TITLE];

            return $this->_fields[$name];
        }
        foreach ($this->_fields as $name => $field) {
            if ($field->type === XSFieldMeta::TYPE_STRING && !$field->isBoolIndex()) {
                return $field;
            }
        }

        return false;
    }

    /**
     * 获取内容字段元数据
     *
     * @return bool|XSFieldMeta 类型为 BODY 的字段
     */
    public function getFieldBody(): bool|XSFieldMeta
    {
        if (isset($this->_typeMap[XSFieldMeta::TYPE_BODY])) {
            $name = $this->_typeMap[XSFieldMeta::TYPE_BODY];

            return $this->_fields[$name];
        }

        return false;
    }

    /**
     * 获取项目字段元数据
     *
     * @param mixed $name  字段名称(string) 或字段序号(vno, int)
     * @param bool  $throw 当字段不存在时是否抛出异常, 默认为 true
     *
     * @return bool|XSFieldMeta 字段元数据对象, 若不存在则返回 false
     * @throws XSException
     * @throw XSException 当字段不存在并且参数 throw 为 true 时抛出异常
     */
    public function getField($name, $throw = true): bool|XSFieldMeta
    {
        if (is_int($name)) {
            if (!isset($this->_vnoMap[$name])) {
                if ($throw === true) {
                    throw new XSException('Not exists field with vno: `' . $name . '\'');
                }

                return false;
            }
            $name = $this->_vnoMap[$name];
        }
        if (!isset($this->_fields[$name])) {
            if ($throw === true) {
                throw new XSException('Not exists field with name: `' . $name . '\'');
            }

            return false;
        }

        return $this->_fields[$name];
    }

    /**
     * 获取项目所有字段结构设置
     *
     * @return XSFieldMeta[]
     */
    public function getAllFields(): array
    {
        return $this->_fields;
    }

    /**
     * 获取所有字段的vno与名称映映射关系
     *
     * @return array vno为键, 字段名为值的数组
     */
    public function getVnoMap(): array
    {
        return $this->_vnoMap;
    }

    /**
     * 添加字段到方案中
     * 每个方案中的特殊类型字段都不能重复出现
     *
     * @param mixed $field  若类型为 XSFieldMeta 表示要添加的字段对象,
     *                      若类型为 string 表示字段名称, 连同 $config 参数一起创建字段对象
     * @param array $config 当 $field 参数为 string 时作为新建字段的配置内容
     *
     * @throw XSException 出现逻辑错误时抛出异常
     */
    public function addField($field, $config = null): static
    {
        if (!$field instanceof XSFieldMeta) {
            $field = new XSFieldMeta($field, $config);
        }

        if (isset($this->_fields[$field->name])) {
            throw new XSException('Duplicated field name: `' . $field->name . '\'');
        }

        if ($field->isSpeical()) {
            if (isset($this->_typeMap[$field->type])) {
                $prev = $this->_typeMap[$field->type];
                throw new XSException('Duplicated ' . strtoupper($config['type']) . ' field: `' . $field->name . '\' and `' . $prev . '\'');
            }
            $this->_typeMap[$field->type] = $field->name;
        }

        $field->vno                 = ($field->type == XSFieldMeta::TYPE_BODY) ? self::MIXED_VNO : count($this->_vnoMap);
        $this->_vnoMap[$field->vno] = $field->name;

        // save field, ensure ID is the first field
        if ($field->type == XSFieldMeta::TYPE_ID) {
            $this->_fields = array_merge([$field->name => $field], $this->_fields);
        } else {
            $this->_fields[$field->name] = $field;
        }

        return $this;
    }

    /**
     * 判断该字段方案是否有效、可用
     * 每个方案必须并且只能包含一个类型为 ID 的字段
     *
     * @param bool $throw 当没有通过检测时是否抛出异常, 默认为 false
     *
     * @return bool 有效返回 true, 无效则返回 false
     * @throw XSException 当检测不通过并且参数 throw 为 true 时抛了异常
     * @throws XSException
     */
    public function checkValid($throw = false): bool
    {
        if (!isset($this->_typeMap[XSFieldMeta::TYPE_ID])) {
            if ($throw) {
                throw new XSException('Missing field of type ID');
            }

            return false;
        }

        return true;
    }

    /**
     * IteratorAggregate 接口, 以支持 foreach 遍历访问所有字段
     */
    #[ReturnTypeWillChange]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->_fields);
    }

    /**
     * 获取搜索日志的字段方案
     *
     * @return XSFieldScheme 搜索日志字段方案
     * @throws XSException
     */
    public static function logger(): XSFieldScheme
    {
        if (self::$_logger === null) {
            $scheme = new self;
            $scheme->addField('id', ['type' => 'id']);
            $scheme->addField('pinyin');
            $scheme->addField('partial');
            $scheme->addField('total', [
                'type'  => 'numeric',
                'index' => 'self',
            ]);

            $scheme->addField('lastnum', [
                'type'  => 'numeric',
                'index' => 'self',
            ]);

            $scheme->addField('currnum', [
                'type'  => 'numeric',
                'index' => 'self',
            ]);

            $scheme->addField('currtag', [
                'type' => 'string',
            ]);

            $scheme->addField('body', [
                'type' => 'body',
            ]);

            self::$_logger = $scheme;
        }

        return self::$_logger;
    }
}
