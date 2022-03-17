# silverstripe-webp

The Plugin provides a helper to create html `<picture>` elements in Silverstripe templates. It supports WebP sources out of the box.

## Requirements

-   SilverStripe 4.x
-   rosell-dk/webp-convert ^2.6
-   php >= 7.4

## Install

Install via composer.

`composer require jbennecker/silverstripe-webp`

Register the plugin as a data extension for the `Assets\Image` class:

```yaml
SilverStripe\Assets\Image:
    extensions:
        - jbennecker\Webp\WebpExtension
```

## Usage

The `jbennecker\Webp\Picture` class provides a flexible Api to manipulate your picture in your template file.

To access the Api, call the `getPicture()` method from the `WebpExtension` like so:

```
$MyImage.Picture
```

This will be enough to output a `<picture>` with standard configuration.

### Api methods

The class provides multiple methods that can be called in any order. You can chain the methods, as they return the instance of the Picture class.

#### setWidths(int ...$widths)

The method will set the widths in the `srcset` attribute in each of the picture's `<source>` tags.

Example:

```
$MyImage.Picture.setWidths(150, 230, 550)
```

Defaults to `350, 750, 1500`.

#### setSizes(string $sizes)

Set the `sizes` attribute on the `<source>` tags a media-query. Defaults to `100w`.

```
$MyImage.Picture.setWidths(370, 750, 1920).setSizes("(min-width: 280px) 100vw, (min-width: 640px) 50vw")
```

#### setFormats(string ...$formats)

Control what `<source>` tags / formats will be present. Defaults to webp and jpeg.

Available options:
* webp
* jpg/jpeg

To e.g. disable webp and only get one `<source>` with a jpg `srcset`:

```
$MyImage.Picture.setFormats('jpg')
```

#### setAlt(string $value)

Sets the `alt` parameter on the `<img>` tag. Defaults to the Image's title from the CMS.

#### setCss(string $value)

Sets the `class` parameter on the `<img>` tag.

#### setWidth(int $width)

Set the `width` attribute on the `<img>` tag. Defaults to the Image's original width.

#### setHeight(int $height)

Set the `height` attribute on the `<img>` tag. Defaults to the Image's original height.

#### setParam(string $param, string $value)

Sets a parameter with the name `$param` on the `<img>` tag.

```
$MyImage.Picture.setClass("w-full border shadow-lg").setParam("title", "This is a title")
```

#### setLazyLoading($lazy = true)

Control the `loading` attribute. Sets it to `lazy` or `eager`. Defaults to lazy loading.

## FAQ

### Images are missing after running `composer upgrade`

Version `1.0` is using a different API and the old methods have been removed.

You should downgrade to version `0.1`:

```
"jbennecker/silverstripe-webp": "^0.1"
```

