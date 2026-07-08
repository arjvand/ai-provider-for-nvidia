=== Forgeia AI Provider for NVIDIA ===
Contributors: arjvand
Tags: ai, nvidia, nim, artificial-intelligence, connector
Requires at least: 6.9
Tested up to: 7.0
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

NVIDIA NIM provider for the WordPress AI Client — text, image, vision, and admin settings.

== Description ==

This plugin integrates NVIDIA's NIM (NVIDIA Inference Microservices) with the WordPress AI Client, enabling your site to use NVIDIA's hosted AI models for text generation, image generation, and vision / multimodal tasks.

**Features:**

* Text generation with NVIDIA NIM models, including function/tool calling and JSON structured output
* Image generation with diffusion models (Stable Diffusion, Flux, and others)
* Vision / multimodal model support — models that accept images and documents alongside text prompts
* Admin settings page to choose default text and image generation models (**Settings > NVIDIA AI Connector**)
* Dynamic model discovery — available models are fetched from the NVIDIA NIM API and classified by capability
* Automatic provider registration with the AI Client

Models from NVIDIA, Meta, Google, Mistral AI, Microsoft, DeepSeek, Qwen, and other providers are discovered and categorized automatically. Many models include free API endpoints for testing and development.

**Requirements:**

* PHP 7.4 or higher
* WordPress 7.0+ (bundles the required AI Client SDK) or WordPress 6.9 with the [wordpress/php-ai-client](https://github.com/wordpress/php-ai-client) plugin installed separately
* NVIDIA API key — set via the `NVIDIA_API_KEY` environment variable or PHP constant

== Supported Models ==

Models are dynamically discovered from the NVIDIA NIM API and classified by capability using explicit allowlists. The following categories are available:

* **Text Generation** — Chat and text completion models from NVIDIA, Meta, Google, Mistral AI, Microsoft, DeepSeek, Qwen, and others. Includes function/tool calling and JSON structured output support.
* **Vision / Multimodal** — Models accepting image and document inputs alongside text prompts, including Meta Llama Vision, Microsoft Phi-4 Multimodal, NVIDIA NeVA, NVIDIA VILA, and others.
* **Image Generation** — Diffusion-based image generation models.
* **Embedding** — Text embedding models for vector representations (recognized by the provider; usable through SDK embedding workflows).

For the current full model catalog, visit the [NVIDIA NIM API](https://integrate.api.nvidia.com/v1/models) directly. Free API endpoints are available for many models.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/forgeia-ai-provider-for-nvidia/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Set your NVIDIA API key via the `NVIDIA_API_KEY` environment variable or PHP constant
4. Optional: Visit **Settings > NVIDIA AI Connector** to choose default text and image generation models

== Screenshots ==

1. The NVIDIA AI Connector settings page for choosing default text and image generation models.

== Frequently Asked Questions ==

= How do I set default text or image generation models? =

After activation, visit **Settings > NVIDIA AI Connector** to choose your preferred default models from the dynamically fetched model list. These defaults are used when no specific model is requested.

= How do I get an NVIDIA API key? =

Visit the [NVIDIA Build](https://build.nvidia.com/) to create an account and generate an API key.

= Does this plugin work without the PHP AI Client? =

No, this plugin requires the PHP AI Client plugin to be installed and activated. It provides the NVIDIA-specific implementation that the PHP AI Client uses.

== Privacy ==

This plugin sends data to the NVIDIA NIM API (https://integrate.api.nvidia.com) when generating text or images. The data sent is limited to the user's prompt text and API key and is used solely to fulfill the generation request. No data is collected, stored, or transmitted to any other third party.

This plugin does not use cookies, tracking, or analytics. It stores no personal data on your server. In accordance with GDPR and UK GDPR requirements, no user consent is needed for privacy compliance — all data processing is limited to API request fulfillment with no storage or sharing of personal information.

== Changelog ==

= 1.1.0 =

* Replace pattern-based model classification with explicit model allowlists for accuracy
* Add comprehensive supported models list to readme.txt (including embedding models)
* Add WordPress.org plugin directory assets (icons)
* Fix SVN deployment slug

= 1.0.1 =

* Rebrand to Forgeia AI Provider for NVIDIA
* Main plugin file renamed to match folder slug
* Add PHP coding standards (PHPCS + PHPStan level max)
* Add direct file access protection to autoload.php
* Replace placeholder values in LICENSE
* Add privacy disclosure for WordPress.org submission
* Update contributors in readme.txt
* Fix GitHub URLs from arjvand/php-ai-client to wordpress/php-ai-client

= 1.0.0 =

* Initial release of the plugin
* Support for NVIDIA NIM text generation models
* Support for image generation models (Stable Diffusion, Flux)
* Function calling support
