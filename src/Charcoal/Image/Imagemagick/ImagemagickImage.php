<?php

namespace Charcoal\Image\Imagemagick;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Image\AbstractImage;

/**
 * The Imagemagick image driver.
 *
 * Run from the binary imagemagick scripts.
 * (`mogrify`, `convert` and `identify`)
 */
class ImagemagickImage extends AbstractImage
{

    /**
     * The temporary file location
     * @var string $tmpFile
     */
    private $tmpFile;

    /**
     * @var string $mogrifyCmd
     */
    private $mogrifyCmd;
    /**
     * @var string $convertCmd
     */
    private $convertCmd;
    /**
     * @var string $identifyCmd
     */
    private $identifyCmd;

    /**
     * Set up the commands.
     */
    public function __construct()
    {
        // This will throw exception if the binaris are not found.
        $this->mogrifyCmd = $this->mogrifyCmd();
        $this->convertCmd = $this->convertCmd();
    }

    /**
     * Clean up the tmp file, if necessary
     */
    public function __destruct()
    {
        $this->resetTmp();
    }

    /**
     * @return string
     */
    public function driverType()
    {
        return 'imagemagick';
    }

    /**
     * Create a blank canvas of a given size, with a given background color.
     *
     * @param integer $width  Image size, in pixels.
     * @param integer $height Image height, in pixels.
     * @param string  $color  Default to transparent.
     * @throws InvalidArgumentException If the size arguments are not valid.
     * @return Image Chainable
     */
    public function create($width, $height, $color = 'rgb(100%, 100%, 100%, 0)')
    {
        if (!is_numeric($width) || $width < 1) {
            throw new InvalidArgumentException(
                'Width must be an integer of at least 1 pixel'
            );
        }
        if (!is_numeric($height) || $height < 1) {
            throw new InvalidArgumentException(
                'Height must be an integer of at least 1 pixel'
            );
        }

        $this->resetTmp();
        touch($this->tmp());
        $this->exec($this->convertCmd().' -size '.(int)$width.'x'.(int)$height.' canvas:"'.$color.'" '.$this->tmp());
        return $this;
    }

    /**
     * Open an image file
     *
     * @param string $source The source path / filename.
     * @throws Exception If the source file does not exist.
     * @throws InvalidArgumentException If the source argument is not a string.
     * @return Image Chainable
     */
    public function open($source = null)
    {
        if ($source !== null && !is_string($source)) {
            throw new InvalidArgumentException(
                'Source must be a string (file path)'
            );
        }
        $source = ($source) ? $source : $this->source();
        $this->resetTmp();
        if (!file_exists($source)) {
            throw new Exception(
                sprintf('File "%s" does not exist', $source)
            );
        }
        $tmp = $this->tmp();
        copy($source, $tmp);
        return $this;
    }

    /**
     * Save an image to a target.
     * If no target is set, the original source will be owerwritten
     *
     * @param string $target The target path / filename.
     * @throws Exception If the target file does not exist or is not writeable.
     * @throws InvalidArgumentException If the target argument is not a string.
     * @return Image Chainable
     */
    public function save($target = null)
    {
        if ($target !== null && !is_string($target)) {
            throw new InvalidArgumentException(
                'Target must be a string (file path)'
            );
        }
        $target = ($target) ? $target : $this->target();
        if (!is_writable(dirname($target))) {
            throw new Exception(
                sprintf('Target "%s" is not writable', $target)
            );
        }
        copy($this->tmp(), $target);
        return $this;
    }

    /**
     * Get the image's width, in pixels
     *
     * @return integer
     */
    public function width()
    {
        if (!file_exists($this->tmp())) {
            return 0;
        }
        $cmd = $this->identifyCmd().' -format "%w" '.$this->tmp();
        return (int)trim($this->exec($cmd));
    }

    /**
     * Get the image's height, in pixels
     *
     * @return integer
     */
    public function height()
    {
        if (!file_exists($this->tmp())) {
            return 0;
        }
        $cmd = $this->identifyCmd().' -format "%h" '.$this->tmp();
        return (int)trim($this->exec($cmd));
    }

    /**
     * @param string $channel The channel name to convert.
     * @return string
     */
    public function convertChannel($channel)
    {
        return ucfirst($channel);
    }

