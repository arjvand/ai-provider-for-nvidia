<?php

declare(strict_types=1);

namespace Forgeia\NvidiaAiProvider\Metadata;

use Forgeia\NvidiaAiProvider\Provider\NvidiaProvider;
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
class NvidiaModelMetadataDirectory extends AbstractOpenAiCompatibleModelMetadataDirectory {

	/**
	 * Image generation model allowlist.
	 *
	 * Derived from /v1/models fetch on 2026-06-19.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const IMAGE_GENERATION_MODELS = array(
		'google/diffusiongemma-26b-a4b-it',
	);

	/**
	 * Multimodal text generation model allowlist (accept image/document input).
	 *
	 * Derived from /v1/models fetch on 2026-06-19.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const MULTIMODAL_TEXT_MODELS = array(
		'adept/fuyu-8b',
		'google/deplot',
		'meta/llama-3.2-11b-vision-instruct',
		'meta/llama-3.2-90b-vision-instruct',
		'microsoft/kosmos-2',
		'microsoft/phi-3-vision-128k-instruct',
		'microsoft/phi-4-multimodal-instruct',
		'nvidia/cosmos-reason2-8b',
		'nvidia/llama-3.1-nemotron-nano-vl-8b-v1',
		'nvidia/nemotron-nano-12b-v2-vl',
		'nvidia/neva-22b',
		'nvidia/nvclip',
		'nvidia/vila',
	);

	/**
	 * Text generation model allowlist (text-only chat completion).
	 *
	 * Derived from /v1/models fetch on 2026-06-19.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const TEXT_GENERATION_MODELS = array(
		'01-ai/yi-large',
		'abacusai/dracarys-llama-3.1-70b-instruct',
		'ai21labs/jamba-1.5-large-instruct',
		'aisingapore/sea-lion-7b-instruct',
		'bigcode/starcoder2-15b',
		'bytedance/seed-oss-36b-instruct',
		'databricks/dbrx-instruct',
		'deepseek-ai/deepseek-coder-6.7b-instruct',
		'deepseek-ai/deepseek-v4-flash',
		'deepseek-ai/deepseek-v4-pro',
		'google/codegemma-1.1-7b',
		'google/codegemma-7b',
		'google/gemma-2-2b-it',
		'google/gemma-2b',
		'google/gemma-3-12b-it',
		'google/gemma-3-4b-it',
		'google/gemma-3n-e2b-it',
		'google/gemma-3n-e4b-it',
		'google/gemma-4-31b-it',
		'google/recurrentgemma-2b',
		'ibm/granite-3.0-3b-a800m-instruct',
		'ibm/granite-3.0-8b-instruct',
		'ibm/granite-34b-code-instruct',
		'ibm/granite-8b-code-instruct',
		'meta/codellama-70b',
		'meta/llama-3.1-70b-instruct',
		'meta/llama-3.1-8b-instruct',
		'meta/llama-3.2-1b-instruct',
		'meta/llama-3.2-3b-instruct',
		'meta/llama-3.3-70b-instruct',
		'meta/llama-4-maverick-17b-128e-instruct',
		'meta/llama2-70b',
		'microsoft/phi-3.5-moe-instruct',
		'microsoft/phi-4-mini-instruct',
		'minimaxai/minimax-m2.7',
		'minimaxai/minimax-m3',
		'mistralai/codestral-22b-instruct-v0.1',
		'mistralai/ministral-14b-instruct-2512',
		'mistralai/mistral-7b-instruct-v0.3',
		'mistralai/mistral-large',
		'mistralai/mistral-large-2-instruct',
		'mistralai/mistral-large-3-675b-instruct-2512',
		'mistralai/mistral-medium-3.5-128b',
		'mistralai/mistral-nemotron',
		'mistralai/mistral-small-4-119b-2603',
		'mistralai/mixtral-8x22b-v0.1',
		'mistralai/mixtral-8x7b-instruct-v0.1',
		'moonshotai/kimi-k2.6',
		'nv-mistralai/mistral-nemo-12b-instruct',
		'nvidia/ising-calibration-1-35b-a3b',
		'nvidia/llama-3.1-nemotron-51b-instruct',
		'nvidia/llama-3.1-nemotron-70b-instruct',
		'nvidia/llama-3.1-nemotron-nano-8b-v1',
		'nvidia/llama-3.1-nemotron-ultra-253b-v1',
		'nvidia/llama-3.3-nemotron-super-49b-v1',
		'nvidia/llama-3.3-nemotron-super-49b-v1.5',
		'nvidia/llama3-chatqa-1.5-70b',
		'nvidia/mistral-nemo-minitron-8b-8k-instruct',
		'nvidia/nemotron-3-nano-30b-a3b',
		'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning',
		'nvidia/nemotron-3-super-120b-a12b',
		'nvidia/nemotron-3-ultra-550b-a55b',
		'nvidia/nemotron-4-340b-instruct',
		'nvidia/nemotron-mini-4b-instruct',
		'nvidia/nemotron-nano-3-30b-a3b',
		'nvidia/nvidia-nemotron-nano-9b-v2',
		'nvidia/riva-translate-4b-instruct',
		'nvidia/riva-translate-4b-instruct-v1.1',
		'openai/gpt-oss-120b',
		'openai/gpt-oss-20b',
		'qwen/qwen3-next-80b-a3b-instruct',
		'qwen/qwen3.5-122b-a10b',
		'qwen/qwen3.5-397b-a17b',
		'sarvamai/sarvam-m',
		'stepfun-ai/step-3.5-flash',
		'stepfun-ai/step-3.7-flash',
		'stockmark/stockmark-2-100b-instruct',
		'upstage/solar-10.7b-instruct',
		'writer/palmyra-creative-122b',
		'writer/palmyra-fin-70b-32k',
		'writer/palmyra-med-70b',
		'writer/palmyra-med-70b-32k',
		'z-ai/glm-5.1',
		'zyphra/zamba2-7b-instruct',
	);

	/**
	 * Embedding model allowlist.
	 *
	 * Derived from /v1/models fetch on 2026-06-19.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const EMBEDDING_MODELS = array(
		'baai/bge-m3',
		'nvidia/embed-qa-4',
		'nvidia/llama-3.2-nemoretriever-1b-vlm-embed-v1',
		'nvidia/llama-3.2-nv-embedqa-1b-v1',
		'nvidia/llama-nemotron-embed-1b-v2',
		'nvidia/llama-nemotron-embed-vl-1b-v2',
		'nvidia/nv-embed-v1',
		'nvidia/nv-embedcode-7b-v1',
		'nvidia/nv-embedqa-e5-v5',
		'nvidia/nv-embedqa-mistral-7b-v2',
		'snowflake/arctic-embed-l',
	);

	/**
	 * Unsupported model allowlist (no SDK abstraction).
	 *
	 * Derived from /v1/models fetch on 2026-06-19.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const UNSUPPORTED_MODELS = array(
		'meta/llama-guard-4-12b',
		'nvidia/ai-synthetic-video-detector',
		'nvidia/gliner-pii',
		'nvidia/llama-3.1-nemoguard-8b-content-safety',
		'nvidia/llama-3.1-nemoguard-8b-topic-control',
		'nvidia/llama-3.1-nemotron-safety-guard-8b-v3',
		'nvidia/nemoretriever-parse',
		'nvidia/nemotron-3-content-safety',
		'nvidia/nemotron-3.5-content-safety',
		'nvidia/nemotron-4-340b-reward',
		'nvidia/nemotron-content-safety-reasoning-4b',
		'nvidia/nemotron-parse',
	);

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected function createRequest( HttpMethodEnum $method, string $path, array $headers = array(), $data = null ): Request {
		return new Request(
			$method,
			NvidiaProvider::url( $path ),
			$headers,
			$data
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected function parseResponseToModelMetadataList( Response $response ): array {
		/** @var ModelsResponseData $responseData */
		$responseData = $response->getData();
		if ( ! isset( $responseData['data'] ) || ! $responseData['data'] ) {
			throw ResponseException::fromMissingData( 'NVIDIA', 'data' );
		}

