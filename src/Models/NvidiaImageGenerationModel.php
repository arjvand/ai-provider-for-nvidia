<?php

declare(strict_types=1);

namespace Forgeia\NvidiaAiProvider\Models;

use Forgeia\NvidiaAiProvider\Provider\NvidiaProvider;
use WordPress\AiClient\Files\Enums\MediaOrientationEnum;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleImageGenerationModel;

/**
 * Class for an NVIDIA image generation model using the Images API.
 *
 * @since 1.0.0
 */
class NvidiaImageGenerationModel extends AbstractOpenAiCompatibleImageGenerationModel {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected function createRequest(
		HttpMethodEnum $method,
		string $path,
		array $headers = array(),
		$data = null
	): Request {
		return new Request(
			$method,
			NvidiaProvider::url( $path ),
			$headers,
			$data,
			$this->getRequestOptions()
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected function prepareGenerateImageParams( array $prompt ): array {
		$params = parent::prepareGenerateImageParams( $prompt );

		/*
		 * NVIDIA NIM image generation models (Stable Diffusion, Flux) use
		 * the OpenAI-compatible Images API format.
		 */
		return $params;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected function prepareSizeParam( ?MediaOrientationEnum $orientation, ?string $aspectRatio ): string {
		$modelId = $this->metadata()->getId();

		// If aspect ratio is provided, map it to size.
		if ( $aspectRatio !== null ) {
			$aspectRatioMap = array(
				'1:1'  => '1024x1024',
				'3:2'  => '1536x1024',
				'2:3'  => '1024x1536',
				'16:9' => '1792x1024',
				'9:16' => '1024x1792',
			);
			if ( isset( $aspectRatioMap[ $aspectRatio ] ) ) {
				return $aspectRatioMap[ $aspectRatio ];
			}
		}

		// Map orientation to size.
		if ( $orientation !== null ) {
			if ( $orientation->isLandscape() ) {
				return '1536x1024';
			}
			if ( $orientation->isPortrait() ) {
				return '1024x1536';
			}
		}

		// Default to square.
		return '1024x1024';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected function getResultId( array $responseData ): string {
		// The Images API returns `created` timestamp instead of `id`.
		return isset( $responseData['created'] ) && is_int( $responseData['created'] )
			? 'img-' . $responseData['created']
			: '';
	}
}
