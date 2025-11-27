<?php

namespace Charcoal\Admin\Template;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;

/**
 * Admin System Info template
 */
class SystemInfoTemplate extends AdminTemplate
{
    protected function authRequired()
    {
        return true;
    }

    public function systemInfo()
    {
        $extensions = [
            'mbstring'  => extension_loaded('mbstring'),
            'openssl'   => extension_loaded('openssl'),
            'gd'        => extension_loaded('gd'),
            'imagick'   => extension_loaded('imagick'),
            'curl'      => extension_loaded('curl'),
            'fileinfo'  => extension_loaded('fileinfo'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml'       => extension_loaded('xml'),
            'zip'       => extension_loaded('zip'),
            'json'      => extension_loaded('json'),
            'pdo'       => extension_loaded('pdo'),
        ];

        $pdoDrivers = [];
        if ($extensions['pdo']) {
            try {
                $pdoDrivers = \PDO::getAvailableDrivers();
            } catch (\Throwable $e) {
                $pdoDrivers = ['error' => $e->getMessage()];
            }
        }
        $extensions['pdo_drivers'] = $pdoDrivers;

        $data = [
            'php_version'         => PHP_VERSION,
            'php_sapi'            => php_sapi_name(),
            'os'                  => php_uname(),
            'server_software'     => $_SERVER['SERVER_SOFTWARE'] ?? null,
            'memory_limit'        => ini_get('memory_limit'),
            'max_execution_time'  => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size'       => ini_get('post_max_size'),
            'timezone'            => date_default_timezone_get(),
            'project_dir'         => getcwd(),
            'locale'              => explode(';', setlocale(LC_ALL, 0)),
            'extensions'          => $extensions,
        ];

        $systemInfo = [];

        foreach ($data as $key => $value) {
            $systemInfo[] = [
                'label' => $key,
                'value' => is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value,
            ];
        }

        return $systemInfo;
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('System Information'));
        }

        return $this->title;
    }
}
