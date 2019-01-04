<?php

namespace Tomato\PDFMerger;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

use Symfony\Component\Process\Process;

/**
 *
 * 接口访问类，包含所有Omi支付API列表的封装，类中方法为static方法，
 *
 */
class PDFMerger
{

    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    private $binary;
    private $inputFiles=[];
    private $timeout = false;

    public function __construct($app = null,$binary=null)
    {
        if (!$app) {
            $app = app();
        }
        $this->app = $app;

        if(is_null($binary)){
            $binary=conf("pdfmerger.binary");
        }

        $this->setBinary($binary);
    }

    /**
     * Defines the binary.
     *
     * @param string $binary The path/name of the binary
     */
    public function setBinary($binary)
    {
        $this->binary = $binary;
    }

    /**
     * Sets the timeout.
     *
     * @param int $timeout The timeout to set
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function addInputFile($file){
        $this->inputFiles[]=$file;
    }

    public function setInputFiles($files){
        if(!is_array($files)){
            throw new \LogicException(
                'Input files have to be an array'
            );
        }
        $this->inputFiles=$files;
    }
    /**
     * {@inheritdoc}
     */
    public function generate($output)
    {
        if (null === $this->binary) {
            throw new \LogicException(
                'You must define a binary prior to conversion.'
            );
        }
        if(empty($this->inputFiles)){
            throw new \LogicException(
                'You must define input files to merge'
            );
        }
        $command = $this->getCommand($output,$this->inputFiles);
        try {
            list($status, $stdout, $stderr) = $this->executeCommand($command);
            $this->checkProcessStatus($status, $stdout, $stderr, $command);
            $this->checkOutput($output, $command);
        } catch (\Exception $e) { // @TODO: should be replaced by \Throwable when support for php5.6 is dropped
            \Log::error(sprintf(
                'An error happened while generating "%s" says something went wrong:' . "\n"
                . 'stderr: "%s"' . "\n"
                . 'stdout: "%s"' . "\n"
                . 'command: %s.',
                $output,
                (isset($stderr) ? $stderr : null),(isset($stdout) ? $stdout : null), $command
            ));
            throw $e;
        }
        return true;
    }

    /**
     * Returns the binary.
     *
     * @return string
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * Returns the command for the given input and output files.
     *
     * @param $outputFile
     * @param $inputFiles
     * @return string
     *
     */
    public function getCommand($outputFile,$inputFiles)
    {
        return $this->buildCommand($this->binary, $outputFile,$inputFiles);
    }
    /**
     * Checks the specified output.
     *
     * @param string $output  The output filename
     * @param string $command The generation command
     *
     * @throws \RuntimeException if the output file generation failed
     */
    protected function checkOutput($output, $command)
    {
        // the output file must exist
        if (!$this->fileExists($output)) {
            throw new \RuntimeException(sprintf(
                'The file \'%s\' was not created (command: %s).',
                $output, $command
            ));
        }
        // the output file must not be empty
        if (0 === $this->filesize($output)) {
            throw new \RuntimeException(sprintf(
                'The file \'%s\' was created but is empty (command: %s).',
                $output, $command
            ));
        }
    }
    /**
     * Checks the process return status.
     *
     * @param int    $status  The exit status code
     * @param string $stdout  The stdout content
     * @param string $stderr  The stderr content
     * @param string $command The run command
     *
     * @throws \RuntimeException if the output file generation failed
     */
    protected function checkProcessStatus($status, $stdout, $stderr, $command)
    {
        if (0 !== $status and '' !== $stderr) {
            throw new \RuntimeException(sprintf(
                'The exit status code \'%s\' says something went wrong:' . "\n"
                . 'stderr: "%s"' . "\n"
                . 'stdout: "%s"' . "\n"
                . 'command: %s.',
                $status, $stderr, $stdout, $command
            ), $status);
        }
    }
    /**
     * Builds the command string.
     *
     * @param string       $binary  The binary path/name
     * @param string       $output  File location to the image-to-be
     * @param string/array $input   Url(s) or file location(s) of the page(s) to process
     *
     * @return string
     */
    protected function buildCommand($binary,$output,$input)
    {
        $command = $binary;
        $escapedBinary = escapeshellarg($binary);
        if (is_executable($escapedBinary)) {
            $command = $escapedBinary;
        }
        $command .= ' -o ' . escapeshellarg($output);
        if (is_array($input)) {
            foreach ($input as $i) {
                $command .= ' ' . escapeshellarg($i) . ' ';
            }
        }
        return $command;
    }
    /**
     * Executes the given command via shell and returns the complete output as
     * a string.
     *
     * @param string $command
     *
     * @return array(status, stdout, stderr)
     */
    protected function executeCommand($command)
    {
        if (method_exists(Process::class, 'fromShellCommandline')) {
            $process = Process::fromShellCommandline($command, null, $this->env);
        } else {
            $process = new Process($command, null);
        }
        if (false !== $this->timeout) {
            $process->setTimeout($this->timeout);
        }
        $process->run();
        return [
            $process->getExitCode(),
            $process->getOutput(),
            $process->getErrorOutput(),
        ];
    }
    /**
     * Wrapper for the "file_exists" function.
     *
     * @param string $filename
     *
     * @return bool
     */
    protected function fileExists($filename)
    {
        return file_exists($filename);
    }
    /**
     * Wrapper for the "filesize" function.
     *
     * @param string $filename
     *
     * @return int or FALSE on failure
     */
    protected function filesize($filename)
    {
        return filesize($filename);
    }

}