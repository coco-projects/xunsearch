<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch\dataImporter;

    use Exception;
    use JsonCollectionParser\Parser;

class Json extends DataSource
{
    public string|null $file = null;

    protected function __construct($file)
    {
        $this->file = $file;
    }

    public static function getIns($file): static
    {
        return new static($file);
    }

    public function run(): void
    {
        $parser = new Parser();

        try {
            $parser->parse($this->file, function (array $chunk) {

                foreach ($chunk as $item) {
                    call_user_func_array($this->coverCallback, [
                        $item,
                        $this,
                    ]);
                }
            });
        } catch (Exception $e) {
            echo $e->getMessage();
            echo PHP_EOL;
        }
    }
}
