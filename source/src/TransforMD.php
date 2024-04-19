<?php

namespace Sterzik\TransforMD;

use Michelf\MarkdownExtra;
use Throwable;

class TransforMD
{
    private $templateEngine;
    private $config;
    private $fileBase;

    public static function run(): void
    {
        $instance = new self(dirname(__DIR__));
        $instance->process();
    }

    public function __construct($rootDir)
    {
        if (!defined("TRANSFORMD_PHAR_DIR")) {
            $this->printError(500);
            exit(1);
        }

        $this->fileBase = TRANSFORMD_PHAR_DIR;
        $this->config = new Config($this->fileBase);
        $this->templateEngine = new TemplateEngine(
            $this->config,
            $rootDir . "/templates",
            $rootDir . "/style"
        );
    }

    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $pos = strpos($uri, "?");
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }
        return $uri;
    }

    private function getRelPath(string $base, string $file): string
    {
        $bl = strlen($base);
        if (strlen($file) < $bl) {
            return null;
        }

        if (substr($file, 0, $bl) != $base) {
            return null;
        }

        return substr($file, $bl);
    }

    public function process(): void
    {
        $uri = $this->getUri();
        $prefix = dirname($_SERVER['SCRIPT_NAME']);
        $relFilename = $this->getRelPath($prefix, $uri);
        $filename = $this->fileBase . $relFilename;

        if (is_dir($filename)) {
            $filename = $filename . "/" . $this->config->getDirectoryIndex();
        }

        if (!is_file($filename)) {
            $this->printError(404);
            return;
        }

        if (isset($_GET['source']) && $_GET['source']) {
            header("Content-Type: text/markdown; charset=utf-8");
            readfile($filename);
        } else {
            header("Content-Type: text/html; charset=utf-8");
            $html = MarkdownExtra::defaultTransform(file_get_contents($filename));
            $this->templateEngine->render("main", ["html" => $html]);
        }
    }

    private function printError(int $code)
    {
        http_response_code($code);
        $this->templateEngine->render("error", ["code" => $code]);
    }
}
