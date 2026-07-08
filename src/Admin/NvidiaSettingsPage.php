<?php

declare(strict_types=1);

namespace Forgeia\NvidiaAiProvider\Admin;

use Forgeia\NvidiaAiProvider\Provider\NvidiaProvider;
use Forgeia\NvidiaAiProvider\Settings\NvidiaProviderSettings;
use Throwable;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

/**
 * Admin settings page for the NVIDIA provider.
 *
 * @since 1.2.0
 *
 * @phpstan-type ModelChoice array{id:string,name:string}
 * @phpstan-type UiData array{
 *     optionName: string,
 *     keys: array{text:string,image:string},
 *     settings: array{default_text_generation_model:string,default_image_generation_model:string},
 *     textModels: list<ModelChoice>,
 *     imageModels: list<ModelChoice>,
 *     loadError: string,
 *     labels: array<string,string>
 * }
 */
class NvidiaSettingsPage {

	private const PAGE_SLUG     = 'forgeia-nvidia-ai-provider';
	private const SCRIPT_HANDLE = 'forgeia-nvidia-settings';
	private const STYLE_HANDLE  = 'forgeia-nvidia-settings';

	/**
	 * Registers the submenu page under Settings.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public static function register(): void {
		add_options_page(
			__( 'Forgeia NVIDIA', 'forgeia-ai-provider-for-nvidia' ),
			__( 'Forgeia NVIDIA', 'forgeia-ai-provider-for-nvidia' ),
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render' )
		);
	}

	/**
	 * Enqueues the no-build WordPress Design System screen assets.
	 *
	 * @since 1.2.0
	 *
	 * @param string $hookSuffix Current admin page hook suffix.
	 * @return void
	 */
	public static function enqueueAssets( string $hookSuffix ): void {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hookSuffix ) {
			return;
		}

