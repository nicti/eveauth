<?php

namespace App\Error;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class DiscordHandler implements LoggerInterface
{
    const BASE_URI = 'https://discord.com/api';
    const VERSION = 'v6';

    protected $client = null;

    public function __construct()
    {
        if (is_null($this->client)) {
            $this->client = new Client([
                'base_uri' => sprintf('%s', rtrim(self::BASE_URI, '/')),
                'headers' => [
                    'Authorization' => sprintf('Bot %s', $_ENV['BOT_TOKEN']),
                    'Content-Type' => 'application/json',
                ],
            ]);
            $gatewayResponse = $this->client->request(
                'GET',
                '/api/'.self::VERSION.'/gateway/bot'
            );
            $socketUrl = json_decode($gatewayResponse->getBody(),true)['url'];
            $socket = new \WebSocket\Client($socketUrl);
            $socket->send(json_encode([
                "op" => 2,
                "d" => [
                    "token" => $_ENV['BOT_TOKEN'],
                    "intents" => '0',
                    "properties" => [
                        '$os' => 'linux',
                        '$browser' => 'EVEAuth',
                        '$device' => 'EVEAuth'
                    ]

                ]
            ]));
            $socket->close();
        }
    }

    /**
     * @param array $message
     * @param array $context
     */
    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @param array $message
     * @param array $context
     */
    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * @param array $message
     * @param array $context
     */
    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @param array $message
     * @param array $context
     */
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * @param array $message
     * @param array $context
     */
    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * @param array $message
     * @param array $context
     */
    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @param array $message
     * @param array $context
     */
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * @param array $message
     * @param array $context
     */
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param mixed $level
     * @param array $message
     * @param array $context
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function log($level, $message, array $context = array())
    {
        $this->client->request(
            'POST',
            '/api/' . self::VERSION . '/channels/' . $_ENV['REPORT_CHANNEL'] . '/messages',
            [
                'json' => [
                    'content' => 'An ' . $level . ' was reported:',
                    'embed' => $message
                ]
            ]
        );
    }
}