		$allModalityCombinationsWithText = array(
			array( ModalityEnum::text() ),
			array( ModalityEnum::text(), ModalityEnum::image() ),
			array( ModalityEnum::text(), ModalityEnum::document() ),
		);

		// Text generation capabilities and options.
		$textCapabilities       = array(
			CapabilityEnum::textGeneration(),
			CapabilityEnum::chatHistory(),
		);
		$textBaseOptions        = array(
			new SupportedOption( OptionEnum::systemInstruction() ),
			new SupportedOption( OptionEnum::maxTokens() ),
			new SupportedOption( OptionEnum::temperature() ),
			new SupportedOption( OptionEnum::topP() ),
			new SupportedOption( OptionEnum::stopSequences() ),
			new SupportedOption( OptionEnum::functionDeclarations() ),
			new SupportedOption( OptionEnum::customOptions() ),
		);
		$textOptions            = array_merge(
			$textBaseOptions,
			array(
				new SupportedOption( OptionEnum::inputModalities(), array( array( ModalityEnum::text() ) ) ),
				new SupportedOption( OptionEnum::outputModalities(), array( array( ModalityEnum::text() ) ) ),
			)
		);
		$multimodalInputOptions = array_merge(
			$textBaseOptions,
			array(
				new SupportedOption(
					OptionEnum::inputModalities(),
					$allModalityCombinationsWithText
				),
				new SupportedOption( OptionEnum::outputModalities(), array( array( ModalityEnum::text() ) ) ),
			)
		);

