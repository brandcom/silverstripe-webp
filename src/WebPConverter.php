<?php

namespace jbennecker\Webp;

use SilverStripe\Control\Director;
use SilverStripe\Dev\Debug;
use WebPConvert\WebPConvert;

class WebPConverter
{
    private string $input_path;

    /**
     * Directory under /public
     */
    private ?string $folder;

    public function __construct(string $input_path, array $options=[])
    {
        if (!file_exists($input_path)) {
            throw new \Exception("File {$input_path} does not exist");
        }

        $this->input_path = $input_path;

        $this->folder = $options['folder'] ?? '/webp';
    }

    public function convert($options = [], $logger = null): bool
    {
        if (file_exists($this->getPath()) && filemtime($this->input_path) < filemtime($this->getPath())) {

            return true;
        }

        try {

            WebPConvert::convert($this->input_path, $this->getPath(), $options, $logger);

            return file_exists($this->getPath());

        } catch (\Exception $e) {

            if (!Director::isLive()) {
                Debug::dump($e->getMessage());
            }

            return false;

        }
    }

    public function getPath(): string
    {
        $public_path = str_replace(PUBLIC_PATH, '', $this->input_path);
        return PUBLIC_PATH . $this->folder . $public_path . '.webp';
    }

    public function getFile(): \SplFileInfo
    {
        return new \SplFileInfo($this->getPath());
    }

}
