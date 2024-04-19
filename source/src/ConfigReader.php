<?php

namespace Sterzik\TransforMD;

use Michelf\MarkdownExtra;
use Symfony\Component\Yaml\Yaml;
use Exception;

class ConfigReader
{
    const TRANSFORMD_CONFIG_NAMES = ["transformd.yaml", "transformd.yml", "transformd.json"];
    const CHECKERS = [
        "index-file" => "path|null",
        "home" => "path|null",
        "home-caption" => "string|null",
    ];


    private $cofnigFileDir;

    public function __construct(string $configFileDir)
    {
        $this->configFileDir = $configFileDir;
    }

    private function loadFromFiles(): array
    {
        foreach (self::TRANSFORMD_CONFIG_NAMES as $file) {
            $path = $this->configFileDir . "/" . $file;
            if (file_exists($path)) {
                if (strpos($file, ".") !== false) {
                    $ext = preg_replace('/^.*\./', '', $file);
                } else {
                    $ext = "";
                }
                $config = $this->loadFromFile($path, strtolower($ext));
                if ($config !== null) {
                    return $config;
                }
            }
        }
        return [];
    }

    private function loadFromFile(string $file, string $ext): ?array
    {
        switch ($ext) {
        case 'yaml':
        case 'yml':
            return $this->loadFromYaml($file);
        case 'json':
            return $this->loadFromJson($file);
        default:
            return null;
        }
    }

    private function loadFromYaml(string $file): ?array
    {
        if (!class_exists(Yaml::class)) {
            return null;
        }
        try {
            $data = Yaml::parseFile($file);
            if (!is_array($data)) {
                $data = [];
            }
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    private function loadFromJson(string $file): ?array
    {
        if (!function_exists("json_decode")) {
            return null;
        }
        $data = @file_get_contents($file);
        if (!is_string($data)) {
            return [];
        }
        $data = json_decode($data, true);
        if (!is_array($data)) {
            $data  = [];
        }
        return $data;
    }

    public function read(): array
    {
        $config = $this->loadFromFiles();
        $this->doCheckConfig($config);
        return $config;
    }

    private function doCheckConfig(array &$config): void
    {
        foreach (array_keys($config) as $key) {
            $value = $config[$key];
            $checker = self::CHECKERS[$key] ?? '';
            if ($this->doCheckValue($value, $checker)) {
                $config[$key] = $value;
            } else {
                unset($config[$key]);
            }
        }
    }

    private function doCheckValue(&$value, string $checkers): bool
    {
        if ($checkers === "") {
            $chekcers = [];
        } else {
            $checkers = explode("|", $checkers);
        }
        foreach ($checkers as $checker) {
            $method = "check" . ucfirst($checker);
            $val2 = $value;
            if (method_exists($this, $method) && $this->$method($val2)) {
                $value = $val2;
                return true;
            }
        }
        return false;
    }

    private function checkNull(&$value): bool
    {
        return is_null($value);
    }

    private function checkString(&$value): bool
    {
        return is_string($value);
    }

    private function checkPath(&$value): bool
    {
        return is_string($value);
    }
}
