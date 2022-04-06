<?php
declare(strict_types=1);

namespace Wysiwyg\StaticPages\DataSource;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Wysiwyg\StaticPages\Domain\Service\StaticPageLoaderService;
use Neos\Flow\Annotations as Flow;

class StaticPageDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    protected static $identifier = 'static-pages';

    /**
     * @Flow\Inject
     * @var StaticPageLoaderService
     */
    protected StaticPageLoaderService $loaderService;

    public function getData(NodeInterface $node = null, array $arguments = [])
    {
        $targetDimensions = $node->getContext()->getTargetDimensions();

        return $this->loaderService->getFilteredPageGroup($arguments['group'], $targetDimensions);
    }
}
