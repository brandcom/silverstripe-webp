<?php

namespace jbennecker\Webp;

use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\View\HTML;
use SilverStripe\View\ViewableData;
use WebPConvert\WebPConvert;

class Picture extends ViewableData
{
    use Configurable;

    private Image $image;

    private array $formats;

    private array $widths;

    private string $sizes;

    private array $params;

    private static $casting = [
        'forTemplate' => 'HtmlText',
    ];

    private static $default_config = [
        'formats' => ['JPEG', 'WebP'],
        'sizes' => '100vw',
        'widths' => [350, 750, 1500],
    ];

    public function __construct(Image $image)
    {
        parent::__construct();

        $config = self::config();

        $this->image = $image;
        $this->formats = $config->formats ?? self::$default_config['formats'];
        $this->widths = $config->widths ?? self::$default_config['widths'];
        $this->sizes = $config->sizes ?? self::$default_config['sizes'];
        $this->params = $this->getDefaultParams();
    }

    /**
     * Currently supported:
     *  - JPEG
     *  - WebP
     *
     * You may override the default (JPEG, WebP) here, to e.g. disable WebP generation.
     */
    public function setFormats(string ...$formats): Picture
    {
        $this->formats = $formats;

        return $this;
    }

    public function setWidths(int ...$widths): Picture
    {
        sort($widths);
        $this->widths = $withs;

        return $this;
    }

    /**
     * Set the html sizes attribute. Defaults to 100vw.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement/sizes#html
     *
     * E.g. "100vw, (min-width: 640px) 50vw"
     * -> The Picture is supposed to be shown fullscreen on smaller screens. On screens largen than 640px,
     * the image is shown at 50% screen width.
     */
    public function setSizes(string $sizes): Picture
    {
        $this->sizes = $sizes;

        return $this;
    }

    /**
     * Set parameters for the <img> tag
     */
    public function setParam(string $param, string $value): Picture
    {
        $this->params[$param] = $value;

        return $this;
    }

    /**
     * Renders the html <picture> element.
     */
    public function forTemplate(): string
    {
        return $this->createPictureElement();
    }

    private function getDefaultParams(): array
    {
        return [
            'alt' => $this->image->getTitle(),
            'width' => $this->image->getWidth(),
            'height' => $this->image->getHeight(),
            'class' => 'bc-picture',
        ];
    }

    private function createPictureElement(): string
    {
        $picture_content = '';

        foreach ($this->formats as $format) {
            $picture_content .= $this->createSourceElement($format);
        }

        $picture_content .= HTML::createTag('img', $this->params);

        return HTML::createTag('picture', [], $picture_content);
    }

    private function createSourceElement(string $format): string
    {
        return '';
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
     */
    private function convertToWebp($width = 1920): ?string
    {
        $scaledImage = $this->owner->scaleMaxWidth($width);
        if (!$scaledImage) {
            return null;
        }

        $source = PUBLIC_PATH . $scaledImage->Link();
        if (!file_exists($source)) {
            return null;
        }

        $destinationLink = '/webp' . $this->owner->scaleMaxWidth($width)->Link() . '-' . $width . 'px.webp';
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
     */
    public function WebpSet(...$widths): ?string
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
     */
    public function SrcSet(...$widths): ?string
    {
        sort($widths);
        $links = [];
        foreach ($widths as $width) {
            $links[] = $this->owner->ScaleMaxWidth($width)->Url . ' ' . $width . 'w';
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
     */
    public function WebpPicture(string $params, int ...$widths): string
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
