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

use Symfony\AI\Platform\Bridge\Anthropic\Claude;
use Symfony\AI\Platform\Bridge\Gemini\Embeddings;
use Symfony\AI\Platform\Bridge\Gemini\Gemini;
use Symfony\AI\Platform\Bridge\Generic\CompletionsModel;
use Symfony\AI\Platform\Bridge\Generic\EmbeddingsModel;
use Symfony\AI\Platform\Bridge\VertexAi\Embeddings\Model as VertexAiEmbeddings;
use Symfony\AI\Platform\Bridge\VertexAi\Gemini\Model as VertexAiGemini;
use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\Exception\InvalidArgumentException;
use Symfony\AI\Platform\ModelCatalog\AbstractModelCatalog;

/**
 * Model catalog powered by models.dev data.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class ModelCatalog extends AbstractModelCatalog
{
    /**
     * Maps a provider's models.dev npm package to the model classes its specialized bridge
     * requires. Providers without an entry use the generic model classes.
     *
     * @var array<string, array{completions?: class-string, embeddings?: class-string}>
     */
    private const MODEL_CLASS_OVERRIDES = [
        '@ai-sdk/anthropic' => [
            'completions' => Claude::class,
        ],
        '@ai-sdk/google' => [
            'completions' => Gemini::class,
            'embeddings' => Embeddings::class,
        ],
        '@ai-sdk/google-vertex' => [
            'completions' => VertexAiGemini::class,
            'embeddings' => VertexAiEmbeddings::class,
        ],
    ];

    /**
     * @var array<string, string> npm package => human-readable provider name
     */
    private const UNSUPPORTED_NPM_PACKAGES = [
        '@ai-sdk/amazon-bedrock' => 'Amazon Bedrock',
    ];

    /**
     * @param string                                                                    $providerId            The models.dev provider ID (e.g. "openai", "groq", "deepseek")
     * @param string|null                                                               $dataPath              Path to the models.dev JSON file (defaults to the bundled file)
     * @param array<string, array{class: class-string, capabilities: list<Capability>}> $additionalModels      Additional models to merge into the catalog
     * @param class-string|null                                                         $completionsModelClass Override the completions model class (defaults to the bridge-specific or generic class)
     * @param class-string|null                                                         $embeddingsModelClass  Override the embeddings model class (defaults to the bridge-specific or generic class)
     */
    public function __construct(
        string $providerId,
        ?string $dataPath = null,
        array $additionalModels = [],
        ?string $completionsModelClass = null,
        ?string $embeddingsModelClass = null,
    ) {
        $data = DataLoader::load($dataPath);

        if (!isset($data[$providerId])) {
            throw new InvalidArgumentException(\sprintf('Provider "%s" not found in models.dev data.', $providerId));
        }

        $npm = $data[$providerId]['npm'] ?? '';
        if (isset(self::UNSUPPORTED_NPM_PACKAGES[$npm])) {
            throw new InvalidArgumentException(\sprintf('Provider "%s" (%s) is not supported by the models.dev bridge because it cannot be driven by the generic client.', $providerId, self::UNSUPPORTED_NPM_PACKAGES[$npm]));
        }

        $override = self::MODEL_CLASS_OVERRIDES[$npm] ?? [];
        $completionsModelClass ??= $override['completions'] ?? CompletionsModel::class;
        $embeddingsModelClass ??= $override['embeddings'] ?? EmbeddingsModel::class;

        $models = [];
        foreach ($data[$providerId]['models'] as $modelData) {
            if ('deprecated' === ($modelData['status'] ?? 'active')) {
                continue;
            }

            $models[$modelData['id']] = [
                'class' => CapabilityMapper::isEmbeddingModel($modelData) ? $embeddingsModelClass : $completionsModelClass,
                'capabilities' => CapabilityMapper::map($modelData),
            ];
        }

        $this->models = array_merge($models, $additionalModels);
    }
}
