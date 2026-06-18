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

This plugin provides NVIDIA NIM integration for the PHP AI Client SDK. It enables WordPress sites to use NVIDIA's inference microservices for text generation, image generation, and embedding generation.

**Features:**

* Text generation with NVIDIA NIM models
* Image generation with Stable Diffusion and Flux models
* Embedding generation with NVIDIA NIM models
* Function calling support
* Automatic provider registration

Available models are dynamically discovered from the NVIDIA NIM API and explicitly categorized by capability (text, image, multimodal, embedding), covering models from NVIDIA, Meta, Google, Mistral, and other providers.

**Requirements:**

* PHP 7.4 or higher
* For WordPress 6.9, the [wordpress/php-ai-client](https://github.com/wordpress/php-ai-client) package must be installed
* For WordPress 7.0 and above, no additional changes are required
* NVIDIA API key

== Supported Models ==

Models are discovered from the NVIDIA NIM API and explicitly categorized by capability.

= Image Generation =

* `google/diffusiongemma-26b-a4b-it`

= Multimodal Text (image/document input) =

* `adept/fuyu-8b`
* `google/deplot`
* `meta/llama-3.2-11b-vision-instruct`
* `meta/llama-3.2-90b-vision-instruct`
* `microsoft/kosmos-2`
* `microsoft/phi-3-vision-128k-instruct`
* `microsoft/phi-4-multimodal-instruct`
* `nvidia/cosmos-reason2-8b`
* `nvidia/llama-3.1-nemotron-nano-vl-8b-v1`
* `nvidia/nemotron-nano-12b-v2-vl`
* `nvidia/neva-22b`
* `nvidia/nvclip`
* `nvidia/vila`

= Text Generation =

* `01-ai/yi-large`
* `abacusai/dracarys-llama-3.1-70b-instruct`
* `ai21labs/jamba-1.5-large-instruct`
* `aisingapore/sea-lion-7b-instruct`
* `bigcode/starcoder2-15b`
* `bytedance/seed-oss-36b-instruct`
* `databricks/dbrx-instruct`
* `deepseek-ai/deepseek-coder-6.7b-instruct`
* `deepseek-ai/deepseek-v4-flash`
* `deepseek-ai/deepseek-v4-pro`
* `google/codegemma-1.1-7b`
* `google/codegemma-7b`
* `google/gemma-2-2b-it`
* `google/gemma-2b`
* `google/gemma-3-12b-it`
* `google/gemma-3-4b-it`
* `google/gemma-3n-e2b-it`
* `google/gemma-3n-e4b-it`
* `google/gemma-4-31b-it`
* `google/recurrentgemma-2b`
* `ibm/granite-3.0-3b-a800m-instruct`
* `ibm/granite-3.0-8b-instruct`
* `ibm/granite-34b-code-instruct`
* `ibm/granite-8b-code-instruct`
* `meta/codellama-70b`
* `meta/llama-3.1-70b-instruct`
* `meta/llama-3.1-8b-instruct`
* `meta/llama-3.2-1b-instruct`
* `meta/llama-3.2-3b-instruct`
* `meta/llama-3.3-70b-instruct`
* `meta/llama-4-maverick-17b-128e-instruct`
* `meta/llama2-70b`
* `microsoft/phi-3.5-moe-instruct`
* `microsoft/phi-4-mini-instruct`
* `minimaxai/minimax-m2.7`
* `minimaxai/minimax-m3`
* `mistralai/codestral-22b-instruct-v0.1`
* `mistralai/ministral-14b-instruct-2512`
* `mistralai/mistral-7b-instruct-v0.3`
* `mistralai/mistral-large`
* `mistralai/mistral-large-2-instruct`
* `mistralai/mistral-large-3-675b-instruct-2512`
* `mistralai/mistral-medium-3.5-128b`
* `mistralai/mistral-nemotron`
* `mistralai/mistral-small-4-119b-2603`
* `mistralai/mixtral-8x22b-v0.1`
* `mistralai/mixtral-8x7b-instruct-v0.1`
* `moonshotai/kimi-k2.6`
* `nv-mistralai/mistral-nemo-12b-instruct`
* `nvidia/ising-calibration-1-35b-a3b`
* `nvidia/llama-3.1-nemotron-51b-instruct`
* `nvidia/llama-3.1-nemotron-70b-instruct`
* `nvidia/llama-3.1-nemotron-nano-8b-v1`
* `nvidia/llama-3.1-nemotron-ultra-253b-v1`
* `nvidia/llama-3.3-nemotron-super-49b-v1`
* `nvidia/llama-3.3-nemotron-super-49b-v1.5`
* `nvidia/llama3-chatqa-1.5-70b`
* `nvidia/mistral-nemo-minitron-8b-8k-instruct`
* `nvidia/nemotron-3-nano-30b-a3b`
* `nvidia/nemotron-3-nano-omni-30b-a3b-reasoning`
* `nvidia/nemotron-3-super-120b-a12b`
* `nvidia/nemotron-3-ultra-550b-a55b`
* `nvidia/nemotron-4-340b-instruct`
* `nvidia/nemotron-mini-4b-instruct`
* `nvidia/nemotron-nano-3-30b-a3b`
* `nvidia/nvidia-nemotron-nano-9b-v2`
* `nvidia/riva-translate-4b-instruct`
* `nvidia/riva-translate-4b-instruct-v1.1`
* `openai/gpt-oss-120b`
* `openai/gpt-oss-20b`
* `qwen/qwen3-next-80b-a3b-instruct`
* `qwen/qwen3.5-122b-a10b`
* `qwen/qwen3.5-397b-a17b`
* `sarvamai/sarvam-m`
* `stepfun-ai/step-3.5-flash`
* `stepfun-ai/step-3.7-flash`
* `stockmark/stockmark-2-100b-instruct`
* `upstage/solar-10.7b-instruct`
* `writer/palmyra-creative-122b`
* `writer/palmyra-fin-70b-32k`
* `writer/palmyra-med-70b`
* `writer/palmyra-med-70b-32k`
* `z-ai/glm-5.1`
* `zyphra/zamba2-7b-instruct`

= Embedding =

* `baai/bge-m3`
* `nvidia/embed-qa-4`
* `nvidia/llama-3.2-nemoretriever-1b-vlm-embed-v1`
* `nvidia/llama-3.2-nv-embedqa-1b-v1`
* `nvidia/llama-nemotron-embed-1b-v2`
* `nvidia/llama-nemotron-embed-vl-1b-v2`
* `nvidia/nv-embed-v1`
* `nvidia/nv-embedcode-7b-v1`
* `nvidia/nv-embedqa-e5-v5`
* `nvidia/nv-embedqa-mistral-7b-v2`
* `snowflake/arctic-embed-l`

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
