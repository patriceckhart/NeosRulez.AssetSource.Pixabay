<?php
namespace NeosRulez\AssetSource\Pixabay\AssetSource;

use Neos\Flow\Annotations as Flow;
use NeosRulez\AssetSource\Pixabay\Api\PixabayClient;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;

final class PixabayAssetSource implements AssetSourceInterface
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var string
     */
    private $assetSourceIdentifier;

    /**
     * @var PixabayAssetProxyRepository
     */
    private $assetProxyRepository;

    /**
     * @var PixabayClient
     */
    protected $pixabayClient;

    /**
     * @var string
     */
    private $defaultSearchTerm;

    /**
     * @var string
     */
    private $iconPath;


    /**
     * GoogleCloudStorageAssetSource constructor.
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     */
    public function __construct(string $assetSourceIdentifier, array $assetSourceOptions)
    {
        $this->assetSourceIdentifier = $assetSourceIdentifier;
        $this->pixabayClient = new PixabayClient(
            $assetSourceOptions['apiKey']
        );
        $this->defaultSearchTerm = trim($assetSourceOptions['defaultSearchTerm']) ?? '';
        $this->iconPath = trim($assetSourceOptions['icon']) ?? '';
    }

    /**
     * This factory method is used instead of a constructor in order to not dictate a __construct() signature in this
     * interface (which might conflict with an asset source's implementation or generated Flow proxy class).
     *
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     * @return AssetSourceInterface
     */
    public static function createFromConfiguration(string $assetSourceIdentifier, array $assetSourceOptions): AssetSourceInterface
    {
        return new static($assetSourceIdentifier, $assetSourceOptions);
    }

    /**
     * A unique string which identifies the concrete asset source.
     * Must match /^[a-z][a-z0-9-]{0,62}[a-z]$/
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->assetSourceIdentifier;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return 'Pixabay';
    }

    /**
     * @return AssetProxyRepositoryInterface
     */
    public function getAssetProxyRepository(): AssetProxyRepositoryInterface
    {
        if ($this->assetProxyRepository === null) {
            $this->assetProxyRepository = new PixabayAssetProxyRepository($this);
        }

        return $this->assetProxyRepository;
    }

    /**
     * @return PixabayClient
     */
    public function getPixabayClient(): PixabayClient
    {
        return $this->pixabayClient;
    }

    /**
     * @return string
     */
    public function getCopyRightNoticeTemplate(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDefaultSearchTerm(): string
    {
        return $this->defaultSearchTerm;
    }

    /**
     * Returns the resource path to Assetsources icon
     *
     * @return string
     */
    public function getIconUri(): string
    {
        return $this->resourceManager->getPublicPackageResourceUriByPath($this->iconPath);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Files provided from pixabay.com';
    }
}
