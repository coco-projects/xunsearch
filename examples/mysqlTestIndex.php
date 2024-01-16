<?php

    use Coco\xunsearch\dataImporter\Importer;
    use Coco\xunsearch\dataImporter\Json;
    use Coco\xunsearch\dataImporter\Mysql;
    use Coco\xunsearch\utils\XSUtils;
    use think\db\ConnectionInterface;

    require './../vendor/autoload.php';

    /**
     * -----------------------------------------------------------------------
     * -----------------------------------------------------------------------
     */

    $source = Mysql::getIns('ithinkphp_smm', [
        "hostname" => "127.0.0.1",
    ]);

    $source->initSelectLogic(function(ConnectionInterface $dbConnect) {
        return $dbConnect->table('ithinkphp_smm_services');
    });

    $source->setCoverCallback(function(array $service, Mysql $json) {

        echo $service['name'];
        echo PHP_EOL;

        $data = [
            'id'             => $service['id'],
            'name'           => $service['name'],
            'details'        => $service['details'],
            'original_price' => $service['original_price'],
            'service_type'   => $service['service_type'],
            'api_service_id' => $service['api_service_id'],
            'time'           => time(),
        ];

        $json->addIndex($data);
    });

    Importer::getIns(XSUtils::iniFileToArray('data/service.ini'), $source)->init(function(Importer $importer) {
        $importer->getXs()->getIndex()->setDb('mysql');

    })->run();
