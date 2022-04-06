<?php
declare(strict_types=1);

namespace Wysiwyg\StaticPages\Domain\Service;

use Neos\Cache\Frontend\FrontendInterface;
use Neos\Flow\Cache\CacheManager;
use Neos\Utility\Files;
use Symfony\Component\DomCrawler\Crawler;
use Neos\Flow\Annotations as Flow;
use Wysiwyg\StaticPages\Exception;

class StaticPageLoaderService
{
    /**
     * @Flow\InjectConfiguration()
     */
    protected array $config;

    protected FrontendInterface $cache;

    const PAGE_GROUP_SEPARATOR = '%';

    public function __construct(CacheManager $cacheManager)
    {
        $this->cache = $cacheManager->getCache('Wysiwyg_Static_Pages');
    }

    /**
     * @param string $fullPageKey
     * @return array
     * @throws Exception
     */
    public function load(string $fullPageKey): array
    {
        if ($this->cache->has($fullPageKey)) {
            return $this->cache->get($fullPageKey);
        }

        [$pageGroup, $pageKey] = explode(self::PAGE_GROUP_SEPARATOR, $fullPageKey, 2);

        if (!isset($this->config['pageGroups'][$pageGroup], $this->config['pageGroups'][$pageGroup][$pageKey])) {
            throw new Exception('No page with key ' . $pageKey . ' found in `Wysiwyg.StaticPages.Pages` configuration.');
        }

        $page = $this->config['pageGroups'][$pageGroup][$pageKey];
        $pageFileInfo = pathinfo($page['file']);
        $fullPageFilePath = Files::concatenatePaths([$this->config['rootFolder'], $page['file']]);

        if (!file_exists($fullPageFilePath)) {
            throw new Exception('Page file not found under the given path. (Path: ' . $fullPageFilePath . ' )');
        }

        $pageContent = [];

        if (strtolower($pageFileInfo['extension']) === 'php') {
            $pageContent = $this->loadPhpPage($fullPageFilePath);
        } else {
            $pageContent = $this->extractContent(file_get_contents($fullPageFilePath));
        }

        $this->cache->set($fullPageKey, $pageContent);

        return $pageContent;
    }

    /**
     * @param string $fullPageContent
     * @return array
     */
    protected function extractContent(string $fullPageContent): array
    {
        if (empty($this->config['contentSelector'])) {
            return $fullPageContent;
        }
        $page = ['stylesheets' => '', 'bodyScripts' => '', 'headScripts' => '', 'content' => ''];

        $crawler = new Crawler($fullPageContent);

        $crawler->filter('link[rel]')->each(function ($node, $i) use (&$page) {
            /** @var Crawler $node */
            $page['stylesheets'].= trim($node->outerHtml()) . PHP_EOL;
        });
        $crawler->filter('head script[src]')->each(function ($node, $i) use (&$page) {
            /** @var Crawler $node */
            $page['headScripts'].= trim($node->outerHtml()) . PHP_EOL;
        });
        $crawler->filter('body script[src]')->each(function ($node, $i) use (&$page) {
            /** @var Crawler $node */
            $page['bodyScripts'].= trim($node->outerHtml()) . PHP_EOL;
        });

        $page['content'] = $crawler->filter($this->config['contentSelector'])->html('');

        return $page;
    }

    /**
     * @param string $__filePath
     * @return array
     */
    protected function loadPhpPage(string $__filePath): array
    {
        // prefixed with __ for variable collision prevention
        $__filePathInfo = pathinfo($__filePath);
        $__includePath = get_include_path();
        $__pageContent = '';

        set_include_path($__filePathInfo['dirname'] . PATH_SEPARATOR . $__includePath);

        ob_start(function ($output) use (&$__pageContent) {
            $__pageContent.= $output;
        });
        include $__filePath;
        ob_end_clean();

        set_include_path($__includePath);

        return $this->extractContent($__pageContent);
    }

    /**
     * @param string $pageGroup
     * @param array $dimensions
     * @return array
     */
    public function getFilteredPageGroup(string $pageGroup, array $dimensions): array
    {
        if (!isset($this->config['pageGroups'], $this->config['pageGroups'][$pageGroup])) {
            return [];
        }

        $filteredPages = [];

        foreach ($this->config['pageGroups'][$pageGroup] as $pageKey => $page) {
            if (isset($page['dimensionConstraints'])) {
                if ($this->passesDimensionConstraints($page['dimensionConstraints'] ?? [], $dimensions)) {
                    $filteredPages[$pageKey] = $page;
                }
            } else {
                $filteredPages[$pageKey] = $page;
            }
        }

        return array_map(function ($page, $pageKey) use ($pageGroup) {
            return [
                'label' => $page['label'] ?? 'No Label',
                'value' => $pageGroup . self::PAGE_GROUP_SEPARATOR . $pageKey,
                'icon' => $page['icon'] ?? null,
            ];
        }, $filteredPages, array_keys($filteredPages));
    }

    /**
     * @param array $constraints
     * @param array $dimensions
     * @return bool
     */
    protected function passesDimensionConstraints(array $constraints, array $dimensions): bool
    {
        $passedConstraints = [];

        foreach ($dimensions as $dimensionName => $dimensionValues) {
            if (is_string($dimensionValues)) {
                $dimensionValues = [ $dimensionValues ];
            }

            if (isset($constraints[$dimensionName])) {
                foreach ($constraints[$dimensionName] as $constraintValue) {
                    if (in_array($constraintValue, $dimensionValues)) {
                        $passedConstraints[$dimensionName] = true;
                    }
                }

                if (!isset($passedConstraints[$dimensionName])) {
                    return false;
                }
            }
        }

        return true;
    }
}
