<?php

namespace jbennecker\Webp;

use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\View\HTML;
use SilverStripe\View\ViewableData;

class Picture extends ViewableData
{
    use Configurable;

    private Image $image;

    private array $formats;

    /**
     * List of integers / widths for <source> elements
     */
    private array $widths;

    /**
     * <source sizes="..."> parameter content
     */
    private string $sizes;

    private array $params;

    private static $casting = [
        'forTemplate' => 'HtmlText',
    ];

    private static $default_config = [
        'formats' => ['WebP', 'JPEG'],
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

    /**
     * @param int[] $widths
     */
    public function setWidths(int ...$widths): Picture
    {
        sort($widths);
        $this->widths = $widths;

        return $this;
    }

    /**
     * Set the html sizes attribute. Defaults to 100vw.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement/sizes#html
     *
     * E.g. "(min-width: 280px) 100vw, (min-width: 640px) 50vw"
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
     * Set the alt parameter for the <img> tag
     */
    public function setAlt(string $value): Picture
    {
        $this->params['alt'] = $value;

        return $this;
    }

    /**
     * Set the class parameter for the <img> tag
     */
    public function setCss(string $value): Picture
    {
        $this->params['class'] = $value;

        return $this;
    }

    /**
     * Renders the html <picture> element.
     */
    public function forTemplate(): string
    {
        return $this->getHtml();
    }

    public function getHtml(): string
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

            $el = $this->createSourceElement($format);

            if (!$el) {
                continue;
            }

            $picture_content .= $el;
        }

        $picture_content .= $this->createImgElement();

        return HTML::createTag('picture', [], $picture_content);
    }

    private function createImgElement(): string
    {
        $params = $this->params;
        $params['src'] = $this->image->ScaleWidth(reset($this->widths))->getURL();

        return HTML::createTag('img', $params);
    }

    private function createSourceElement(string $format): string
    {
        $srcset = [];
        foreach ($this->widths as $width) {

            $scaled = $this->image->ScaleWidth($width)->getURL();

            switch (mb_strtolower($format)) {
                case 'jpeg':
                case 'jpg':
                    $path = $scaled;
                    break;
                case 'webp':
                    $converter = new WebPConverter(PUBLIC_PATH . $scaled);
                    if (!$converter->convert()) {
                        continue 2;
                    }

                    $path = str_replace(PUBLIC_PATH, '', $converter->getPath());
                    break;
            }

            if (empty($path)) {
                continue;
            }

            $srcset[] = sprintf('%s %sw', $path, $width);
        }

        if (empty($srcset)) {
            return '';
        }

        $type = mime_content_type(PUBLIC_PATH . $path);

        if (!$type) {
            return '';
        }

        return HTML::createTag('source', [
            'type' => $type,
            'srcset' => implode(', ', $srcset),
            'sizes' => $this->sizes,
        ]);
    }
}
