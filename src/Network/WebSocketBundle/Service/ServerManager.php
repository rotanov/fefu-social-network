<?php

namespace Network\WebSocketBundle\Service;

use \InvalidArgumentException;
use Wrench\Server;
use \Closure;
use Monolog\Logger;

class ServerManager
{
    /**
     * @var Closure
     */
    protected $logger;

    /**
     * @var array<string => Server>
     */
    protected $servers = array();

    /**
     * @var array<Application>
     */
    protected $applications = array();

    /**
     * @var array<string => array>
     */
    protected $configuration;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = function ($message, $level = 'info') {
            echo $level . ': ' . $message . "\n";
        };
    }

    public function addApplication($key, $application)
    {
        $this->applications[$key] = $application;
    }

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Gets a server by name
     *
     * @param string $name
     * @return Server
     */
    public function getServer($name)
    {
        if (!isset($this->servers[$name])) {
            return $this->createServer($name);
        }
        return $this->servers[$name];
    }

    /**
     * Creates a server
     *
     * @param string $name
     * @throws InvalidArgumentException
     * @return Server
     */
    public function createServer($name)
    {
        if (!isset($this->configuration[$name])) {
            throw new InvalidArgumentException('Invalid server name');
        }

        $config = $this->configuration[$name];

        $server = new $config['class'](
            $config['listen'],
            array(
                'logger'          => $this->logger,
                'allowed_origins' => $config['allow_origin'],
                'check_origin' => $config['check_origin'],
                'rate_limiter_options' => [
                    'connections'     => $config['max_clients'],
                    'connections_per_ip' => $config['max_connections_per_ip'],
                    'requests_per_minute' => $config['max_requests_per_minute']
                ]
            )
        );

        foreach ($config['applications'] as $key) {
            if (!isset($this->applications[$key])) {
                throw new \RuntimeException('Invalid server config: application ' . $key . ' not found');
            }
            $server->registerApplication($key, $this->applications[$key]);
        }

        return (($this->servers[$name] = $server));
    }

    /**
     * @param \Closure|Logger $logger
     * @return void
     */
    public function setLogger($logger)
    {
        if ($logger instanceof Logger) {
            $this->logger = function ($message, $level) use ($logger) {
                switch ($level) {
                    case 'info':
                        $logger->info($message);
                        return;
                    case 'warn':
                    default:
                        $logger->warn($message);
                        return;
                }
            };
        } else {
            $this->logger = $logger;
        }
    }
}
