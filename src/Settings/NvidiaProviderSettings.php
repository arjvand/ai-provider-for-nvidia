<?php

declare(strict_types=1);

namespace Forgeia\NvidiaAiProvider\Settings;

/**
 * Settings storage for the NVIDIA provider plugin.
 *
 * @since 1.2.0
 *
 * @phpstan-type SettingsArray array{
 *     default_text_generation_model: string,
 *     default_image_generation_model: string
 * }
 */
class NvidiaProviderSettings {

	public const OPTION_GROUP                       = 'forgeia_nvidia_ai_provider';
	public const OPTION_NAME                        = 'forgeia_nvidia_ai_provider_settings';
	public const KEY_DEFAULT_TEXT_GENERATION_MODEL  = 'default_text_generation_model';
	public const KEY_DEFAULT_IMAGE_GENERATION_MODEL = 'default_image_generation_model';

	/**
	 * Registers the plugin settings with WordPress.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public static function register(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( self::class, 'sanitize' ),
				'default'           => self::getDefaultSettings(),
			)
		);
	}

	/**
	 * Returns the normalized saved settings.
	 *
	 * @since 1.2.0
	 *
	 * @return SettingsArray
	 */
	public static function getSettings(): array {
		if ( ! function_exists( 'get_option' ) ) {
			return self::getDefaultSettings();
		}

		return self::normalizeSettings(
			get_option( self::OPTION_NAME, self::getDefaultSettings() )
		);
	}

	/**
	 * Returns the saved default text generation model ID.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function getDefaultTextGenerationModel(): string {
		$settings = self::getSettings();

		return $settings[ self::KEY_DEFAULT_TEXT_GENERATION_MODEL ];
	}

	/**
	 * Returns the saved default image generation model ID.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function getDefaultImageGenerationModel(): string {
		$settings = self::getSettings();

		return $settings[ self::KEY_DEFAULT_IMAGE_GENERATION_MODEL ];
	}

	/**
	 * Sanitizes settings before WordPress persists them.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Raw submitted option value.
	 * @return SettingsArray
	 */
	public static function sanitize( $value ): array {
		return self::normalizeSettings( $value );
	}

	/**
	 * Returns empty defaults.
	 *
	 * @since 1.2.0
	 *
	 * @return SettingsArray
	 */
	public static function getDefaultSettings(): array {
		return array(
			self::KEY_DEFAULT_TEXT_GENERATION_MODEL  => '',
			self::KEY_DEFAULT_IMAGE_GENERATION_MODEL => '',
		);
	}

	/**
	 * Normalizes raw option data into the expected settings shape.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Raw option value.
	 * @return SettingsArray
	 */
	private static function normalizeSettings( $value ): array {
		$settings = self::getDefaultSettings();

		if ( ! is_array( $value ) ) {
			return $settings;
		}

		$settings[ self::KEY_DEFAULT_TEXT_GENERATION_MODEL ]  = self::sanitizeModelId(
			$value[ self::KEY_DEFAULT_TEXT_GENERATION_MODEL ] ?? ''
		);
		$settings[ self::KEY_DEFAULT_IMAGE_GENERATION_MODEL ] = self::sanitizeModelId(
			$value[ self::KEY_DEFAULT_IMAGE_GENERATION_MODEL ] ?? ''
		);

		return $settings;
	}

	/**
	 * Sanitizes an NVIDIA model ID.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Raw model ID.
	 * @return string
	 */
	private static function sanitizeModelId( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$modelId = trim( $value );
		if ( '' === $modelId ) {
			return '';
		}

		$sanitized = preg_replace( '/[^A-Za-z0-9._:\/-]/', '', $modelId );

		return is_string( $sanitized ) ? $sanitized : '';
	}
}