		$pluginFile = dirname( __DIR__, 2 ) . '/forgeia-ai-provider-for-nvidia.php';

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style(
			self::STYLE_HANDLE,
			plugins_url( 'assets/css/settings-page.css', $pluginFile ),
			array( 'wp-components' ),
			self::assetVersion( dirname( __DIR__, 2 ) . '/assets/css/settings-page.css' )
		);
		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			plugins_url( 'assets/js/settings-page.js', $pluginFile ),
			array( 'wp-components', 'wp-dom-ready', 'wp-element', 'wp-i18n' ),
			self::assetVersion( dirname( __DIR__, 2 ) . '/assets/js/settings-page.js' ),
			true
		);
		wp_add_inline_script(
			self::SCRIPT_HANDLE,
			'window.forgeiaNvidiaSettings = ' . wp_json_encode( self::getUiData() ) . ';',
			'before'
		);
	}

	/**
	 * Renders the settings page form.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap forgeia-nvidia-settings-page">';
		echo '<h1>' . esc_html__( 'Forgeia NVIDIA Settings', 'forgeia-ai-provider-for-nvidia' ) . '</h1>';
		echo '<form action="options.php" method="post" id="forgeia-nvidia-settings-form">';
		settings_fields( NvidiaProviderSettings::OPTION_GROUP );
		echo '<div id="forgeia-nvidia-settings-root"></div>';
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Returns data consumed by the WPDS JavaScript screen.
	 *
	 * @since 1.2.0
	 *
	 * @return UiData
	 */
	private static function getUiData(): array {
		$modelChoices = self::getModelChoices();

		return array(
			'optionName'  => NvidiaProviderSettings::OPTION_NAME,
			'keys'        => array(
				'text'  => NvidiaProviderSettings::KEY_DEFAULT_TEXT_GENERATION_MODEL,
				'image' => NvidiaProviderSettings::KEY_DEFAULT_IMAGE_GENERATION_MODEL,
			),
			'settings'    => NvidiaProviderSettings::getSettings(),
			'textModels'  => $modelChoices['text'],
			'imageModels' => $modelChoices['image'],
			'loadError'   => $modelChoices['loadError'],
			'labels'      => array(
				'heading'       => __( 'Default models', 'forgeia-ai-provider-for-nvidia' ),
				'description'   => __( 'Choose which NVIDIA models should be preferred when the AI Client auto-discovers a text or image generation model.', 'forgeia-ai-provider-for-nvidia' ),
				'textLabel'     => __( 'Text generation model', 'forgeia-ai-provider-for-nvidia' ),
				'imageLabel'    => __( 'Image generation model', 'forgeia-ai-provider-for-nvidia' ),
				'emptyOption'   => __( 'Use provider ordering', 'forgeia-ai-provider-for-nvidia' ),
				'saveButton'    => __( 'Save settings', 'forgeia-ai-provider-for-nvidia' ),
				'loadingFailed' => __( 'Model list could not be loaded. Saved values remain available and can still be saved.', 'forgeia-ai-provider-for-nvidia' ),
			),
		);
	}

	/**
	 * Returns model choices grouped by capability.
	 *
	 * @since 1.2.0
	 *
	 * @return array{text:list<ModelChoice>,image:list<ModelChoice>,loadError:string}
	 */
	private static function getModelChoices(): array {
		/** @var list<ModelChoice> $textModels */
		$textModels = array();
		/** @var list<ModelChoice> $imageModels */
		$imageModels = array();
		$loadError   = '';

		try {
			foreach ( NvidiaProvider::modelMetadataDirectory()->listModelMetadata() as $modelMetadata ) {
				if ( self::supportsTextGeneration( $modelMetadata ) ) {
					$textModels[] = self::modelChoiceFromMetadata( $modelMetadata );
				}
				if ( self::supportsImageGeneration( $modelMetadata ) ) {
					$imageModels[] = self::modelChoiceFromMetadata( $modelMetadata );
				}
			}
		} catch ( Throwable $throwable ) {
			$loadError = $throwable->getMessage();
		}

		$settings = NvidiaProviderSettings::getSettings();
		self::prependSavedChoice( $textModels, $settings[ NvidiaProviderSettings::KEY_DEFAULT_TEXT_GENERATION_MODEL ] );
		self::prependSavedChoice( $imageModels, $settings[ NvidiaProviderSettings::KEY_DEFAULT_IMAGE_GENERATION_MODEL ] );

		return array(
			'text'      => $textModels,
			'image'     => $imageModels,
			'loadError' => $loadError,
		);
	}

	/**
	 * Converts model metadata into a UI choice.
	 *
	 * @since 1.2.0
	 *
	 * @param ModelMetadata $modelMetadata Model metadata.
	 * @return ModelChoice
	 */
	private static function modelChoiceFromMetadata( ModelMetadata $modelMetadata ): array {
		return array(
			'id'   => $modelMetadata->getId(),
			'name' => $modelMetadata->getName(),
		);
	}

	/**
	 * Prepends a saved model ID when live model loading failed or the model is absent.
	 *
	 * @since 1.2.0
	 *
	 * @param list<ModelChoice> $choices Existing choices.
	 * @param string            $modelId Saved model ID.
	 * @return void
	 */
	private static function prependSavedChoice( array &$choices, string $modelId ): void {
		if ( '' === $modelId ) {
			return;
		}

		foreach ( $choices as $choice ) {
			if ( $choice['id'] === $modelId ) {
				return;
			}
		}

		array_unshift(
			$choices,
			array(
				'id'   => $modelId,
				'name' => $modelId,
			)
		);
	}

	/**
	 * Checks whether a model supports text generation.
	 *
	 * @since 1.2.0
	 *
	 * @param ModelMetadata $modelMetadata Model metadata.
	 * @return bool
	 */
	private static function supportsTextGeneration( ModelMetadata $modelMetadata ): bool {
		foreach ( $modelMetadata->getSupportedCapabilities() as $capability ) {
			if ( $capability->isTextGeneration() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether a model supports image generation.
	 *
	 * @since 1.2.0
	 *
	 * @param ModelMetadata $modelMetadata Model metadata.
	 * @return bool
	 */
	private static function supportsImageGeneration( ModelMetadata $modelMetadata ): bool {
		foreach ( $modelMetadata->getSupportedCapabilities() as $capability ) {
			if ( $capability->isImageGeneration() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns an asset version based on file modification time.
	 *
	 * @since 1.2.0
	 *
	 * @param string $path Absolute asset path.
	 * @return string
	 */
	private static function assetVersion( string $path ): string {
		if ( file_exists( $path ) ) {
			return (string) filemtime( $path );
		}

		return '1.2.0';
	}
}
