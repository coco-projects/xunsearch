<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch\dataImporter;

    use Coco\xunsearch\XS;
    use Coco\xunsearch\XSDocument;

abstract class DataSource
{
    public $coverCallback = null;
    public XS|null $xs            = null;

    abstract public function run(): void;

    /**
     * @param callable $coverCallback
     *
     * @return DataSource
     */
    public function setCoverCallback(callable $coverCallback): static
    {
        $this->coverCallback = $coverCallback;

        return $this;
    }

    /**
     * @param XS|null $xs
     *
     * @return DataSource
     */
    public function setXs(?XS $xs): static
    {
        $this->xs = $xs;

        return $this;
    }

    public function addIndex($data): static
    {
        $index = $this->xs->getIndex();

        $doc = new XSDocument();
        $doc->setFields($data);
        $index->add($doc);

        return $this;
    }
}
