<?php
declare(strict_types=1);

namespace Wysiwyg\StaticPages\Eel\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Wysiwyg\StaticPages\Domain\Service\StaticPageLoaderService;
use Wysiwyg\StaticPages\Exception;

class StaticPageLoaderHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\InjectConfiguration()
     */
    protected array $config;

    /**
     * @Flow\Inject
     * @var StaticPageLoaderService
     */
    protected StaticPageLoaderService $loaderService;

    /**
     * @param string $pageKey
     * @return string
     */
    public function content(string $pageKey): string
    {
        return $this->load($pageKey, 'content');
    }

    /**
     * @param string $pageKey
     * @return string
     */
    public function bodyScripts(string $pageKey): string
    {
        return $this->load($pageKey, 'bodyScripts');
    }

    /**
     * @param string $pageKey
     * @return string
     */
    public function headScripts(string $pageKey): string
    {
        return $this->load($pageKey, 'headScripts');
    }

    /**
     * @param string $pageKey
     * @return string
     */
    public function stylesheets(string $pageKey): string
    {
        return $this->load($pageKey, 'stylesheets');
    }

    /**
     * @param string $pageKey
     * @param string $type
     * @return string
     */
    protected function load(string $pageKey, string $type): string
    {
        try {
            return $this->loaderService->load($pageKey)[$type];
        } catch (Exception $e) {
            if ($type === 'content') {
                return $e->getMessage();
            } else {
                return '';
            }
        }
    }

    /**
     * All methods are considered safe, i.e. can be executed from within Eel
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return in_array($methodName, [
            'stylesheets',
            'headScripts',
            'bodyScripts',
            'content',
        ]);
    }
}
