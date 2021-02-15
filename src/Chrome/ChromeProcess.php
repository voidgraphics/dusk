<?php

namespace Laravel\Dusk\Chrome;

use Laravel\Dusk\OperatingSystem;
use RuntimeException;
use Symfony\Component\Process\Process;

class ChromeProcess
{
    /**
     * The path to the Chromedriver.
     *
     * @var string|null
     */
    protected $driver;

    /**
     * Create a new ChromeProcess instance.
     *
     * @param  string  $driver
     * @return void
     */
    public function __construct($driver = null)
    {
        $this->driver = $driver;
    }

    /**
     * Build the process to run Chromedriver.
     *
     * @param  array  $arguments
     * @return \Symfony\Component\Process\Process
     *
     * @throws \RuntimeException
     */
    public function toProcess(array $arguments = [])
    {
        if ($this->driver) {
            $driver = $this->driver;
        } elseif ($this->onWindows()) {
            $driver = realpath(__DIR__.'/../../bin/chromedriver-win.exe');
        } elseif ($this->onIntelMac()) {
            $driver = realpath(__DIR__.'/../../bin/chromedriver-mac-intel');
        } elseif ($this->onArmMac()) {
            $driver = realpath(__DIR__.'/../../bin/chromedriver-mac-arm');
        } else {
            $driver = __DIR__.'/../../bin/chromedriver-linux';
        }

        $this->driver = realpath($driver);

        if ($this->driver === false) {
            throw new RuntimeException(
                "Invalid path to Chromedriver [{$driver}]. Make sure to install the Chromedriver first by running the dusk:chrome-driver command."
            );
        }

        return $this->process($arguments);
    }

    /**
     * Build the Chromedriver with Symfony Process.
     *
     * @param  array  $arguments
     * @return \Symfony\Component\Process\Process
     */
    protected function process(array $arguments = [])
    {
        return new Process(
            array_merge([realpath($this->driver)], $arguments), null, $this->chromeEnvironment()
        );
    }

    /**
     * Get the Chromedriver environment variables.
     *
     * @return array
     */
    protected function chromeEnvironment()
    {
        if ($this->onIntelMac() || $this->onArmMac() || $this->onWindows()) {
            return [];
        }

        return ['DISPLAY' => $_ENV['DISPLAY'] ?? ':0'];
    }

    /**
     * Determine if Dusk is running on Windows or Windows Subsystem for Linux.
     *
     * @return bool
     */
    protected function onWindows()
    {
        return OperatingSystem::onWindows();
    }

    /**
     * Determine if Dusk is running on Mac x86_64.
     *
     * @return bool
     */
    protected function onIntelMac()
    {
        return OperatingSystem::onIntelMac();
    }

    /**
     * Determine if Dusk is running on Mac arm64.
     *
     * @return bool
     */
    protected function onArmMac()
    {
        return OperatingSystem::onArmMac();
    }
}
