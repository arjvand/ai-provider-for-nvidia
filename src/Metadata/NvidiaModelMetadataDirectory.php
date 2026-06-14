<?php

declare(strict_types=1);

namespace Arjvand\NvidiaAiProvider\Metadata;

use Arjvand\NvidiaAiProvider\Provider\NvidiaProvider;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleModelMetadataDirectory;

/**
 * Class for the NVIDIA model metadata directory.
 *
 * @since 1.0.0
 *
 * @phpstan-type ModelsResponseData array{
 *     data: list<array{id: string, owned_by?: string}>
 * }
 */
class NvidiaModelMetadataDirectory extends AbstractOpenAiCompatibleModelMetadataDirectory
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected function createRequest(HttpMethodEnum $method, string $path, array $headers = [], $data = null): Request
    {
        return new Request(
            $method,
            NvidiaProvider::url($path),
            $headers,
            $data
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected function parseResponseToModelMetadataList(Response $response): array
    {
        /** @var ModelsResponseData $responseData */
        $responseData = $response->getData();
        if (!isset($responseData['data']) || !$responseData['data']) {
            throw ResponseException::fromMissingData('NVIDIA', 'data');
        }

        $allModalityCombinationsWithText = [
            [ModalityEnum::text()],
            [ModalityEnum::text(), ModalityEnum::image()],
            [ModalityEnum::text(), ModalityEnum::document()],
        ];

        // Text generation capabilities and options.
        $textCapabilities = [
            CapabilityEnum::textGeneration(),
            CapabilityEnum::chatHistory(),
        ];
        $textBaseOptions = [
            new SupportedOption(OptionEnum::systemInstruction()),
            new SupportedOption(OptionEnum::maxTokens()),
            new SupportedOption(OptionEnum::temperature()),
            new SupportedOption(OptionEnum::topP()),
            new SupportedOption(OptionEnum::stopSequences()),
            new SupportedOption(OptionEnum::functionDeclarations()),
            new SupportedOption(OptionEnum::customOptions()),
        ];
        $textOptions = array_merge($textBaseOptions, [
            new SupportedOption(OptionEnum::inputModalities(), [[ModalityEnum::text()]]),
            new SupportedOption(OptionEnum::outputModalities(), [[ModalityEnum::text()]]),
        ]);
        $multimodalInputOptions = array_merge($textBaseOptions, [
            new SupportedOption(
                OptionEnum::inputModalities(),
                $allModalityCombinationsWithText
            ),
            new SupportedOption(OptionEnum::outputModalities(), [[ModalityEnum::text()]]),
        ]);

        // Image generation capabilities and options.
        $imageCapabilities = [
            CapabilityEnum::imageGeneration(),
        ];
        $imageOptions = [
            new SupportedOption(OptionEnum::inputModalities(), [[ModalityEnum::text()]]),
            new SupportedOption(OptionEnum::outputModalities(), [[ModalityEnum::image()]]),
            new SupportedOption(OptionEnum::candidateCount()),
            new SupportedOption(OptionEnum::outputMimeType(), ['image/png', 'image/jpeg']),
            new SupportedOption(OptionEnum::outputMediaOrientation(), [
                \WordPress\AiClient\Files\Enums\MediaOrientationEnum::square(),
                \WordPress\AiClient\Files\Enums\MediaOrientationEnum::landscape(),
                \WordPress\AiClient\Files\Enums\MediaOrientationEnum::portrait(),
            ]),
            new SupportedOption(OptionEnum::outputMediaAspectRatio(), ['1:1', '3:2', '2:3']),
            new SupportedOption(OptionEnum::customOptions()),
        ];

        $modelsData = (array) $responseData['data'];

        $models = array_values(
            array_map(
                static function (array $modelData) use (
                    $textCapabilities,
                    $textOptions,
                    $multimodalInputOptions,
                    $imageCapabilities,
                    $imageOptions
                ): ModelMetadata {
                    $modelId = $modelData['id'];
                    $ownedBy = $modelData['owned_by'] ?? '';

                    // Determine model capabilities based on ID patterns.
                    if (self::isImageGenerationModel($modelId)) {
                        $modelCaps = $imageCapabilities;
                        $modelOptions = $imageOptions;
                    } elseif (self::isTextGenerationModel($modelId)) {
                        $modelCaps = $textCapabilities;
                        if (self::supportsMultimodalInput($modelId)) {
                            $modelOptions = $multimodalInputOptions;
                        } else {
                            $modelOptions = $textOptions;
                        }
                    } else {
                        // Default to text generation for unknown models.
                        $modelCaps = $textCapabilities;
                        $modelOptions = $textOptions;
                    }

                    return new ModelMetadata(
                        $modelId,
                        $modelId,
                        $modelCaps,
                        $modelOptions
                    );
                },
                $modelsData
            )
        );

        usort($models, [$this, 'modelSortCallback']);

        return $models;
    }

    /**
     * Checks if a model is an image generation model.
     *
     * @since 1.0.0
     *
     * @param string $modelId The model ID.
     * @return bool True if the model is an image generation model.
     */
    private static function isImageGenerationModel(string $modelId): bool
    {
        return str_contains($modelId, 'stable-diffusion')
            || str_contains($modelId, 'flux')
            || str_contains($modelId, 'sdxl');
    }

    /**
     * Checks if a model is a text generation model.
     *
     * @since 1.0.0
     *
     * @param string $modelId The model ID.
     * @return bool True if the model is a text generation model.
     */
    private static function isTextGenerationModel(string $modelId): bool
    {
        // Most models on NVIDIA NIM are text generation models.
        // Exclude known non-text models.
        return !self::isImageGenerationModel($modelId)
            && !str_contains($modelId, 'embed')
            && !str_contains($modelId, 'rerank')
            && !str_contains($modelId, 'safety')
            && !str_contains($modelId, 'detect')
            && !str_contains($modelId, 'parse')
            && !str_contains($modelId, 'pii');
    }

    /**
     * Checks if a text generation model supports multimodal input.
     *
     * @since 1.0.0
     *
     * @param string $modelId The model ID.
     * @return bool True if the model supports multimodal text input.
     */
    private static function supportsMultimodalInput(string $modelId): bool
    {
        return str_contains($modelId, 'vision')
            || str_contains($modelId, '-vl')
            || str_contains($modelId, 'multimodal')
            || str_contains($modelId, 'vila')
            || str_contains($modelId, 'neva');
    }

    /**
     * Callback function for sorting models by ID, to be used with `usort()`.
     *
     * This method expresses preferences for certain models or model families within the provider by putting them
     * earlier in the sorted list. The objective is not to be opinionated about which models are better, but to ensure
     * that more commonly used, more recent, or flagship models are presented first to users.
     *
     * @since 1.0.0
     *
     * @param ModelMetadata $a First model.
     * @param ModelMetadata $b Second model.
     * @return int Comparison result.
     */
    protected function modelSortCallback(ModelMetadata $a, ModelMetadata $b): int
    {
        $aId = $a->getId();
        $bId = $b->getId();

        // Prefer NVIDIA-branded models.
        $aIsNvidia = str_starts_with($aId, 'nvidia/');
        $bIsNvidia = str_starts_with($bId, 'nvidia/');
        if ($aIsNvidia && !$bIsNvidia) {
            return -1;
        }
        if ($bIsNvidia && !$aIsNvidia) {
            return 1;
        }

        // Prefer Meta models.
        $aIsMeta = str_starts_with($aId, 'meta/');
        $bIsMeta = str_starts_with($bId, 'meta/');
        if ($aIsMeta && !$bIsMeta) {
            return -1;
        }
        if ($bIsMeta && !$aIsMeta) {
            return 1;
        }

        // Prefer newer model versions (higher version numbers).
        $aVersion = self::extractVersion($aId);
        $bVersion = self::extractVersion($bId);
        if ($aVersion !== null && $bVersion !== null) {
            if (version_compare($aVersion, $bVersion, '>')) {
                return -1;
            }
            if (version_compare($bVersion, $aVersion, '>')) {
                return 1;
            }
        }

        // Fallback: Sort alphabetically.
        return strcmp($aId, $bId);
    }

    /**
     * Extracts a version number from a model ID.
     *
     * @since 1.0.0
     *
     * @param string $modelId The model ID.
     * @return string|null The version string, or null if no version found.
     */
    private static function extractVersion(string $modelId): ?string
    {
        if (preg_match('/(\d+\.\d+(?:\.\d+)?)/', $modelId, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
