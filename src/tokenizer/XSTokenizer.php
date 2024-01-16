<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch\tokenizer;

    use Coco\xunsearch\XSDocument;

    /**
     * 自定义字段词法分析器接口
     * 系统将按照 {@link getTokens} 返回的词汇列表对相应的字段建立索引
     *
     * @author  hightman <hightman@twomice.net>
     * @version 1.0.0
     * @package XS.tokenizer
     */
interface XSTokenizer
{
    /**
     * 内置分词器定义(常量)
     */
    const DFL = 0;

    /**
     * 执行分词并返回词列表
     *
     * @param string          $value 待分词的字段值(UTF-8编码)
     * @param XSDocument|null $doc   当前相关的索引文档
     *
     * @return array 切好的词组成的数组
     */
    public function getTokens($value, XSDocument $doc = null): array;
}
