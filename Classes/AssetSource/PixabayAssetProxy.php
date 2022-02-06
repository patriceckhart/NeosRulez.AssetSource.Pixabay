<?php

namespace NeosRulez\AssetSource\Pixabay\AssetSource;

use DateTime;
use DateTimeInterface;
use NeosRulez\AssetSource\Pixabay\Exception\TransferException;
use Neos\Eel\EelEvaluatorInterface;
use Neos\Eel\Exception;
use Neos\Eel\Utility;
use Neos\Flow\Annotations as Flow;
use Neos\Http\Factories\UriFactory;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\HasRemoteOriginalInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\ProvidesOriginalUriInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\SupportsIptcMetadataInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImportedAsset;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Psr\Http\Message\UriInterface;

final class PixabayAssetProxy implements AssetProxyInterface, HasRemoteOriginalInterface, SupportsIptcMetadataInterface, ProvidesOriginalUriInterface
{
    /**
     * @var array
     */
    private $file;

    /**
     * @var PixabayAssetSource
     */
    private $assetSource;

    /**
     * @var ImportedAsset
     */
    private $importedAsset;

    /**
     * @var array
     */
    private $iptcProperties;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="defaultContext", package="Neos.Fusion")
     */
    protected $defaultContextConfiguration;

    /**
     * @Flow\Inject
     * @var UriFactory
     */
    protected $uriFactory;

    /**
     * @var EelEvaluatorInterface
     * @Flow\Inject(lazy=false)
     */
    protected $eelEvaluator;

    /**
     * @param array $file
     * @param PixabayAssetSource $assetSource
     */
    public function __construct(array $file, PixabayAssetSource $assetSource)
    {
        $this->file = $file;
        $this->assetSource = $assetSource;
        $this->importedAsset = (new ImportedAssetRepository)->findOneByAssetSourceIdentifierAndRemoteAssetIdentifier($assetSource->getIdentifier(), $this->getIdentifier());
    }

    /**
     * @return PixabayAssetSource
     */
    public function getAssetSource(): PixabayAssetSource
    {
        return $this->assetSource;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return (string)$this->getProperty('id');
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return basename($this->file['previewURL']);
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return basename($this->file['previewURL']);
    }

    /**
     * @return DateTimeInterface
     * @throws \Exception
     */
    public function getLastModified(): DateTimeInterface
    {
        return new DateTime();
    }

    public function getFileSize(): int
    {
        return $this->file['imageSize'];
    }

    public function getMediaType(): string
    {
        return 'image/jpeg';
    }

    /**
     * @return int|null
     */
    public function getWidthInPixels(): ?int
    {
        return (int)$this->getProperty('imageWidth');
    }

    public function getHeightInPixels(): ?int
    {
        return (int)$this->getProperty('imageHeight');
    }

    public function getThumbnailUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->file['webformatURL']);
    }

    public function getPreviewUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->file['webformatURL']);
    }

    public function getOriginalUri(): ?UriInterface
    {
        $largeImageUri = $this->file['largeImageURL'];
        if(array_key_exists('fullHDURL', $this->file)) {
            $largeImageUri = $this->file['largeImageURL'];
        }
        return $this->uriFactory->createUri($largeImageUri);
    }

    /**
     * @return resource
     * @throws TransferException
     */
    public function getImportStream()
    {
        return $this->assetSource->getPixabayClient()->getFileStream($this->getFileUrl());
    }

    /**
     * @return null|string
     */
    public function getLocalAssetIdentifier(): ?string
    {
        return $this->importedAsset instanceof ImportedAsset ? $this->importedAsset->getLocalAssetIdentifier() : '';
    }

    /**
     * Returns true if the binary data of the asset has already been imported into the Neos asset source.
     *
     * @return bool
     */
    public function isImported(): bool
    {
        return $this->importedAsset !== null;
    }

    /**
     * Returns true, if the given IPTC metadata property is available, ie. is supported and is not empty.
     *
     * @param string $propertyName
     * @return bool
     * @throws Exception
     */
    public function hasIptcProperty(string $propertyName): bool
    {
        return isset($this->getIptcProperties()[$propertyName]);
    }

    /**
     * Returns the given IPTC metadata property if it exists, or an empty string otherwise.
     *
     * @param string $propertyName
     * @return string
     * @throws Exception
     */
    public function getIptcProperty(string $propertyName): string
    {
        return $this->getIptcProperties()[$propertyName] ?? '';
    }

    /**
     * Returns all known IPTC metadata properties as key => value (e.g. "Title" => "My File")
     *fff
     * @return array
     * @throws Exception
     */
    public function getIptcProperties(): array
    {
        if ($this->iptcProperties === null) {
            $this->iptcProperties = [
                'Title' => $this->getLabel(),
                'CopyrightNotice' => 'Pixabay.com / ' . $this->getProperty('user'),
            ];
        }

        return $this->iptcProperties;
    }

    /**
     * @param string $propertyName
     * @return mixed|null
     */
    protected function getProperty(string $propertyName)
    {
        return $this->file[$propertyName] ?? null;
    }

    /**
     *
     * @return string
     */
    protected function getFileUrl(): string
    {
        return $this->file['largeImageURL'];
    }

}
