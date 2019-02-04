<?php

namespace SimpleSAML\Logger;

use Psr\Log\LoggerInterface;
use SimpleSAML\Logger;

/**
 * A logging handler using Symfony logger (e.g. Monolog)
 *
 * @package SimpleSAMLphp
 */
class SymfonyLoggingHandler implements LoggingHandlerInterface
{
    /**
     * @var null|LoggerInterface
     */
    protected $driver = null;
    /**
     * @var string|null
     */
    protected $processname = null;
    /**
     * @var string
     */
    protected $format;


    /**
     * Build a new logging handler based on files.
     * @throws \Exception
     * @throws \SimpleSAML_Error_Exception
     */
    public function __construct(\SimpleSAML_Configuration $config)
    {
        $this->driver = $config->getValue('logging.handler_driver');
        if (is_null($this->driver)) {
            throw new \Exception("No driver for SymfonyLoggingHandler: `logging.handler_driver` is not set to logger object");
        }

        $this->processname = $config->getString('logging.processname', 'SimpleSAMLphp');

        \SimpleSAML\Utils\Time::initTimezone();
    }


    /**
     * Set the format desired for the logs.
     *
     * @param string $format The format used for logs.
     */
    public function setLogFormat($format)
    {
        $this->format = $format;
    }


    /**
     * Log a message to the log file.
     *
     * @param int    $level The log level.
     * @param string $string The formatted message to log.
     */
    public function log($level, $string)
    {
        if (!is_null($this->driver)) {
            $formats = ['%process', '%level'];
            $replacements = [$this->processname, ''];

            $matches = [];
            if (preg_match('/%date(?:\{([^\}]+)\})?/', $this->format, $matches)) {
                $format = "%b %d %H:%M:%S";
                if (isset($matches[1])) {
                    $format = $matches[1];
                }

                $formats[] = $matches[0];
                $replacements[] = strftime($format);
            }

            $string = str_replace($formats, $replacements, $string);

            switch ($level) {
                case Logger::EMERG:
                    $this->driver->emergency($string);
                    break;
                case Logger::ALERT:
                    $this->driver->alert($string);
                    break;
                case Logger::CRIT:
                    $this->driver->critical($string);
                    break;
                case Logger::ERR:
                    $this->driver->error($string);
                    break;
                case Logger::WARNING:
                    $this->driver->warning($string);
                    break;
                case Logger::NOTICE:
                    $this->driver->notice($string);
                    break;
                case Logger::INFO:
                    $this->driver->info($string);
                    break;
                case Logger::DEBUG:
                    $this->driver->debug($string);
                    break;
                default:
                    $this->driver->log($level, $string);
                    break;
            }
        }
    }
}
