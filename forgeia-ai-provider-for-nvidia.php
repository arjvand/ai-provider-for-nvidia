<?php

/**
 * Plugin Name: Forgeia AI Provider for NVIDIA
 * Plugin URI: https://github.com/arjvand/forgeia-ai-provider-for-nvidia
 * Description: Forgeia AI Provider for NVIDIA for the WordPress AI Client.
 * Requires at least: 6.9
 * Requires PHP: 7.4
 * Version: 1.0.1
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

use Forgeia\NvidiaAiProvider\Provider\NvidiaProvider;
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

add_action( 'init', __NAMESPACE__ . '\\register_provider', 5 );
