<?php

declare(strict_types=1);

namespace Forgeia\NvidiaAiProvider\Models;

use Forgeia\NvidiaAiProvider\Provider\NvidiaProvider;
use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Messages\DTO\Message;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

/**
 * Class for an NVIDIA text generation model using the Chat Completions API.
 *
 * This builds on the SDK's OpenAI-compatible implementation with NVIDIA NIM
 * specific adjustments for message content and structured output.
 *
 * @since 1.0.0
 */
class NvidiaTextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel {

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
	 * NVIDIA NIM does not support streaming through this provider, so it is
	 * always disabled. A custom `stream` option is rejected because it would
	 * conflict with that fixed parameter.
	 *
	 * @since 1.0.0
	 *
	 * @param list<Message> $prompt The prompt to generate text for. Either a single message or a list of messages
	 *                              from a chat.
	 * @return array<string, mixed> The parameters for the API request.
	 */
	protected function prepareGenerateTextParams( array $prompt ): array {
		$params = parent::prepareGenerateTextParams( $prompt );

		if ( array_key_exists( 'stream', $this->getConfig()->getCustomOptions() ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new InvalidArgumentException(
				sprintf(
					'The custom option "%s" conflicts with an existing parameter.',
					'stream'
				)
			);
		}

		$params['stream'] = false;

		return $params;
	}

	/**
	 * {@inheritDoc}
	 *
	 * NVIDIA NIM expects plain string content for text-only messages, while the
	 * OpenAI-compatible base class always uses the array content format. Content
	 * arrays that contain images, audio, or other files are left untouched.
	 *
	 * @since 1.0.0
	 *
	 * @param list<Message> $messages The messages to prepare.
	 * @param string|null $systemInstruction An optional system instruction to prepend to the messages.
	 * @return array<int, array<string, mixed>> The prepared messages parameter.
	 */
	protected function prepareMessagesParam( array $messages, ?string $systemInstruction = null ): array {
		$messagesParam = parent::prepareMessagesParam( $messages, $systemInstruction );

		foreach ( $messagesParam as $index => $messageData ) {
			if ( ! isset( $messageData['content'] ) || ! is_array( $messageData['content'] ) ) {
				continue;
			}

			$content = $messageData['content'];

			if ( array() === $content ) {
				// Assistant tool-call messages historically omitted the content key.
				if ( isset( $messageData['tool_calls'] ) ) {
					unset( $messagesParam[ $index ]['content'] );
				} else {
					$messagesParam[ $index ]['content'] = '';
				}
				continue;
			}

			$texts = array();
			foreach ( $content as $contentPart ) {
				if ( ! is_array( $contentPart ) || 'text' !== ( $contentPart['type'] ?? null ) ) {
					continue 2;
				}

				$text = $contentPart['text'] ?? null;
				if ( ! is_string( $text ) ) {
					continue 2;
				}

				$texts[] = $text;
			}

			$messagesParam[ $index ]['content'] = implode( '', $texts );
		}

		return $messagesParam;
	}

	/**
	 * Prepares the response_format parameter for the API request.
	 *
	 * This is only called if the output MIME type is `application/json`.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string, mixed>|null $outputSchema The output schema.
	 * @return array<string, mixed> The prepared response format parameter.
	 */
	protected function prepareResponseFormatParam( ?array $outputSchema ): array {
		if ( $outputSchema !== null ) {
			return array(
				'type'        => 'json_schema',
				'json_schema' => array(
					'name'   => 'response',
					'schema' => $outputSchema,
				),
			);
		}
		return array( 'type' => 'json_object' );
	}
}
