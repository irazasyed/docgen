<?php

namespace Docgen;

use RuntimeException;

class PackageManifest
{
    private string $basePath;
    private ?array $composer = null;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath ?? getcwd();
    }

    public function composer(): array
    {
        if($this->composer === null) {
            $this->composer = $this->resolveComposerJson();
        }

        return $this->composer;
    }

    public function config(string $key, $default = null): mixed
    {
        return data_get($this->composer(), $key, $default);
    }

    public function aliases(): ?string
    {
        return $this->config('extra.laravel.aliases');
    }

    public function providers(): ?string
    {
        return $this->config('extra.laravel.providers');
    }

    private function resolveComposerJson(): array
    {
        $composer = realpath($this->basePath . '/composer.json');
        if(!$composer) {
            throw new RuntimeException(sprintf('File "%s" does not exist.', $composer));
        }

        return json_decode(file_get_contents($composer), true, 512, JSON_THROW_ON_ERROR);
    }
}
