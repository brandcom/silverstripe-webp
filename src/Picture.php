<?php

namespace jbennecker\Webp;

use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\CustomMethods;
use SilverStripe\Dev\Debug;
use SilverStripe\View\ViewableData;

class Picture extends ViewableData
{
    use Configurable;

    private Image $image;

    private string $test;

    private static $casting = [
        'forTemplate' => 'HtmlText',
    ];

    public function __construct(Image $image)
    {
        parent::__construct();

        $config = self::config();

        $this->image = $image;
        $this->test = "123";
    }

    public function getTest(): string
    {
        return "Test";
    }

    public function setTest(string $test="default"): Picture
    {
        $this->test = $test;
        Debug::dump($this->test);
        return $this;
    }

    public function forTemplate(): string
    {
        return '<span style="color: tomato;">' . $this->test . '</span>';
    }
}
