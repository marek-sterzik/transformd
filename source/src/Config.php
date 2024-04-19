<?php


namespace Sterzik\TransforMD;

use Michelf\MarkdownExtra;

class Config
{
    private $config;

    public function __construct(string $configFileDir)
    {
        $this->config = (new ConfigReader($configFileDir))->read();
    }

    public function getDirectoryIndex(): string
    {
        return $this->getValue("index-file", "index.md");
    }

    public function getHomeUrl(): ?string
    {
        return $this->getValue("home", null);
    }

    public function getHomeCaption(): ?string
    {
        return $this->getValue("home-caption", "home");
    }

    public function getTemplateVars(): array
    {
        $homeUrl = $this->getHomeUrl();
        $homeCaption = $this->getHomeCaption() ?? $homeUrl;
        return [
            "homeUrl" => $homeUrl,
            "homeCaption" => $homeCaption,
        ];
    }

    private function getValue(string $key, $default)
    {
        return array_key_exists($key, $this->config) ? $this->config[$key] : $default;
    }
}