		// Image generation capabilities and options.
		$imageCapabilities = array(
			CapabilityEnum::imageGeneration(),
		);
		$imageOptions      = array(
			new SupportedOption( OptionEnum::inputModalities(), array( array( ModalityEnum::text() ) ) ),
			new SupportedOption( OptionEnum::outputModalities(), array( array( ModalityEnum::image() ) ) ),
			new SupportedOption( OptionEnum::candidateCount() ),
			new SupportedOption( OptionEnum::outputMimeType(), array( 'image/png', 'image/jpeg' ) ),
			new SupportedOption(
				OptionEnum::outputMediaOrientation(),
				array(
					\WordPress\AiClient\Files\Enums\MediaOrientationEnum::square(),
					\WordPress\AiClient\Files\Enums\MediaOrientationEnum::landscape(),
					\WordPress\AiClient\Files\Enums\MediaOrientationEnum::portrait(),
				)
			),
			new SupportedOption( OptionEnum::outputMediaAspectRatio(), array( '1:1', '3:2', '2:3' ) ),
			new SupportedOption( OptionEnum::customOptions() ),
		);

		// Embedding capabilities (no SDK model class yet).
		$embeddingCapabilities = array(
			CapabilityEnum::embeddingGeneration(),
		);

		$modelsData = (array) $responseData['data'];

		$models = array_values(
			array_filter(
				array_map(
					static function ( array $modelData ) use (
						$textCapabilities,
						$textOptions,
						$multimodalInputOptions,
						$imageCapabilities,
						$imageOptions,
						$embeddingCapabilities
					): ?ModelMetadata {
						$modelId = $modelData['id'];

						// Classification priority order: unsupported skipped, then image > multimodal > text > embedding.
						if ( self::isUnsupportedModel( $modelId ) ) {
							return null;
						}

						if ( self::isImageGenerationModel( $modelId ) ) {
							$modelCaps    = $imageCapabilities;
							$modelOptions = $imageOptions;
						} elseif ( self::isMultimodalTextModel( $modelId ) ) {
							$modelCaps    = $textCapabilities;
							$modelOptions = $multimodalInputOptions;
						} elseif ( self::isTextGenerationModel( $modelId ) ) {
							$modelCaps    = $textCapabilities;
							$modelOptions = $textOptions;
						} elseif ( self::isEmbeddingModel( $modelId ) ) {
							$modelCaps    = $embeddingCapabilities;
							$modelOptions = array();
						} else {
							// Unrecognized model: log warning and skip.
							// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
							error_log(
								'Unrecognized NVIDIA model ID, skipping: ' . $modelId
							);
							return null;
						}

						return new ModelMetadata(
							$modelId,
							$modelId,
							$modelCaps,
							$modelOptions
						);
					},
					$modelsData
				),
				static function ( ?ModelMetadata $model ): bool {
					return $model !== null;
				}
			)
		);

