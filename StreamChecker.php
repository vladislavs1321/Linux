<?php

/**
 * Class CheckingAllowRun
 */
class CheckingAllowRun
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Checks if allow continue work
     *
     * @return bool
     */
    public function allow()
    {
        @$fp = fopen($this->filePath, 'x+');
        if ($fp === false) {
            $fp = fopen($this->filePath, 'r');
            $time = fread($fp, filesize($this->filePath));
            if ($time == '' || (time() - $time) <= 60) {
                return false;
            }
        }
        fclose($fp);

        return true;
    }

    /**
     * Updates
     */
    public function update()
    {
        @$fp = fopen($this->filePath, 'w+');
        if ($fp !== false) {
            fseek($fp, 0);
            ftruncate($fp, 0);
            fwrite($fp, time());
            fflush($fp);
            fclose($fp);
        }
    }

    /**
     * Finishes
     */
    public function finish()
    {
        unlink($this->filePath);
    }
}
