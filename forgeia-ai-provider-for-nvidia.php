<?php

/**
 * Plugin Name: Forgeia AI Provider for NVIDIA
 * Plugin URI: https://github.com/arjvand/forgeia-ai-provider-for-nvidia
 * Description: Forgeia AI Provider for NVIDIA for the WordPress AI Client.
 * Requires at least: 6.9
 * Requires PHP: 7.4
 * Version: 1.2.0
 * Author: Alireza Arjvand
 * Author URI: https://github.com/arjvand
 * License: GPL-2.0-or-later
 * License URI: https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain: forgeia-ai-provider-for-nvidia
 *
 * @package Forgeia\NvidiaAiProvider
 */

declare(strict_types=1);

namespace Forgeia\NvidiaAiProvider;

use Forgeia\NvidiaAiProvider\Admin\NvidiaSettingsPage;
use Forgeia\NvidiaAiProvider\Provider\NvidiaProvider;
use Forgeia\NvidiaAiProvider\Settings\NvidiaProviderSettings;
use WordPress\AiClient\AiClient;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

require_once __DIR__ . '/src/autoload.php';

/**
 * Registers the Forgeia AI Provider for NVIDIA with the AI Client.
 *
 * @since 1.0.0
 *
 * @return void
 */
function register_provider(): void {
	if ( ! class_exists( AiClient::class ) ) {
		return;
	}

	$registry = AiClient::defaultRegistry();

	if ( $registry->hasProvider( NvidiaProvider::class ) ) {
		return;
	}

	$registry->registerProvider( NvidiaProvider::class );
}

/**
 * Registers admin-only settings hooks.
 *
 * @since 1.2.0
 *
 * @return void
 */
function register_admin_hooks(): void {
	if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
		return;
	}

	add_action( 'admin_init', array( NvidiaProviderSettings::class, 'register' ) );
	add_action( 'admin_menu', array( NvidiaSettingsPage::class, 'register' ) );
	add_action( 'admin_enqueue_scripts', array( NvidiaSettingsPage::class, 'enqueueAssets' ) );
}

add_action( 'init', __NAMESPACE__ . '\\register_provider', 5 );
add_action( 'init', __NAMESPACE__ . '\\register_admin_hooks', 6 );