		usort( $models, array( $this, 'modelSortCallback' ) );

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
	private static function isImageGenerationModel( string $modelId ): bool {
		return in_array( $modelId, self::IMAGE_GENERATION_MODELS, true );
	}

	/**
	 * Checks if a model is a text generation model.
	 *
	 * @since 1.0.0
	 *
	 * @param string $modelId The model ID.
	 * @return bool True if the model is a text generation model.
	 */
	private static function isTextGenerationModel( string $modelId ): bool {
		return in_array( $modelId, self::TEXT_GENERATION_MODELS, true );
	}

	/**
	 * Checks if a model is a multimodal text model.
	 *
	 * @since 1.1.0
	 *
	 * @param string $modelId The model ID.
	 * @return bool True if the model is a multimodal text model.
	 */
	private static function isMultimodalTextModel( string $modelId ): bool {
		return in_array( $modelId, self::MULTIMODAL_TEXT_MODELS, true );
	}

	/**
	 * Checks if a model is an embedding model.
	 *
	 * @since 1.1.0
	 *
	 * @param string $modelId The model ID.
	 * @return bool True if the model is an embedding model.
	 */
	private static function isEmbeddingModel( string $modelId ): bool {
		return in_array( $modelId, self::EMBEDDING_MODELS, true );
	}

	/**
	 * Checks if a model is in the unsupported allowlist.
	 *
	 * @since 1.1.0
	 *
	 * @param string $modelId The model ID.
	 * @return bool True if the model is unsupported.
	 */
	private static function isUnsupportedModel( string $modelId ): bool {
		return in_array( $modelId, self::UNSUPPORTED_MODELS, true );
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
	protected function modelSortCallback( ModelMetadata $a, ModelMetadata $b ): int {
		$aId = $a->getId();
		$bId = $b->getId();

		// Prefer NVIDIA-branded models.
		$aIsNvidia = str_starts_with( $aId, 'nvidia/' );
		$bIsNvidia = str_starts_with( $bId, 'nvidia/' );
		if ( $aIsNvidia && ! $bIsNvidia ) {
			return -1;
		}
		if ( $bIsNvidia && ! $aIsNvidia ) {
			return 1;
		}

		// Prefer Meta models.
		$aIsMeta = str_starts_with( $aId, 'meta/' );
		$bIsMeta = str_starts_with( $bId, 'meta/' );
		if ( $aIsMeta && ! $bIsMeta ) {
			return -1;
		}
		if ( $bIsMeta && ! $aIsMeta ) {
			return 1;
		}

		// Prefer newer model versions (higher version numbers).
		$aVersion = self::extractVersion( $aId );
		$bVersion = self::extractVersion( $bId );
		if ( $aVersion !== null && $bVersion !== null ) {
			if ( version_compare( $aVersion, $bVersion, '>' ) ) {
				return -1;
			}
			if ( version_compare( $bVersion, $aVersion, '>' ) ) {
				return 1;
			}
		}

		// Fallback: Sort alphabetically.
		return strcmp( $aId, $bId );
	}

	/**
	 * Extracts a version number from a model ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $modelId The model ID.
	 * @return string|null The version string, or null if no version found.
	 */
	private static function extractVersion( string $modelId ): ?string {
		if ( preg_match( '/(\d+\.\d+(?:\.\d+)?)/', $modelId, $matches ) ) {
			return $matches[1];
		}
		return null;
	}
}
