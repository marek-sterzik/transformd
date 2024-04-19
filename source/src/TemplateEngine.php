<?php

namespace Sterzik\TransforMD;

class TemplateEngine
{
    const EXTENSIONS_MIME = [
        "css" => "text/css",
        "js" => "text/javascript",
        "html" => "text/html",
        "txt" => "text/plain",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png" => "image/png",
        "gif" => "image/gif",
    ];

    private $config;
    private $defaultTemplateDir;
    private $assetDir;

    public function __construct(Config $config, string $defaultTemplateDir, string $assetDir)
    {
        $this->config = $config;
        $this->defaultTemplateDir = $defaultTemplateDir;
        $this->assetDir = $assetDir;
    }

    public function fetch(string $templateName, array $vars = []): string
    {
        ob_start();
        try {
            $this->render($templateName, $vars);
        } finally {
            $data = ob_get_clean();
        }
        return $data;
    }

    public function render(string $templateName, array $vars = []): void
    {
        $this->renderTemplateRaw($templateName, $vars);
    }

    private function renderTemplateRaw(string $templateName, array $vars): void
    {
        $vars = array_merge($this->getCommonTemplateArgs($vars), $vars);
        $__tpl_args__ = [$templateName, $vars];
        foreach ($__tpl_args__[1] as $var => $value) {
            if ($var !== 'var' && $var !== 'value' && $var !== '__tpl_args__') {
                $$var = $value;
            }
        }
        unset($var);
        unset($value);
        if (array_key_exists("var", $__tpl_args__[1])) {
            $var = $__tpl_args__[1]["var"];
        }
        if (array_key_exists("value", $__tpl_args__[1])) {
            $var = $__tpl_args__[1]["value"];
        }
        $__tpl_args__ = $__tpl_args__[0];
        @include $this->defaultTemplateDir . "/" . $__tpl_args__ . ".php";
    }

    private function asset(string $assetFile, string $mime): string
    {
        return "data:" . $mime . ";base64," . base64_encode(file_get_contents($this->assetDir . "/" . $assetFile));
    }

    private function getCommonTemplateArgs(array $realVars): array
    {
        $asset = function(string $file) {
            $basename = basename($file);
            if (strpos($basename, ".") !== false) {
                $ext = strtolower(preg_replace('/^.*\./', '', $basename));
                $mime = self::EXTENSIONS_MIME[$ext] ?? null;
            } else {
                $mime = null;
            }
            if ($mime === null) {
                $mime = "text/plain";
            }
            return $this->asset($file, $mime);
        };

        $render = function(string $template, ?array $vars = null) use ($realVars) {
            if ($vars === null) {
                $vars = $realVars;
            }
            $this->render($template, $vars);
            return "";
        };
        
        return array_merge($this->config->getTemplateVars(), [
            "asset" => $asset,
            "render" => $render,
        ]);
    }
}
