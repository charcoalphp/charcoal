<?php

namespace Charcoal\Image\Imagemagick;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Image\AbstractImage;

/**
*
*/
class ImagemagickImage extends AbstractImage
{

    /**
    * The temporary file location
    * @var string $tmp_file
    */
    private $tmp_file;
    private $mogrify_cmd;
    private $convert_cmd;
    private $identify_cmd;

    /**
    * @throws Exception
    */
    public function __construct()
    {
        // This will throw exception if the binaris are not found.
        $this->mogrify_cmd = $this->mogrify_cmd();
        $this->convert_cmd = $this->convert_cmd();
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
            throw new InvalidArgumentException(
                'Width must be an integer of at least 1 pixel'
            );
        }
        if (!is_int($height) || $height < 1) {
            throw new InvalidArgumentException(
                'Height must be an integer of at least 1 pixel'
            );
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
            throw new InvalidArgumentException(
                'Source must be a string'
            );
        }
        $source = ($source) ? $source : $this->source();
        $this->reset_tmp();
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
    * @param string $target The target path / filename
    * @throws Exception
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function save($target = null)
    {
        if ($target !== null && !is_string($target)) {
            throw new InvalidArgumentException(
                'Target must be a string'
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
        $cmd = $this->identify_cmd().' -format "%w" '.$this->tmp();
        return trim($this->exec($cmd));
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
        return trim($this->exec($cmd));
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
    * Try (as best as possible) to find a command name.
    * - With `type -p`
    * - Or else, with `where`
    * - Or else, with `which`
    *
    * @param string $cmd_name
    * @throws Exception
    * @return string
    */
    protected function find_cmd($cmd_name)
    {
        $cmd = exec('type -p '.$cmd_name);
        $cmd = str_replace($cmd_name.' is ', '', $cmd);
        
        if (!$cmd) {
            $cmd = exec('where '.$cmd_name);
        }

        if (!$cmd) {
            exec('which mogrify');
        }

        if (!$cmd) {
            throw new Exception(
                sprintf('Can not find imagemagick\'s "%s" command.', $cmd_name)
            );
        }
        
        return $cmd;
    }

    /**
    * @throws Exception
    * @return string
    */
    public function mogrify_cmd()
    {
        if ($this->mogrify_cmd !== null) {
            return $this->mogrify_cmd;
        }
        $this->mogrify_cmd = $this->find_cmd('mogrify');
        return $this->mogrify_cmd;
    }

    /**
    * @throws Exception
    * @return string
    */
    public function convert_cmd()
    {
        if ($this->convert_cmd !== null) {
            return $this->convert_cmd;
        }
        $this->convert_cmd = $this->find_cmd('convert');
        return $this->convert_cmd;
    }

    /**
    * @throws Exception
    * @return string
    */
    public function identify_cmd()
    {
        if ($this->identify_cmd !== null) {
            return $this->identify_cmd;
        }
        $this->identify_cmd = $this->find_cmd('identify');
        return $this->identify_cmd;
    }

    /**
    * @return string
    */
    public function tmp()
    {
        if ($this->tmp_file !== null) {
            return $this->tmp_file;
        }

        $this->tmp_file = sys_get_temp_dir().'/'.uniqid().'.png';
        return $this->tmp_file;
    }

    /**
    * @return ImagemagickImage Chainable
    */
    public function reset_tmp()
    {
        if (file_exists($this->tmp_file)) {
            unlink($this->tmp_file);
        }
        $this->tmp_file = null;
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


    /**
    * Convert a gravity name (string) to an `Imagick::GRAVITY_*` constant (integer)
    *
    * @param string $gravity
    * @throws InvalidArgumentException
    * @return integer
    */
    public function imagemagick_gravity($gravity)
    {
        $gravity_map = [
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
        if (!isset($gravity_map[$gravity])) {
            throw new InvalidArgumentException(
                'Invalid gravity'
            );
        }
        return $gravity_map[$gravity];
    }
}
