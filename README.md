# Forgeia AI Provider for NVIDIA

Forgeia AI Provider for NVIDIA for the [PHP AI Client](https://github.com/wordpress/php-ai-client) SDK. Works as both a Composer package and a WordPress plugin.

## Requirements

- PHP 7.4 or higher
- When using with WordPress, requires WordPress 7.0 or higher
    - If using an older WordPress release, the [wordpress/php-ai-client](https://github.com/wordpress/php-ai-client) package must be installed

## Installation

### As a Composer Package

```
composer require forgeia/forgeia-ai-provider-for-nvidia
```

### As a WordPress Plugin

1. Download the plugin files
2. Upload to `/wp-content/plugins/forgeia-ai-provider-for-nvidia/`
3. Ensure the PHP AI Client plugin is installed and activated
4. Activate the plugin through the WordPress admin

## Usage

### With WordPress

The provider automatically registers itself with the PHP AI Client on the `init` hook. Simply ensure both plugins are active and configure your API key:

```php
// Set your NVIDIA API key (or use the NVIDIA_API_KEY environment variable)
putenv('NVIDIA_API_KEY=your-api-key');

// Use the provider
$result = AiClient::prompt('Hello, world!')
    ->usingProvider('nvidia')
    ->generateTextResult();
```

### As a Standalone Package

```php
use WordPress\AiClient\AiClient;
use Forgeia\NvidiaAiProvider\Provider\NvidiaProvider;

// Register the provider
$registry = AiClient::defaultRegistry();
$registry->registerProvider(NvidiaProvider::class);

// Set your API key
putenv('NVIDIA_API_KEY=your-api-key');

// Generate text
$result = AiClient::prompt('Explain quantum computing')
    ->usingProvider('nvidia')
    ->generateTextResult();

echo $result->toText();
```

## Supported Models

Available models are dynamically discovered from the NVIDIA NIM API. This includes text generation models (NVIDIA Nemotron, Meta Llama, Google Gemma, Mistral, and others), and image generation models (Stable Diffusion, Flux). See the [NVIDIA Build](https://build.nvidia.com/) for the full list of available models.

## Configuration

The provider uses the `NVIDIA_API_KEY` environment variable for authentication. You can set this in your environment or via PHP:

```php
putenv('NVIDIA_API_KEY=your-api-key');
```

## License

GPL-2.0-or-later
