<?php

namespace jbennecker\Webp;

use WebPConvert\WebPConvert;

class WebPConverter
{
    private string $input_path;

    public function __construct(string $input_path)
    {
        if (!file_exists($input_path)) {
            throw new \Exception("File {$input_path} does not exist");
        }

        $this->input_path = $input_path;
    }

    public function convert($options = [], $logger = null): bool
    {
        try {

            WebPConvert::convert($this->input_path, $this->getPath(), $options, $logger);

            return file_exists($this->getPath());

        } catch (\Exception $e) {

            throw new \Exception("Could not convert file to WebP: {$e->getMessage()}.");
        }
    }

    public function getPath(): string
    {
        return $this->input_path . '.webp';
    }

    public function getFile(): \SplFileInfo
    {
        return new \SplFileInfo($this->getPath());
    }

}
