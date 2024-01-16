<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch\dataImporter;

    use Coco\xunsearch\XS;
    use Coco\xunsearch\XSFactory;

class Importer
{
    protected XS|null         $xs         = null;
    protected DataSource|null $dataSource = null;

    public static function getIns($config, DataSource $dataSource): static
    {
        return new static($config, $dataSource);
    }

    protected function __construct($config, DataSource $dataSource)
    {
        $this->xs         = XSFactory::getIns($config);
        $this->dataSource = $dataSource;
    }

    public function init($callback): static
    {
        call_user_func_array($callback, [$this]);

        return $this;
    }

    public function run(): void
    {
        $this->dataSource->setXs($this->xs)->run();
    }

    /**
     * @return XS|null
     */
    public function getXs(): ?XS
    {
        return $this->xs;
    }
}
