<?php

    use Coco\xunsearch\dataImporter\Importer;
    use Coco\xunsearch\dataImporter\Json;
    use Coco\xunsearch\utils\XSUtils;

    require './../vendor/autoload.php';

    /**
     * -----------------------------------------------------------------------
     * -----------------------------------------------------------------------
     */

    $jsonFile = 'data/service.json';
    $source   = Json::getIns($jsonFile);

    $source->setCoverCallback(function(array $services, Json $json) {

        if (isset($services['services']))
        {
            foreach ($services['services'] as $k => $service)
            {
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
            }
        }

    });

    Importer::getIns(XSUtils::iniFileToArray('data/service.ini'), $source)->init(function(Importer $importer) {
        $importer->getXs()->getIndex()->setDb('json');

    })->run();
