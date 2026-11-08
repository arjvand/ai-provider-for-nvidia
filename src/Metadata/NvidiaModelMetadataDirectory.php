<?php

declare(strict_types=1);

namespace Forgeia\NvidiaAiProvider\Metadata;

use Forgeia\NvidiaAiProvider\Provider\NvidiaProvider;
use Forgeia\NvidiaAiProvider\Settings\NvidiaProviderSettings;
use WordPress\AiClient\Files\Enums\MediaOrientationEnum;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\EmbeddingGeneration\Contracts\EmbeddingGenerationModelInterface;
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
	 * Derived from /v1/models fetch on 2026-11-07.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const IMAGE_GENERATION_MODELS = array(
		'google/diffusiongemma-26b-a4b-it',
	);

	/**
	 * Multimodal text generation model allowlist (accept image input).
	 *
	 * Derived from /v1/models fetch on 2026-11-07.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const MULTIMODAL_TEXT_MODELS = array(
		'adept/fuyu-8b',
		'deepseek-ai/deepseek-v4.1-flash',
		'google/deplot',
		'google/gemma-3-12b-it',
		'google/gemma-3-4b-it',
		'google/gemma-4-31b-it',
		'meta/llama-3.2-90b-vision-instruct',
		'meta/muse-glimmer-30b',
		'microsoft/kosmos-2',
		'microsoft/phi-3-vision-128k-instruct',
		'minimaxai/minimax-m3.1-flash',
		'mistralai/mistral-large-4-1t-a52b',
		'moonshotai/kimi-k2.6',
		'nvidia/cosmos-reason2-8b',
		'nvidia/ising-calibration-1.5-31b',
		'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning',
		'nvidia/nemotron-3.5-super-vl-120b-a12b',
		'nvidia/neva-22b',
		'nvidia/vila',
		'stepfun-ai/step-5-preview',
		'z-ai/glm-5.3-flash',
	);

	/**
	 * Text generation model allowlist (text-only chat completion).
	 *
	 * Derived from /v1/models fetch on 2026-11-07.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const TEXT_GENERATION_MODELS = array(
		'01-ai/yi-large',
		'ai21labs/jamba-1.5-large-instruct',
		'aisingapore/sea-lion-7b-instruct',
		'bigcode/starcoder2-15b',
		'databricks/dbrx-instruct',
		'deepseek-ai/deepseek-coder-6.7b-instruct',
		'google/codegemma-1.1-7b',
		'google/codegemma-7b',
		'google/gemma-2b',
		'google/recurrentgemma-2b',
		'ibm/granite-3.0-3b-a800m-instruct',
		'ibm/granite-3.0-8b-instruct',
		'ibm/granite-34b-code-instruct',
		'ibm/granite-8b-code-instruct',
		'meta/codellama-70b',
		'meta/llama2-70b',
		'microsoft/phi-3.5-moe-instruct',
		'mistralai/codestral-22b-instruct-v0.1',
		'mistralai/mistral-7b-instruct-v0.3',
		'mistralai/mistral-large',
		'mistralai/mistral-large-2-instruct',
		'mistralai/mixtral-8x22b-v0.1',
		'nv-mistralai/mistral-nemo-12b-instruct',
		'nvidia/llama-3.1-nemotron-51b-instruct',
		'nvidia/llama-3.1-nemotron-70b-instruct',
		'nvidia/llama-3.1-nemotron-ultra-253b-v1',
		'nvidia/llama3-chatqa-1.5-70b',
		'nvidia/mistral-nemo-minitron-8b-8k-instruct',
		'nvidia/nemotron-3-super-120b-a12b',
		'nvidia/nemotron-3-ultra-550b-a55b',
		'nvidia/nemotron-3.5-bolt-e4b',
		'nvidia/nemotron-3.5-lightning-30b-a3b',
		'nvidia/nemotron-4-340b-instruct',
		'nvidia/nemotron-nano-3-30b-a3b',
		'nvidia/riva-translate-4b-instruct',
		'nvidia/riva-translate-4b-instruct-v2',
		'openai/gpt-oss-20b',
		'poolside/laguna-xs-2.1',
		'writer/palmyra-creative-122b',
		'writer/palmyra-fin-70b-32k',
		'writer/palmyra-med-70b',
		'writer/palmyra-med-70b-32k',
		'zyphra/zamba2-7b-instruct',
	);

	/**
	 * Embedding model allowlist.
	 *
	 * Derived from /v1/models fetch on 2026-11-07.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const EMBEDDING_MODELS = array(
		'nvidia/embed-qa-4',
		'nvidia/llama-3.2-nemoretriever-1b-vlm-embed-v1',
		'nvidia/llama-3.2-nv-embedqa-1b-v1',
		'nvidia/llama-nemotron-embed-vl-1b-v2',
		'nvidia/nemotron-3-embed-1b',
		'nvidia/nemotron-3.5-embed-1b',
		'nvidia/nemotron-3.5-embed-8b',
		'nvidia/nv-embedqa-mistral-7b-v2',
		'nvidia/nvclip',
		'snowflake/arctic-embed-l',
	);

	/**
	 * Unsupported model allowlist (no SDK abstraction).
	 *
	 * Derived from /v1/models fetch on 2026-11-07.
	 *
	 * @since 1.1.0
	 *
	 * @var list<string>
	 */
	private const UNSUPPORTED_MODELS = array(
		'meta/llama-guard-4-12b',
		'nvidia/ai-synthetic-video-detector',
		'nvidia/llama-3.1-nemoguard-8b-content-safety',
		'nvidia/llama-3.1-nemoguard-8b-topic-control',
		'nvidia/llama-3.1-nemotron-safety-guard-8b-v3',
		'nvidia/nemotron-3.5-content-safety',
		'nvidia/nemotron-4-340b-reward',
		'nvidia/nemotron-parse',
		'nvidia/nemotron-parse-2.0',
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

		$modelsData = (array) $responseData['data'];
		$models     = array();

		foreach ( $modelsData as $modelData ) {
			$modelMetadata = $this->createModelMetadata( $modelData['id'] );
			if ( null !== $modelMetadata ) {
				$models[] = $modelMetadata;
			}
		}

		usort( $models, array( $this, 'modelSortCallback' ) );

		return $models;
	}

	/**
	 * Creates metadata for explicit model IDs without listing provider models.
	 *
	 * @since 1.4.0
	 *
	 * @param list<string> $modelIds The explicit model IDs.
	 * @return array<string, ModelMetadata> Map of model ID to model metadata, omitting unsupported and
	 *                                       unrecognized IDs.
	 */
	protected function createModelMetadataForExplicitModelIds( array $modelIds ): array {
		$modelsMetadata = array();

		foreach ( $modelIds as $modelId ) {
			$modelMetadata = $this->createModelMetadata( $modelId );
			if ( null !== $modelMetadata ) {
				$modelsMetadata[ $modelId ] = $modelMetadata;
			}
		}

		return $modelsMetadata;
	}

	/**
	 * Builds metadata for a single model based on the allowlists.
	 *
	 * Classification priority order: unsupported skipped, then image > multimodal > text > embedding.
	 *
	 * @since 1.4.0
	 *
	 * @param string $modelId The model ID.
	 * @return ModelMetadata|null The model metadata, or null if the model is unsupported or unrecognized.
	 */
	private function createModelMetadata( string $modelId ): ?ModelMetadata {
		if ( self::isUnsupportedModel( $modelId ) ) {
			return null;
		}

		if ( self::isImageGenerationModel( $modelId ) ) {
			return new ModelMetadata(
				$modelId,
				$modelId,
				array( CapabilityEnum::imageGeneration() ),
				self::getImageGenerationOptions()
			);
		}

		if ( self::isMultimodalTextModel( $modelId ) ) {
			return new ModelMetadata(
				$modelId,
				$modelId,
				self::getTextGenerationCapabilities(),
				self::getTextGenerationOptions( true )
			);
		}

		if ( self::isTextGenerationModel( $modelId ) ) {
			return new ModelMetadata(
				$modelId,
				$modelId,
				self::getTextGenerationCapabilities(),
				self::getTextGenerationOptions( false )
			);
		}

		if ( self::isEmbeddingModel( $modelId ) ) {
			if ( ! self::isEmbeddingGenerationSupported() ) {
				return null;
			}

			return new ModelMetadata(
				$modelId,
				$modelId,
				array( CapabilityEnum::embeddingGeneration() ),
				self::getEmbeddingGenerationOptions()
			);
		}

		$this->reportUnrecognizedModel( $modelId );

		return null;
	}

	/**
	 * Reports an unrecognized model ID.
	 *
	 * @since 1.4.0
	 *
	 * @param string $modelId The unrecognized model ID.
	 * @return void
	 */
	private function reportUnrecognizedModel( string $modelId ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Unrecognized NVIDIA model ID, skipping: ' . $modelId );
		}

		if ( function_exists( 'do_action' ) ) {
			do_action( 'forgeia_nvidia_unrecognized_model', $modelId );
		}
	}

	/**
	 * Returns the capabilities shared by text generation models.
	 *
	 * @since 1.4.0
	 *
	 * @return list<CapabilityEnum>
	 */
	private static function getTextGenerationCapabilities(): array {
		return array(
			CapabilityEnum::textGeneration(),
			CapabilityEnum::chatHistory(),
		);
	}

	/**
	 * Returns the base options shared by text generation models.
	 *
	 * @since 1.4.0
	 *
	 * @return list<SupportedOption>
	 */
	private static function getTextBaseOptions(): array {
		return array(
			new SupportedOption( OptionEnum::systemInstruction() ),
			new SupportedOption( OptionEnum::maxTokens() ),
			new SupportedOption( OptionEnum::temperature() ),
			new SupportedOption( OptionEnum::topP() ),
			new SupportedOption( OptionEnum::stopSequences() ),
			new SupportedOption( OptionEnum::functionDeclarations() ),
			new SupportedOption( OptionEnum::outputMimeType(), array( 'application/json' ) ),
			new SupportedOption( OptionEnum::outputSchema() ),
			new SupportedOption( OptionEnum::customOptions() ),
		);
	}

	/**
	 * Returns the options for text generation models.
	 *
	 * Multimodal models advertise text and text+image input modalities only.
	 *
	 * @since 1.4.0
	 *
	 * @param bool $supportsImageInput Whether the model accepts image input.
	 * @return list<SupportedOption>
	 */
	private static function getTextGenerationOptions( bool $supportsImageInput ): array {
		$allModalityCombinationsWithText = array(
			array( ModalityEnum::text() ),
		);

		if ( $supportsImageInput ) {
			$allModalityCombinationsWithText[] = array( ModalityEnum::text(), ModalityEnum::image() );
		}

		$options   = self::getTextBaseOptions();
		$options[] = new SupportedOption( OptionEnum::inputModalities(), $allModalityCombinationsWithText );
		$options[] = new SupportedOption( OptionEnum::outputModalities(), array( array( ModalityEnum::text() ) ) );

		return $options;
	}

	/**
	 * Returns the options for image generation models.
	 *
	 * @since 1.4.0
	 *
	 * @return list<SupportedOption>
	 */
	private static function getImageGenerationOptions(): array {
		return array(
			new SupportedOption( OptionEnum::inputModalities(), array( array( ModalityEnum::text() ) ) ),
			new SupportedOption( OptionEnum::outputModalities(), array( array( ModalityEnum::image() ) ) ),
			new SupportedOption( OptionEnum::candidateCount() ),
			new SupportedOption( OptionEnum::outputMimeType(), array( 'image/png', 'image/jpeg' ) ),
			new SupportedOption(
				OptionEnum::outputMediaOrientation(),
				array(
					MediaOrientationEnum::square(),
					MediaOrientationEnum::landscape(),
					MediaOrientationEnum::portrait(),
				)
			),
			new SupportedOption( OptionEnum::outputMediaAspectRatio(), array( '1:1', '3:2', '2:3' ) ),
			new SupportedOption( OptionEnum::customOptions() ),
		);
	}

	/**
	 * Returns the options for embedding generation models.
	 *
	 * @since 1.4.0
	 *
	 * @return list<SupportedOption>
	 */
	private static function getEmbeddingGenerationOptions(): array {
		return array(
			new SupportedOption( OptionEnum::inputModalities(), array( array( ModalityEnum::text() ) ) ),
		);
	}

	/**
	 * Checks whether the installed SDK supports embedding generation.
	 *
	 * Embedding models are only advertised when the SDK provides the embedding generation model interface
	 * (AI Client 1.4+). Older SDKs such as 1.3.1 do not define it.
	 *
	 * @since 1.4.0
	 *
	 * @return bool True if embedding generation is supported.
	 */
	private static function isEmbeddingGenerationSupported(): bool {
		return interface_exists( EmbeddingGenerationModelInterface::class );
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

		$preferredComparison = self::comparePreferredDefaultModels( $aId, $bId );
		if ( 0 !== $preferredComparison ) {
			return $preferredComparison;
		}

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
	 * Compares model IDs against saved default model preferences.
	 *
	 * @since 1.2.0
	 *
	 * @param string $aId First model ID.
	 * @param string $bId Second model ID.
	 * @return int Sort comparison result.
	 */
	private static function comparePreferredDefaultModels( string $aId, string $bId ): int {
		$preferredModelIds = array_values(
			array_filter(
				array(
					NvidiaProviderSettings::getDefaultTextGenerationModel(),
					NvidiaProviderSettings::getDefaultImageGenerationModel(),
				),
				static function ( string $modelId ): bool {
					return '' !== $modelId;
				}
			)
		);

		if ( array() === $preferredModelIds ) {
			return 0;
		}

		$aRank = array_search( $aId, $preferredModelIds, true );
		$bRank = array_search( $bId, $preferredModelIds, true );

		if ( false !== $aRank && false === $bRank ) {
			return -1;
		}

		if ( false !== $bRank && false === $aRank ) {
			return 1;
		}

		if ( false !== $aRank && false !== $bRank && $aRank !== $bRank ) {
			return $aRank <=> $bRank;
		}

		return 0;
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
