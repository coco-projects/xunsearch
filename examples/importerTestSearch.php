<?php

    use Coco\xunsearch\utils\XSUtils;
    use Coco\xunsearch\XSDocument;
    use Coco\xunsearch\XSFactory;
    use JsonCollectionParser\Parser;

    require './../vendor/autoload.php';

    $xs = XSFactory::getIns(XSUtils::iniFileToArray('data/service.ini'));

    $search = $xs->getSearch();
    $index  = $xs->getIndex();

    /**
     * -----------------------------------------------------------------------
     * -----------------------------------------------------------------------
     */

    $query = '保修';  // 这里的搜索语句很简单，就一个短语

    $search->setDb('json');
//    $search->setDb('mysql');

    $search->setQuery($query);                   // 设置搜索语句
    $search->setLimit(50, 0);                    // 设置返回结果最多为 5 条，并跳过前 10 条

    $docs  = $search->search(); // 执行搜索，将搜索结果文档保存在 $docs 数组中
    $count = $search->count();  // 获取搜索结果的匹配总数估算值

    foreach ($docs as $k => $v)
    {
        print_r($v->getFields());
    }

    echo PHP_EOL;
    print_r($count);
