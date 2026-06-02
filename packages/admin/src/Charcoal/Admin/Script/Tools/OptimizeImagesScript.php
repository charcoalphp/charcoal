<?php

namespace Charcoal\Admin\Script\Tools;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Pimple\Container;
use Charcoal\Admin\AdminScript;

/**
 *
 */
class OptimizeImagesScript extends AdminScript
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var integer
     */
    private $jpgLevel;

    /**
     * @var integer
     */
    private $pngLevel;

    /**
     * @var string
     */
    private $dir;

    #[\Override]
    public function defaultArguments(): array
    {
        $arguments = [
            'jpg' => [
                'longPrefix' => 'jpg',
                'description' => 'Jpeg compression quality (0-100).',
                'defaultValue' => 85
            ],
            'png' => [
                'longPrefix' => 'png',
                'description'   => 'PNG optimization level (0-7). This gets slower as this number increases.',
                'defaultValue'  => 0
            ],
            'dir' => [
                'longPrefix' => 'dir',
                'description' => 'Directory (relative) to process.',
                'defaultValue' => 'www/uploads/'
            ]
        ];
        return array_merge(parent::defaultArguments(), $arguments);
    }

    /**
     * @param RequestInterface  $request  PSR-7 request.
     * @param ResponseInterface $response PSR-7 response.
     */
    public function run(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        unset($request);

        $climate = $this->climate();
        $climate->arguments->parse();

        $this->setVerbose($climate->arguments->get('verbose'));
        $this->jpgLevel = $climate->arguments->get('jpg');
        $this->pngLevel = $climate->arguments->get('png');
        $dir = $climate->arguments->get('dir');
        $this->dir = realpath($this->basePath . DIRECTORY_SEPARATOR . $dir);
        if (!$this->dir) {
            $climate->error('Invalid directory');
            return $response;
        }

        $climate->underline()->out(
            sprintf('Optimize images ("%s")', $dir)
        );


        if ($this->jpgLevel > 0) {
            if ($this->jpgLevel < 60) {
                $climate->error(sprintf(
                    'ERROR: A quality of %s is VERY low. This will most likely make the resulting images very pixelated. Aborted.',
                    $this->jpgLevel
                ));
                return $response;
            }
            $jpegoptimCmd = $this->getJpegoptimCmd();
            if (!$jpegoptimCmd) {
                $climate->error('Could not find jpegoptim on this system. Aborted.');
                return $response;
            }
            $this->runJpegoptim($jpegoptimCmd);
        }

        if ($this->pngLevel > 0) {
            $optipngCmd = $this->getOptipngCmd();
            if (!$optipngCmd) {
                $climate->error('Could not find optipng on this system. Aborted.');
            }
            $this->runOptipng($optipngCmd);
        }

        return $response;
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath = $container['config']['basePath'];
    }

    /**
     * @return string
     */
    private function getJpegoptimCmd()
    {
        return $this->findCmd('jpegoptim');
    }

    /**
     * @return string
     */
    private function getOptipngCmd()
    {
        return $this->findCmd('optipng');
    }

    /**
     * @param string $cmd The jpegoptim command.
     */
    private function runJpegoptim($cmd): void
    {
        $cmdName = sprintf(
            'cd %s && \
            find -type f \( \
                -name "*.jpg" -o \
                -name "*.JPG" -o \
                -name "*.jpeg" -o \
                -name "*.JPEG" \
            \) -exec %s --strip-all --max=%s {} \;; \
            find -type f \( \
                -name "*.jpg" -o \
                -name "*.JPG" -o \
                -name "*.jpeg" -o \
                -name "*.JPEG" \
            \) -exec chmod -R a+r {} \;',
            $this->dir,
            $cmd,
            $this->jpgLevel
        );
        if ($this->verbose()) {
            $this->climate()->out($cmdName);
        }
        $this->climate()->out(shell_exec($cmdName));
    }

    /**
     * @param string $cmd The jpegoptim command.
     */
    private function runOptipng($cmd): void
    {
        $cmdName = sprintf(
            'cd %s && \
            find -type f \( \
                -name "*.png" \-o \
                -name "*.PNG" \
            \) \
            -exec %s -o%s {} \;',
            $this->dir,
            $cmd,
            $this->pngLevel
        );
        if ($this->verbose()) {
            $this->climate()->out($cmdName);
        }
        $this->climate()->out(shell_exec($cmdName));
    }

    /**
     * @param string $cmdName The binary name to search.
     * @return string
     */
    private function findCmd(string $cmdName)
    {
        $cmd = exec('type -p ' . $cmdName);
        $cmd = str_replace($cmdName . ' is ', '', $cmd);
        if ($cmd === '' || $cmd === '0') {
            $cmd = exec('where ' . $cmdName);
        }
        if (!$cmd) {
            $cmd = exec('which ' . $cmdName);
        }
        if (!$cmd) {
            return '';
        }
        return $cmd;
    }
}
