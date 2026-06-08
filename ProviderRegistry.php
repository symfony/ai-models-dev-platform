<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Bridge\ModelsDev;

use Symfony\AI\Platform\Exception\InvalidArgumentException;

/**
 * Provides access to provider metadata from the models.dev data.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class ProviderRegistry
{
    /**
     * Well-known API base URLs for providers whose models.dev entry omits the "api" field
     * because the Vercel AI SDK hardcodes the URL inside their dedicated npm packages.
     *
     * @var array<string, string> npm package => base URL
     */
    private const NPM_PACKAGE_BASE_URLS = [
        '@ai-sdk/cerebras' => 'https://api.cerebras.ai',
        '@ai-sdk/cohere' => 'https://api.cohere.com/compatibility',
        '@ai-sdk/deepinfra' => 'https://api.deepinfra.com/v1/openai',
        '@ai-sdk/groq' => 'https://api.groq.com/openai',
        '@ai-sdk/mistral' => 'https://api.mistral.ai',
        '@ai-sdk/openai' => 'https://api.openai.com',
        '@ai-sdk/perplexity' => 'https://api.perplexity.ai',
        '@ai-sdk/togetherai' => 'https://api.together.xyz',
        '@ai-sdk/xai' => 'https://api.x.ai',
    ];

    /**
     * @var array<string, array{name: string, api: string|null, npm: string|null}>
     */
    private readonly array $providers;

    public function __construct(
        private ?string $dataPath = null,
    ) {
        $providers = [];
        foreach (DataLoader::load($dataPath) as $providerId => $providerData) {
            $providers[$providerId] = [
                'name' => $providerData['name'] ?? $providerId,
                'api' => $providerData['api'] ?? null,
                'npm' => $providerData['npm'] ?? null,
            ];
        }

        $this->providers = $providers;
    }

    /**
     * @return string|null The API base URL, or null if neither the models.dev data nor the
     *                     well-known npm-package fallback expose one
     */
    public function getApiBaseUrl(string $providerId): ?string
    {
        if (!isset($this->providers[$providerId])) {
            throw new InvalidArgumentException(\sprintf('Provider "%s" not found in registry.', $providerId));
        }

        $api = $this->providers[$providerId]['api'];
        if (null === $api && null !== ($npm = $this->providers[$providerId]['npm'])) {
            $api = self::NPM_PACKAGE_BASE_URLS[$npm] ?? null;
        }

        return null !== $api ? rtrim($api, '/') : null;
    }

    public function getProviderName(string $providerId): string
    {
        if (!isset($this->providers[$providerId])) {
            throw new InvalidArgumentException(\sprintf('Provider "%s" not found in registry.', $providerId));
        }

        return $this->providers[$providerId]['name'];
    }

    public function getCatalog(string $providerId): ?ModelCatalog
    {
        if (!isset($this->providers[$providerId])) {
            return null;
        }

        return new ModelCatalog($providerId, $this->dataPath);
    }

    public function has(string $providerId): bool
    {
        return isset($this->providers[$providerId]);
    }

    /**
     * @return list<string>
     */
    public function getProviderIds(): array
    {
        return array_keys($this->providers);
    }
}
