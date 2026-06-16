=== Forgeia AI Provider for NVIDIA ===
Contributors: arjvand
Tags: ai, nvidia, nim, artificial-intelligence, connector
Requires at least: 6.9
Tested up to: 7.0
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Forgeia AI Provider for NVIDIA for the PHP AI Client SDK.

== Description ==

This plugin provides Forgeia NVIDIA NIM integration for the PHP AI Client SDK. It enables WordPress sites to use NVIDIA's inference microservices for text generation and image generation.

**Features:**

* Text generation with NVIDIA NIM models
* Image generation with Stable Diffusion and Flux models
* Function calling support
* Automatic provider registration

Available models are dynamically discovered from the NVIDIA NIM API, including models from NVIDIA, Meta, Google, Mistral, and other providers.

**Requirements:**

* PHP 7.4 or higher
* For WordPress 6.9, the [wordpress/php-ai-client](https://github.com/wordpress/php-ai-client) package must be installed
* For WordPress 7.0 and above, no additional changes are required
* NVIDIA API key

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/forgeia-ai-provider-for-nvidia/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure your NVIDIA API key via the `NVIDIA_API_KEY` environment variable or constant

== Frequently Asked Questions ==

= How do I get an NVIDIA API key? =

Visit the [NVIDIA Build](https://build.nvidia.com/) to create an account and generate an API key.

= Does this plugin work without the PHP AI Client? =

No, this plugin requires the PHP AI Client plugin to be installed and activated. It provides the NVIDIA-specific implementation that the PHP AI Client uses.

== Privacy ==

This plugin sends data to the NVIDIA NIM API (https://integrate.api.nvidia.com) when generating text or images. The data sent includes the user's prompt text and API key. No data is collected, stored, or transmitted to any other third party. The NVIDIA API is used solely to fulfill the generation request.

This plugin does not use cookies, tracking, or analytics.

== Changelog ==

= 1.0.0 =

* Initial release of the plugin
* Support for NVIDIA NIM text generation models
* Support for image generation models (Stable Diffusion, Flux)
* Function calling support
