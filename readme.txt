=== Forgeia AI Provider for NVIDIA ===
Contributors: arjvand
Tags: ai, nvidia, nim, artificial-intelligence, connector
Requires at least: 6.9
Tested up to: 7.1
Stable tag: 1.4.0
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

NVIDIA NIM provider for the WordPress AI Client — text, image, vision, embeddings, and admin settings.

== Description ==

This plugin integrates NVIDIA's NIM (NVIDIA Inference Microservices) with the WordPress AI Client, enabling your site to use NVIDIA's hosted AI models for text generation, image generation, vision / multimodal tasks, and embedding generation (with AI Client 1.4+).

**Features:**

* Text generation with NVIDIA NIM models, including function/tool calling and JSON structured output
* Image generation with diffusion models such as DiffusionGemma
* Vision / multimodal model support — models that accept images alongside text prompts
* Embedding generation with AI Client 1.4+ — text embeddings for semantic search, retrieval, and clustering
* Admin settings page to choose default text and image generation models (**Settings > NVIDIA AI Connector**)
* Dynamic model discovery — available models are fetched from the NVIDIA NIM API and classified by capability
* Automatic provider registration with the AI Client

Models from NVIDIA, Meta, Google, Mistral AI, Microsoft, DeepSeek, Moonshot AI, and other providers are discovered and categorized automatically. Many models include free API endpoints for testing and development.

**Requirements:**

* PHP 7.4 or higher
* WordPress 6.9 or higher with the [wordpress/php-ai-client](https://github.com/wordpress/php-ai-client) plugin installed and activated, or WordPress 7.0 or higher, which bundles the AI Client SDK
* NVIDIA API key — set via the `NVIDIA_API_KEY` environment variable or PHP constant

**Embedding requirements:** Embedding generation requires AI Client 1.4 or higher. WordPress 7.0 and 7.1 bundle AI Client SDK 1.3.1, which does not include embedding support, so embedding models are unavailable on those versions.

== Supported Models ==

Models are dynamically discovered from the NVIDIA NIM API and classified by capability using explicit allowlists. The following categories are available:

* **Text Generation** — Chat and text completion models including NVIDIA Nemotron 3 and 3.5, Meta Llama, Google Gemma 3 and 4, Mistral Large 4, Moonshot Kimi K2.6, Muse Glimmer, Step 5, GLM 5.3, and others. Includes function/tool calling and JSON structured output support.
* **Vision / Multimodal** — Models accepting image inputs alongside text prompts, including NVIDIA Nemotron 3 and 3.5 vision-language models, Google Gemma 3 and 4, Moonshot Kimi K2.6, Muse Glimmer, Step 5, Mistral Large 4, GLM 5.3, NVIDIA NeVA, NVIDIA VILA, and others.
* **Image Generation** — Diffusion-based image generation models, including DiffusionGemma.
* **Embedding** — Embedding models implemented with AI Client 1.4+, including Nemotron 3.5 Embed, NVCLIP, NV-EmbedQA, Arctic Embed, and others. This version sends text inputs to embedding models. Embedding generation is unavailable on WordPress 7.0 and 7.1, which bundle AI Client 1.3.1.

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

On WordPress 6.9, the [wordpress/php-ai-client](https://github.com/wordpress/php-ai-client) plugin must be installed and activated. On WordPress 7.0 and higher, the AI Client SDK is bundled with WordPress, so no separate plugin is required. This plugin provides the NVIDIA-specific implementation that the PHP AI Client uses.

== Privacy ==

This plugin sends data to the NVIDIA NIM API (https://integrate.api.nvidia.com) when generating text, images, or embeddings. The data sent is limited to the user's prompt text, image data for multimodal requests, embedding input text, and API key, and is used solely to fulfill the generation request. No data is collected, stored, or transmitted to any other third party.

This plugin does not use cookies, tracking, or analytics. It stores no personal data on your server. In accordance with GDPR and UK GDPR requirements, no user consent is needed for privacy compliance — all data processing is limited to API request fulfillment with no storage or sharing of personal information.

== Changelog ==

= 1.4.0 =

* AI Client SDK compatibility: develop against AI Client ^1.5, support runtime 1.3.1+, and refactor the text generation model onto the SDK's OpenAI-compatible base class
* Refresh NVIDIA model catalog (2026-11-07): 84 live models, 12 added, 47 removed, 4 reclassified
* Add embedding generation support with AI Client 1.4+ (unavailable on WordPress 7.0/7.1, which bundle AI Client 1.3.1)
* Behavior changes: content_filter responses now map to the SDK content filter finish reason; multimodal models advertise text and text+image inputs only (no document modality); unrecognized-model logging is debug-only and fires the forgeia_nvidia_unrecognized_model action
* Change the default text model to nvidia/nemotron-3-super-120b-a12b, with an idempotent migration from the legacy nvidia/llama-3.3-nemotron-super-49b-v1

= 1.3.0 =

* Refresh NVIDIA model catalog (2026-07-29)
* Add new models: google/gemma-4-31b-it (multimodal), meta/llama-4-maverick-17b-128e-instruct (multimodal), moonshotai/kimi-k2.6 (multimodal), nvidia/ising-calibration-1.5-31b (multimodal), nvidia/riva-translate-4b-instruct-v2 (text), poolside/laguna-xs-2.1 (text), thinkingmachines/inkling (text), nvidia/nemotron-3-embed-1b (embedding)
* Remove deprecated/renamed models: google/gemma-3n-e2b-it, google/gemma-3n-e4b-it, mistralai/ministral-14b-instruct-2512, mistralai/mistral-large-3-675b-instruct-2512, mistralai/mistral-small-4-119b-2603, mistralai/mixtral-8x7b-instruct-v0.1, nvidia/ising-calibration-1-35b-a3b, qwen/qwen3.5-397b-a17b

= 1.2.0 =

* Add settings page with preferred default model selection (SelectControl → ComboboxControl)
* Add JSON structured output support (outputMimeType + outputSchema)
* Add settings page screenshot to readme and assets
* Add NVIDIA provider settings storage, admin page, and WPDS assets
* Redesign settings page with custom branded card layout
* Fix button text contrast; rename menu to NVIDIA AI Connector
* Update model allowlist: replace z-ai/glm-5.1 with z-ai/glm-5.2
* Mark 54 models with (Free Endpoint) in readme.txt
* Rewrite readme.txt to focus on plugin features

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
