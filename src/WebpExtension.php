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

    /**
     * Webp
     *
     * Returns the URL for a Webp image.
     *
     * If there is no Webp version yet, an existing image is created.
     *
     * @param integer $width
     * @return string|null
     *
     * @deprecated
     */
    public function Webp($width=1920): ?string
    {
        $scaledImage = $this->owner->scaleMaxWidth($width);
        if (!$scaledImage) {
            return null;
        }

        $source = PUBLIC_PATH . $scaledImage->Link();
        if (!file_exists($source)) {
            return null;
        }

        $destinationLink = '/webp' . $this->owner->scaleMaxWidth($width)->Link() . '-' . $width. 'px.webp';
        $destinationPath = PUBLIC_PATH . $destinationLink;
        $options = [];

        if (!file_exists($destinationPath) || filemtime($source) > filemtime($destinationPath)) {

            try {
                WebPConvert::convert($source, $destinationPath, $options);
            } catch (\Exception $e) {
                return null;
            }
        }
        return $destinationLink;
    }

    /**
     * WebpSet
     *
     * For use in <img> tag:
     * <img
     *  src="$BackgroundImage.ScaleWidth(1200).Link"
     *  srcset="$BackgroundImage.WebpSet(550, 750, 1200, 1500, 2200, 2700)"
     *  alt="$BackgroundImage.Title"
     *  class="background-image"
     * >
     *
     * Returns an imploded array of the image's webp-Version in the respective size with [size]w hints for the browser, e.g.
     *  '/path/to/image.jpg-550px.webp 550w, /path/to/image.jpg-800px.webp 800w, '
     *
     * @param [type] ...$widths
     * @return string|null
     *
     * @deprecated
     */
    public function WebpSet(...$widths) : ?string
    {
        sort($widths);
        $links = [];
        foreach ($widths as $width) {
            $links[] = $this->Webp($width) . ' ' . $width . 'w';
        }
        return implode(', ', $links);
    }

    /**
     * SrcSet
     *
     * As WebpSet, the method generates a srcset string, but with the original filetype.
     *
     * @param [type] ...$widths
     * @return string|null
     *
     * @deprecated
     */
    public function SrcSet(...$widths) : ?string
    {
        sort($widths);
        $links = [];
        foreach ($widths as $width) {
            $links[] = $this->owner->ScaleMaxWidth($width)->getURL() . ' ' . $width . 'w';
        }
        return implode(', ', $links);
    }

    /**
     * WebpPicture
     *
     * In Example.ss:
     * Image.WebpPicture('class="image bg-image" width="550" height="370"', 170, 550, 950, 1200).RAW
     *
     * Note: Must me used in template with the .RAW method to prevent the HTML from being escaped.
     *
     * @param string $params HTML Parameters for the img tag, except alt="". Do not use $ViewVariables in the Template as they won't be evaluated.
     * @param integer ...$widths
     * @return string
     *
     * @deprecated use $Image.Picture.setCss('my-css').setWidths(123, 456) syntax
     */
    public function WebpPicture(string $params, int ...$widths) : string
    {
        if ($this->owner->Link()) {
            return '
                <picture>
                    <source
                        type="image/webp"
                        srcset="' . $this->WebpSet(...$widths) . '"
                    >
                    <source
                        type="' . $this->owner->getMimeType() . '"
                        srcset="' . $this->SrcSet(...$widths) . '"
                    >
                    <img
                        src="' . $this->owner->ScaleMaxWidth(array_sum($widths) / count($widths))->Url . '" '
                . $params
                . ' alt="' . $this->owner->Title . '"
                    >
                </picture>';
        }

        return '';
    }
}
