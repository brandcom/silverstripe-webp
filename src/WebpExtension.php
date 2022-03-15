<?php
namespace jbennecker\Webp;

use WebPConvert\WebPConvert;
use \SilverStripe\ORM\DataExtension;

/**
 * @property \SilverStripe\Assets\Image owner
 */
class WebpExtension extends DataExtension
{
    public function getPicture()
    {
        return Picture::create($this->owner);
    }
}
