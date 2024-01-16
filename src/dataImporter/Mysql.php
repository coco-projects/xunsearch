<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch\dataImporter;

    use Coco\webuploader\Db;
    use think\db\BaseQuery;
    use think\db\ConnectionInterface;
    use think\DbManager;

class Mysql extends DataSource
{
    protected static ?Mysql        $ins       = null;
    protected ?ConnectionInterface $dbConnect = null;
    protected ?BaseQuery           $dbHandler = null;

    protected array $dbConfig = [
        'hostname' => '127.0.0.1',
        'password' => 'root',
        'username' => 'root',
        'charset'  => 'utf8mb4',
    ];

    protected function __construct($dbName, $dbConfig = [])
    {
        foreach ($dbConfig as $k => $v) {
            if (isset($this->dbConfig[$k])) {
                $this->dbConfig[$k] = $v;
            }
        }

        $this->dbConfig['type']     = 'mysql';
        $this->dbConfig['database'] = $dbName;

        $dbManager = new DbManager();

        $dbManager->setConfig([
            'default'     => 'xs',
            'connections' => [
                'xs' => $this->dbConfig,
            ],
        ]);

        $this->dbConnect = $dbManager->connect('xs');
    }

    public static function getIns($dbName, $config = []): ?Mysql
    {
        if (!static::$ins instanceof self) {
            static::$ins = new static($dbName, $config);
        }

        return static::$ins;
    }

    public function initSelectLogic(callable $callback): static
    {
        $this->dbHandler = call_user_func_array($callback, [
            $this->dbConnect,
        ]);

        return $this;
    }

    public function run(): void
    {
        $this->dbHandler->chunk(100, function ($data) {
            foreach ($data as $item) {
                call_user_func_array($this->coverCallback, [
                    $item,
                    $this,
                ]);
            }
        });
    }
}
