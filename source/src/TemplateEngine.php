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
        $fileDescriptor = $this->config->getFile("templates", $templateName);
        if ($fileDescriptor === null) {
            $template = $this->defaultTemplateDir . "/" . $templateName . ".php";
            $isFile = true;
        } elseif ($fileDescriptor['type'] === 'file') {
            $template = $fileDescriptor['data'];
            $isFile = true;
        } elseif ($fileDescriptor['type'] === 'data') {
            $template = $fileDescriptor['data'];
            $isFile = false;
        } else {
            return;
        }
        
        $this->renderTemplateRaw($template, $isFile, $vars);
    }

    private function renderTemplateRaw(string $template, bool $isFile, array $vars): void
    {
        $vars = array_merge($this->getCommonTemplateArgs($vars), $vars);
        $__tpl_args__ = [$template, $isFile, $vars];
        unset($template);
        unset($isFile);
        unset($vars);
        foreach ($__tpl_args__[2] as $var => $value) {
            if ($var !== 'var' && $var !== 'value' && $var !== '__tpl_args__') {
                $$var = $value;
            }
        }
        unset($var);
        unset($value);
        if (array_key_exists("var", $__tpl_args__[2])) {
            $var = $__tpl_args__[2]["var"];
        }
        if (array_key_exists("value", $__tpl_args__[2])) {
            $var = $__tpl_args__[2]["value"];
        }
        if ($__tpl_args__[1]) {
            @include $__tpl_args__[0];
        } else {
            eval("?>" . $__tpl_args__[0]);
        }
    }

    private function assetExists(string $assetFile): bool
    {
        $fileDescriptor = $this->config->getFile("assets", $assetFile);
        if ($fileDescriptor === null) {
            return file_exists($this->assetDir . "/" . $assetFile);
        } elseif ($fileDescriptor['type'] === 'file') {
            return file_exists($fileDescriptor['data']);
        } elseif ($fileDescriptor['type'] === 'data') {
            return true;
        } else {
            return false;
        }
    }

    private function asset(string $assetFile, string $mime): string
    {
        $fileDescriptor = $this->config->getFile("assets", $assetFile);
        if ($fileDescriptor === null) {
            $content = file_get_contents($this->assetDir . "/" . $assetFile);
        } elseif ($fileDescriptor['type'] === 'file') {
            $content = @file_get_contents($fileDescriptor['data']);
        } elseif ($fileDescriptor['type'] === 'data') {
            $content = $fileDescriptor['data'];
        } else {
            $content = '';
        }
        return "data:" . $mime . ";base64," . base64_encode($content);
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

        $assetExists = function(string $file) {
            return $this->assetExists($file);
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
            "assetExists" => $assetExists,
            "render" => $render,
        ]);
    }
}
