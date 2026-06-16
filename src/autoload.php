<?php

/**
 * PSR-4 autoloader for the Forgeia AI Provider for NVIDIA package.
 *
 * @since 1.0.0
 *
 * @package Forgeia\NvidiaAiProvider
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

spl_autoload_register(
	static function ( string $class ): void {
		$prefix  = 'Forgeia\\NvidiaAiProvider\\';
		$baseDir = __DIR__ . '/';

		$len = strlen( $prefix );

		if ( strncmp( $class, $prefix, $len ) !== 0 ) {
			return;
		}

		$relativeClass = substr( $class, $len );
		$file          = $baseDir . str_replace( '\\', '/', $relativeClass ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);
