![Pixabay](https://raw.githubusercontent.com/patriceckhart/NeosRulez.AssetSource.Pixabay/master/pixabay-logo.png)

# Pixabay Asset Source

This package provides a Neos Asset Source to access the pixabay.com image database.

![Preview](https://raw.githubusercontent.com/patriceckhart/NeosRulez.AssetSource.Pixabay/master/Preview.png)

## Installation

The NeosRulez.AssetSource.Pixabay package is listed on packagist (https://packagist.org/packages/neosrulez/assetsource-pixabay)

Just run:

```
composer require neosrulez/assetsource-pixabay
```

## Configuration

```yaml
Neos:
  Media:
    assetSources:
      pixabay:
        assetSource: 'NeosRulez\AssetSource\Pixabay\AssetSource\PixabayAssetSource'
        assetSourceOptions:
          apiKey: 'your-api-key'
          icon: 'resource://NeosRulez.AssetSource.Pixabay/Public/Pixabay.svg'
          defaultSearchTerm: ''
```

## Notice

If you have full API access at pixabay.com, you will get Full HD scaled images with a maximum width/height of 1920 px.

With the regular API Access, the images have a maximum width/height of 1280 px.

## Author

* E-Mail: mail@patriceckhart.com
* URL: http://www.patriceckhart.com
