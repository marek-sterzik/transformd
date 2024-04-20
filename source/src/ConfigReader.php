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
        "assets" => "filelist",
        "templates" => "filelist",
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
            $checkers = [];
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

    private function checkFilelist(&$value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        foreach (array_keys($value) as $key) {
            if (!$this->checkFileContent($value[$key])) {
                unset($value[$key]);
            }
        }
        return true;
    }

    private function checkFileContent(&$value): bool
    {
        if (is_string($value)) {
            $val2 = ltrim($value);
            if (strlen($val2) === strlen("null") && strtolower($val2) === "null") {
                $value = null;
                unset($val2);
            }
        }
        if ($value === null) {
            $value = ["type" => "null", "data" => null];
        }
        if (is_string($value)) {
            $pos = strpos($value, ":");
            $type = null;
            if ($pos !== null) {
                $type = strtolower(ltrim(substr($value, 0, $pos)));
                if (!preg_match('/^[a-z_]+$/', $type)) {
                    $type = null;
                }
            }
            if ($type === null) {
                $data = $value;
            } else {
                $data = substr($value, $pos + 1);
            }
            $value = ["type" => $type, "data" => $data];
        }

        if (!is_array($value)) {
            return false;
        }
        if (!array_key_exists('type', $value) || !is_string($value['type'])) {
            return false;
        }
        $value['type'] = strtolower($value['type']);

        if ($value['type'] !== 'null' && !array_key_exists('data', $value)) {
            return false;
        }
        if (!array_key_exists('data', $value)) {
            $value['data'] = null;
        }
        if (!is_string($value['data']) && $value['data'] !== null) {
            return false;
        }
        if ($value['data'] === null && $value['type'] !== 'null') {
            return false;
        }
        if (count($value) > 2) {
            $value = ["type" => $value["type"], "data" => $value["data"]];
        }
        if ($value['type'] === 'data' || $value['type'] === 'd') {
            $value['type'] = 'data';
        } elseif($value['type'] === 'file' || $value['type'] === 'f') {
            $value['type'] = 'file';
            $value['data'] = $this->resolveRelativePath($value['data']);
            if ($value['data'] === null) {
                return false;
            }
            if (!file_exists($value['data'])) {
                return false;
            }
        } elseif($value['type'] === 'null') {
            return true;
        } else {
            return false;
        }
        return true;
    }

    private function resolveRelativePath(string $path): string
    {
        //TODO implement relative path resolving
        return $path;
    }
}
