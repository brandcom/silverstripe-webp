<?php

namespace jbennecker\Webp;

use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\View\ViewableData;

class Picture extends ViewableData
{
    use Configurable;

    private Image $image;

    public function __construct(Image $image)
    {
        parent::__construct();

        $config = self::config();

        $this->image = $image;
    }

    public function getTest(): string
    {
        return "Test";
    }
}