    /**
     * Try (as best as possible) to find a command name.
     * - With `type -p`
     * - Or else, with `where`
     * - Or else, with `which`
     *
     * @param string $cmdName The command name to find.
     * @throws Exception If the command can not be found.
     * @return string
     */
    protected function findCmd($cmdName)
    {
        $cmd = exec('type -p '.$cmdName);
        $cmd = str_replace($cmdName.' is ', '', $cmd);

        if (!$cmd) {
            $cmd = exec('where '.$cmdName);
        }

        if (!$cmd) {
            $cmd = exec('which '.$cmdName);
        }

        if (!$cmd) {
            throw new Exception(
                sprintf('Can not find imagemagick\'s "%s" command.', $cmdName)
            );
        }

        return $cmd;
    }

    /**
     * @return string The full path of the mogrify command.
     */
    public function mogrifyCmd()
    {
        if ($this->mogrifyCmd !== null) {
            return $this->mogrifyCmd;
        }
        $this->mogrifyCmd = $this->findCmd('mogrify');
        return $this->mogrifyCmd;
    }

    /**
     * @return string The full path of the convert command.
     */
    public function convertCmd()
    {
        if ($this->convertCmd !== null) {
            return $this->convertCmd;
        }
        $this->convertCmd = $this->findCmd('convert');
        return $this->convertCmd;
    }

    /**
     * @return string The full path of the identify comand.
     */
    public function identifyCmd()
    {
        if ($this->identifyCmd !== null) {
            return $this->identifyCmd;
        }
        $this->identifyCmd = $this->findCmd('identify');
        return $this->identifyCmd;
    }

    /**
     * Generate a temporary file, to apply effects on.
     *
     * @return string
     */
    public function tmp()
    {
        if ($this->tmpFile !== null) {
            return $this->tmpFile;
        }

        $this->tmpFile = sys_get_temp_dir().'/'.uniqid().'.png';
        return $this->tmpFile;
    }

    /**
     * @return ImagemagickImage Chainable
     */
    public function resetTmp()
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
        $this->tmpFile = null;
        return $this;
    }

    /**
     * Exec a command, either with `proc_open()` or `shell_exec()`
     *
     * The `proc_open()` method is preferred, as it allows to catch errors in
     * the STDERR buffer (and throw Exception) but it might be disabled in some
     * systems for security reasons.
     *
     * @param string $cmd The command to execute.
     * @throws Exception If the command fails.
     * @return string
     */
    public function exec($cmd)
    {
        if (function_exists('proc_open')) {
            $proc = proc_open(
                $cmd,
                [
                    1 => ['pipe','w'],
                    2 => ['pipe','w'],
                ],
                $pipes
            );
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            proc_close($proc);

            if ($err) {
                throw new Exception(
                    sprintf('Error executing command "%s": %s', $cmd, $err)
                );
            }

            return $out;
        } else {
            $ret = shell_exec($cmd, $out);

            return $ret;
        }
    }

    /**
     * @param string $cmd The command to run.
     * @throws Exception If the tmp file was not properly set.
     * @return ImagemagickImage Chainable
     */
    public function applyCmd($cmd)
    {
        if (!file_exists($this->tmp())) {
            throw new Exception(
                'No file currently set as tmp file, commands can not be executed.'
            );
        }
        $mogrify = $this->mogrifyCmd();
        $this->exec($mogrify.' '.$cmd.' '.$this->tmp());
        return $this;
    }


    /**
     * Convert a gravity name (string) to an `Imagick::GRAVITY_*` constant (integer)
     *
     * @param string $gravity The standard gravity name.
     * @throws InvalidArgumentException If the gravity argument is not a valid gravity.
     * @return integer
     */
    public function imagemagickGravity($gravity)
    {
        $gravityMap = [
            'center'    => 'center',
            'n'         => 'north',
            's'         => 'south',
            'e'         => 'east',
            'w'         => 'west',
            'ne'        => 'northeast',
            'nw'        => 'northwest',
            'se'        => 'southeast',
            'sw'        => 'southwest'
        ];
        if (!isset($gravityMap[$gravity])) {
            throw new InvalidArgumentException(
                'Invalid gravity. Possible values are: center, n, s, e, w, ne, nw, se, sw.'
            );
        }
        return $gravityMap[$gravity];
    }
}
