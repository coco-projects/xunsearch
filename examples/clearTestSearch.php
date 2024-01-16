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

    $query = 'name:自定义';  // 这里的搜索语句很简单，就一个短语

    $index->setDb('json');
//    $index->setDb('mysql');
//    $index->setDb('hello');
//    $index->setDb('db');
    $index->clean();