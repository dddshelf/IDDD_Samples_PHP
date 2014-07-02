<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ;

use SaasOvation\Common\AssertionConcern;

/**
 * I am a configuration for making a connection to
 * RabbitMQ. I include information for the host, port,
 * virtual host, and user.
 *
 * @author Vaughn Vernon
 */
class ConnectionSettings extends AssertionConcern
{
    /**
     * My hostName, which is the name of the host server.
     *
     * @var string
     */
    private $hostName;

    /**
     * My password, which is the password of the connecting user.
     *
     * @var string
     */
    private $password;

    /**
     * My port, which is the host server port.
     *
     * @var int
     */
    private $port;

    /**
     * My username, which is the name of the connecting user.
     *
     * @var string
     */
    private $username;

    /**
     * My virtualHost, which is the name of the RabbitMQ virtual host.
     *
     * @var string
     */
    private $virtualHost;

    /**
     * Constructs my default state.
     *
     * @param string $aHostName
     * @param int $aPort
     * @param string $aVirtualHost
     * @param string $aUsername
     * @param string $aPassword
     *
     * @return ConnectionSettings
     */
    public static function instance($aHostName = 'localhost', $aPort = 5672, $aVirtualHost = '/', $aUsername = 'guest', $aPassword = 'guest')
    {
        return new ConnectionSettings(
            $aHostName, $aPort, $aVirtualHost, $aUsername, $aPassword
        );
    }

    /**
     * Constructs my default state.
     *
     * @param string $aHostName
     * @param int $aPort
     * @param string $aVirtualHost
     * @param string $aUsername
     * @param string $aPassword
     */
    protected function __construct(
        $aHostName,
        $aPort,
        $aVirtualHost,
        $aUsername,
        $aPassword
    ) {

        $this->setHostName($aHostName);
        $this->setPassword($aPassword);
        $this->setPort($aPort);
        $this->setUsername($aUsername);
        $this->setVirtualHost($aVirtualHost);
    }

    /**
     * Answers my hostName.
     *
     * @return String
     */
    public function hostName()
    {
        return $this->hostName;
    }

    /**
     * Sets my hostName.
     *
     * @param string $aHostName
     */
    private function setHostName($aHostName)
    {
        $this->assertArgumentNotEmpty($aHostName, 'Host name must be provided.');

        $this->hostName = $aHostName;
    }

    /**
     * Answers my password.
     *
     * @return string
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * Sets my password.
     *
     * @param string $aPassword
     */
    private function setPassword($aPassword)
    {
        $this->password = $aPassword;
    }

    /**
     * Answers my port.
     *
     * @return int
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * Answers whether or not a port is included.
     *
     * @return boolean
     */
    public function hasPort()
    {
        return $this->port() > 0;
    }

    /**
     * Sets my port.
     *
     * @param int $aPort
     */
    private function setPort($aPort)
    {
        $this->port = $aPort;
    }

    /**
     * Answers whether or not the user credentials are included.
     *
     * @return boolean
     */
    public function hasUserCredentials()
    {
        return null !== $this->username() && null !== $this->password();
    }

    /**
     * Answers my username.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * Sets my username.
     *
     * @param string $aUsername
     */
    private function setUsername($aUsername)
    {
        $this->username = $aUsername;
    }

    /**
     * Answers my virtualHost.
     *
     * @return string
     */
    public function virtualHost()
    {
        return $this->virtualHost;
    }

    /**
     * Sets my virtualHost.
     *
     * @param string $aVirtualHost
     */
    private function setVirtualHost($aVirtualHost)
    {
        $this->assertArgumentNotEmpty($aVirtualHost, 'Virtual host must be provided.');

        $this->virtualHost = $aVirtualHost;
    }
}
