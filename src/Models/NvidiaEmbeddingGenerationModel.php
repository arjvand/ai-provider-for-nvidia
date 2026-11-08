<?php

declare(strict_types=1);

namespace Forgeia\NvidiaAiProvider\Models;

use Forgeia\NvidiaAiProvider\Provider\NvidiaProvider;
use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Messages\DTO\MessagePart;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiBasedModel;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Http\Util\ResponseUtil;
use WordPress\AiClient\Providers\Models\EmbeddingGeneration\Contracts\EmbeddingGenerationModelInterface;
use WordPress\AiClient\Results\DTO\Embedding;
use WordPress\AiClient\Results\DTO\EmbeddingResult;
use WordPress\AiClient\Results\DTO\TokenUsage;

/**
 * Class for an NVIDIA embedding generation model using the Embeddings API.
 *
 * @since 1.4.0
 *
 * @phpstan-type EmbeddingData array{
 *     index?: int,
 *     embedding?: array<int, mixed>,
 *     object?: string
 * }
 * @phpstan-type UsageData array{
 *     prompt_tokens?: int,
 *     total_tokens?: int
 * }
 * @phpstan-type ResponseData array{
 *     id?: string,
 *     data?: list<EmbeddingData>,
 *     usage?: UsageData
 * }
 */
class NvidiaEmbeddingGenerationModel extends AbstractApiBasedModel implements EmbeddingGenerationModelInterface {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.4.0
	 */
	final public function generateEmbeddingResult( array $inputs ): EmbeddingResult {
		$params = $this->prepareEmbeddingParams( $inputs );

		$request = new Request(
			HttpMethodEnum::POST(),
			NvidiaProvider::url( 'embeddings' ),
			array( 'Content-Type' => 'application/json' ),
			$params,
			$this->getRequestOptions()
		);

		// Add authentication credentials to the request.
		$request = $this->getRequestAuthentication()->authenticateRequest( $request );

		// Send and process the request.
		$response = $this->getHttpTransporter()->send( $request );
		ResponseUtil::throwIfNotSuccessful( $response );

		return $this->parseResponseToEmbeddingResult( $response );
	}

	/**
	 * Prepares the given inputs into parameters for the API request.
	 *
	 * @since 1.4.0
	 *
	 * @param list<MessagePart> $inputs The inputs to embed.
	 * @return array<string, mixed> The parameters for the API request.
	 */
	protected function prepareEmbeddingParams( array $inputs ): array {
		if ( array() === $inputs ) {
			throw new InvalidArgumentException( 'At least one input is required to generate an embedding.' );
		}

		$texts = array();
		foreach ( $inputs as $input ) {
			$text = $input->getText();
			if ( null === $text ) {
				throw new InvalidArgumentException(
					'NVIDIA embedding models only support text inputs.'
				);
			}
			$texts[] = $text;
		}

		$params = array(
			'model'           => $this->metadata()->getId(),
			'input'           => $texts,
			'encoding_format' => 'float',
		);

		if ( $this->requiresInputTypeQuery() ) {
			$params['input_type'] = 'query';
		}

		return $params;
	}

	/**
	 * Checks whether the model requires the `input_type` request parameter.
	 *
	 * NV-EmbedQA models require `input_type` to be set to `query`.
	 *
	 * @since 1.4.0
	 *
	 * @return bool True if the model requires the `input_type` request parameter.
	 */
	protected function requiresInputTypeQuery(): bool {
		$modelId = strtolower( $this->metadata()->getId() );

		return strpos( $modelId, 'embedqa' ) !== false || strpos( $modelId, 'embed-qa' ) !== false;
	}

	/**
	 * Parses the response from the API endpoint to an embedding result.
	 *
	 * @since 1.4.0
	 *
	 * @param Response $response The response from the API endpoint.
	 * @return EmbeddingResult The parsed embedding result.
	 */
	protected function parseResponseToEmbeddingResult( Response $response ): EmbeddingResult {
		/** @var ResponseData $responseData */
		$responseData = $response->getData();

		if (
			! isset( $responseData['data'] ) ||
			! is_array( $responseData['data'] ) ||
			array() === $responseData['data']
		) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw ResponseException::fromMissingData( $this->providerMetadata()->getName(), 'data' );
		}

		$providerName = $this->providerMetadata()->getName();
		$embeddings   = array();
		$dimensions   = 0;

		foreach ( array_values( $responseData['data'] ) as $position => $entry ) {
			if ( ! is_array( $entry ) ) {
				throw ResponseException::fromInvalidData(
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					$providerName,
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					"data[{$position}]",
					'The value must be an associative array.'
				);
			}

			if (
				! isset( $entry['embedding'] ) ||
				! is_array( $entry['embedding'] ) ||
				array() === $entry['embedding']
			) {
				throw ResponseException::fromInvalidData(
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					$providerName,
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					"data[{$position}].embedding",
					'The value must be a non-empty array of numbers.'
				);
			}

			$values = array();
			foreach ( array_values( $entry['embedding'] ) as $value_index => $value ) {
				if ( ! is_int( $value ) && ! is_float( $value ) ) {
					throw ResponseException::fromInvalidData(
						// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
						$providerName,
						// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
						"data[{$position}].embedding[{$value_index}]",
						'The value must be an integer or float.'
					);
				}
				$values[] = $value;
			}

			$valueCount = count( $values );
			if ( 0 === $dimensions ) {
				$dimensions = $valueCount;
			} elseif ( $dimensions !== $valueCount ) {
				throw ResponseException::fromInvalidData(
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					$providerName,
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					"data[{$position}].embedding",
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					sprintf( 'Expected %d values, received %d.', $dimensions, $valueCount )
				);
			}

			$index = isset( $entry['index'] ) && is_int( $entry['index'] ) ? $entry['index'] : $position;

			$embeddings[ $index ] = new Embedding( $values, $dimensions );
		}

		ksort( $embeddings );

		$id = isset( $responseData['id'] ) && is_string( $responseData['id'] ) ? $responseData['id'] : '';

		$promptTokens = 0;
		$totalTokens  = 0;
		if ( isset( $responseData['usage'] ) && is_array( $responseData['usage'] ) ) {
			$usage = $responseData['usage'];
			if ( isset( $usage['prompt_tokens'] ) && is_int( $usage['prompt_tokens'] ) ) {
				$promptTokens = $usage['prompt_tokens'];
			}
			$totalTokens = $promptTokens;
			if ( isset( $usage['total_tokens'] ) && is_int( $usage['total_tokens'] ) ) {
				$totalTokens = $usage['total_tokens'];
			}
		}

		$tokenUsage = new TokenUsage( $promptTokens, 0, $totalTokens );

		// Use any other data from the response as provider-specific response metadata.
		$additionalData = $responseData;
		unset( $additionalData['id'], $additionalData['data'], $additionalData['usage'] );

		return new EmbeddingResult(
			$id,
			array_values( $embeddings ),
			$dimensions,
			$tokenUsage,
			$this->providerMetadata(),
			$this->metadata(),
			$additionalData
		);
	}
}
