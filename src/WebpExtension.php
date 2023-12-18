<?php
namespace jbennecker\Webp;

use \SilverStripe\ORM\DataExtension;
use WebPConvert\WebPConvert;

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
     * Konvertiert ein Bild in das .webp-Format und gibt den Pfad zum konvertierten Bild zurück.
     *
     * @param int $width Die maximale Breite, auf die das Bild skaliert werden soll.
     * @return string|null Pfad zum .webp-Bild, geeignet für das 'src'-Attribut eines <img>-Tags.
     */
    public function Webp($width): ?string
    {
        // Skaliert das Bild auf die maximale Breite
        $scaledImage = $this->owner->scaleMaxWidth($width);
        if (!$scaledImage) {
            return null;
        }

        // TODO
        // Wenn der owner schon im Webp-Format ist,
        // dann hier den Link zum skalierten Webp-Bild zurück geben.

        $source = PUBLIC_PATH . $scaledImage->Link();
        if (!file_exists($source)) {
            return null;
        }

        $destinationLink = '/webp/' . $this->owner->ID . '_' . $width . '_' . pathinfo($this->owner->Link())['filename'] . '.webp';
        $destinationLink = strtolower($destinationLink);
        $destinationPath = PUBLIC_PATH . $destinationLink;
        $options = [];

        // Überprüft, ob eine neue Konvertierung notwendig ist
        if (!file_exists($destinationPath) || filemtime($source) > filemtime($destinationPath)) {
            WebPConvert::convert($source, $destinationPath, $options);
        }

        return $destinationLink;
    }
}
