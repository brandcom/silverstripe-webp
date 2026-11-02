<?php

namespace jbennecker\Webp;

use SilverStripe\Core\Extension;
use WebPConvert\WebPConvert;

/**
 * @property \SilverStripe\Assets\Image owner
 */
class WebpExtension extends Extension
{
    public function getPicture(): Picture
    {
        return new Picture($this->getOwner());
    }

    /**
     * Konvertiert ein Bild in das .webp-Format und gibt den Pfad zum konvertierten Bild zurück.
     *
     * @param int $width Die maximale Breite, auf die das Bild skaliert werden soll.
     * @return string|null Pfad zum .webp-Bild, geeignet für das 'src'-Attribut eines <img>-Tags.
     */
    public function Webp(int $width): ?string
    {
        // Skaliert das Bild auf die maximale Breite
        $scaledImage = $this->getOwner()->scaleMaxWidth($width);
        if (!$scaledImage) {
            return null;
        }

        // Wenn owner bereits im Webp-Format ist, wird der Link zum skalierten Webp-Bild zurückgeben.
        if ($scaledImage->getMimeType() == "image/webp") {
            return $scaledImage->Link();
        }

        $source = PUBLIC_PATH . $scaledImage->Link();
        if (!file_exists($source)) {
            return null;
        }

        $destinationLink = '/webp/' . $this->getOwner()->ID . '_' . $width . '_' . pathinfo($this->getOwner()->Link())['filename'] . '.webp';
        $destinationLink = strtolower($destinationLink);
        $destinationPath = PUBLIC_PATH . $destinationLink;
        $options = [];

        // Überprüft, ob eine neue Konvertierung notwendig ist
        if (!file_exists($destinationPath) || filemtime($source) > filemtime($destinationPath)) {
            $dir = dirname($destinationPath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            WebPConvert::convert($source, $destinationPath, $options);
        }

        return $destinationLink;
    }
}
