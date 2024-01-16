<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch\tokenizer;

    use Coco\xunsearch\XSDocument;

    /**
     * 内置空分词器
     *
     * @author  hightman <hightman@twomice.net>
     * @version 1.0.0
     * @package XS.tokenizer
     */
class XSTokenizerNone implements XSTokenizer
{

    public function getTokens($value, XSDocument $doc = null): array
    {
        return [];
    }
}
