<?php

namespace Charcoal\Image\Imagemagick;

use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Image\AbstractImage as AbstractImage;

/**
*
*/
class ImagemagickImage extends AbstractImage
{

    /**
    * The temporary file location
    * @var string $_tmp
    */
    private $_tmp;
    private $_mogrify_cmd;
    private $_convert_cmd;
    private $_identify_cmd;

    /**
    * @throws Exception
    */
    public function __construct()
    {
        // This will throw exception if the binaris are not found.
        $this->_mogrify_cmd = $this->mogrify_cmd();
        $this->_convert_cmd = $this->convert_cmd();
    }

    /**
    * Clean up the tmp file, if necessary
    */
    public function __destruct()
    {
        $this->reset_tmp();
    }

    /**
    * @return string
    */
    public function driver_type()
    {
        return 'imagemagick';
    }

    /**
    * Create a blank canvas of a given size, with a given background color.
    *
    * @param integer $width  Image size, in pixels
    * @param integer $height Image height, in pixels
    * @param string  $color  Default to transparent.
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function create($width, $height, $color = 'rgb(100%, 100%, 100%, 0)')
    {
        if (!is_int($width) || $width < 1) {
            throw new InvalidArgumentException('Width must be an integer of at least 1 pixel');
        }
        if (!is_int($height) || $height < 1) {
            throw new InvalidArgumentException('Height must be an integer of at least 1 pixel');
        }
        
        $this->reset_tmp();
        touch($this->tmp());
        $this->exec($this->convert_cmd().' -size '.$width.'x'.$height.' canvas:"'.$color.'" '.$this->tmp());
        return $this;
    }

    /**
    * Open an image file
    *
    * @param string $source The source path / filename
    * @throws Exception
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function open($source = null)
    {
        if ($source !== null && !is_string($source)) {
            throw new InvalidArgumentException('Source must be a string');
        }
        $source = ($source) ? $source : $this->source();
        $this->reset_tmp();
        if (!file_exists($source)) {
            throw new Exception('File does not exist');
        }
        $tmp = $this->tmp();
        copy($source, $tmp);
        return $this;
    }

    /**
    * Save an image to a target.
    * If no target is set, the original source will be owerwritten
    *
    * @param string $target The target path / filename
    * @throws Exception
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function save($target = null)
    {
        if ($target !== null && !is_string($target)) {
            throw new InvalidArgumentException('Target must be a string');
        }
        $target = ($target) ? $target : $this->target();
        if (!is_writable(dirname($target))) {
            throw new Exception('Target is not writable');
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
        $cmd = $this->identify_cmd().' -format "%w" '.$this->tmp();
        return $this->exec($cmd);
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
        $cmd = $this->identify_cmd().' -format "%h" '.$this->tmp();
        return $this->exec($cmd);
    }

    /**
    * @param string $channel
    * @return string
    */
    public function convert_channel($channel)
    {

        return ucfirst($channel);
    }

    /**
    * @throws Exception
    * @return string
    */
    public function mogrify_cmd()
    {
        if ($this->_mogrify_cmd !== null) {
            return $this->_mogrify_cmd;
        }
        $cmd = exec('type -p mogrify');
        if (!$cmd) {
            $cmd = exec('where mogrify');
        }
        if (!$cmd) {
            exec('which mogrify');
        }
        if (!$cmd) {
            throw new Exception('Can not find imagemagick\'s mogrify command.');
        }
        return $cmd;
    }

    /**
    * @throws Exception
    * @return string
    */
    public function convert_cmd()
    {
        if ($this->_convert_cmd !== null) {
            return $this->_convert_cmd;
        }

        $cmd = exec('type -p convert');

        if (! $cmd) {
            $cmd = exec('where convert');
        }

        if (! $cmd) {
            $cmd = exec('which convert');
        }
        if (!$cmd) {
            throw new Exception('Can not find imagemagick\'s convert command.');
        }
        return $cmd;
    }

    /**
    * @throws Exception
    * @return string
    */
    public function identify_cmd()
    {
        if ($this->_identify_cmd !== null) {
            return $this->_identify_cmd;
        }

        $cmd = exec('type -p identify');

        if (! $cmd) {
            $cmd = exec('where identify');
        }

        if (! $cmd) {
            $cmd = exec('which identify');
        }
        if (!$cmd) {
            throw new Exception('Can not find imagemagick\'s identify command.');
        }
        return $cmd;
    }

    /**
    * @return string
    */
    public function tmp()
    {
        if ($this->_tmp !== null) {
            return $this->_tmp;
        }

        $this->_tmp = sys_get_temp_dir().'/'.uniqid().'.png';
        return $this->_tmp;
    }

    /**
    * @return ImagemagickImage Chainable
    */
    public function reset_tmp()
    {
        if (file_exists($this->_tmp)) {
            unlink($this->_tmp);
        }
        $this->_tmp = null;
        return $this;
    }

    /**
    * Exec a command, either with `proc_open()` or `shell_exec()`
    *
    * The `proc_open()` method is preferred, as it allows to catch errors in
    * the STDERR buffer (and throw Exception) but it might be disabled in some
    * systems for security reasons.
    *
    * @param string $cmd
    * @throws Exception
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
                    sprintf('Error executing command: %s', $err)
                );
            }

            return $out;
        } else {
            $ret = shell_exec($cmd, $out);

            return $ret;
        }
    }

    /**
    * @param string $cmd
    * @throws Exception
    * @return ImagemagickImage Chainable
    */
    public function apply_cmd($cmd)
    {
        if (!file_exists($this->tmp())) {
            throw new Exception('No file');
        }
        $mogrify = $this->mogrify_cmd();
        $ret = $this->exec($mogrify.' '.$cmd.' '.$this->tmp());
        return $this;
    }
}
