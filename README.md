# silverstripe-webp

The Plugin integrates simple WebP image conversion into Silverstripe.

## Requirements

-   SilverStripe 4.x

## Install

Install via composer.

`composer require jbennecker/silverstripe-webp`

Register the plugin as a data extension for the `Image` asset:

```yaml
SilverStripe\Assets\Image:
    extensions:
        - jbennecker\Webp\WebpExtension
```

## Usage

Convert images on the go in a Template, e.g. `Example.ss`.

### Create a responsive `<picture>` element

You can create picture elements with responsive Webp and fallback sources:

`$Image.WebpPicture(string $params, int ...$widths)`

e.g.

`$Image.WebpPicture('class="example" width="100" height="50"', 370, 550, 1200).RAW`

Don't forget to append `.RAW` in order to get unescaped html.

This will create the following html output (for .jpg input):

```html
<picture>
    <source
        type="image/webp"
        srcset="
            /webp/.../path/image.jpg-370px.webp   370w,
            /webp/.../path/image.jpg-550px.webp   550w,
            /webp/.../path/image.jpg-1200px.webp 1200w
        "
    />
    <source
        type="image/jpeg"
        srcset="
            /.../path/XYZimage__ScaleMaxWidth....jpg  370w,
            /.../path/XZXimage__ScaleMaxWidth....jpg  550w,
            /.../path/ABCimage__ScaleMaxWidth....jpg 1200w
        "
    />
    <img src="/.../path/ABXimage__ScaleMaxWidth....jpg" class="example" width="100" height="50" alt="[$Image.Title]" />
</picture>
```

The fallback `<img>` will have the average width of your defined `...$widths`.

_Note:_ The `alt` parameter is set automatically as no variables can be added to the `$params` string.

### Get `srcset` string or a single WebP-Link

You can get `srcset` sources with widths by using the `$Image.WebpSet(120, 550, 700)` method and insert the string in a `srcset` parameter of an `<img>` tag.

Additionally, you can call `$Image.Webp` to get a single url to the converted Webp file.

You will find additional help in the `WebpExtension.php` file.
