<?php
namespace NeosRulez\AssetSource\Pixabay\AssetSource;

use GuzzleHttp\Exception\GuzzleException;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;

final class PixabayAssetProxyQuery implements AssetProxyQueryInterface
{

    /**
     * @var PixabayAssetSource
     */
    private $assetSource;

    /**
     * @var int
     */
    private $limit = 80;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var string
     */
    private $searchTerm = '';

    /**
     * UnsplashAssetProxyQuery constructor.
     * @param PixabayAssetSource $assetSource
     */
    public function __construct(PixabayAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }

    /**
     * @param string $searchTerm
     */
    public function setSearchTerm(string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * @return AssetProxyQueryResultInterface
     * @throws AssetSourceConnectionExceptionInterface
     * @throws \Exception
     * @throws GuzzleException
     */
    public function execute(): AssetProxyQueryResultInterface
    {
        $page = (int)ceil(($this->offset + 1) / $this->limit);

        $searchTerm = $this->searchTerm ?: $this->assetSource->getDefaultSearchTerm();

        if ($searchTerm === '') {
            $files = $this->assetSource->getPixabayClient()->curated($this->limit, $page);
        } else {
            $files = $this->assetSource->getPixabayClient()->search($searchTerm, $this->limit, $page);
        }

        return new PixabayAssetProxyQueryResult($this, $files, $this->assetSource);
    }

    /**
     * @return int
     * @throws \Exception
     * @throws GuzzleException
     */
    public function count(): int
    {
        return $this->execute()->count();
    }
}
