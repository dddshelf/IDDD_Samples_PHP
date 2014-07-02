<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\SlothMQ;

use Exception;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;
use React\Socket\Server;
use RuntimeException;
use Verraes\ClassFunctions\ClassFunctions;

abstract class SlothWorker
{
    private static $HUB_PORT = 55555;

    /**
     * @var int
     */
    private $port;

    /**
     * @var resource
     */
    private $socket;

    protected function __construct()
    {
        $this->open();
    }

    protected function close()
    {
        fclose($this->socket);
        $this->socket = null;
    }

    protected function isClosed()
    {
        return null === $this->socket;
    }

    protected function port()
    {
        return $this->port;
    }

    protected function receive()
    {
        $conn = stream_socket_accept($this->socket);

        if (false === $conn) {
            return null;
        }

        return stream_get_contents($conn);
    }

    protected function sendTo($aPort, $anEncodedMessage)
    {
        $client = stream_socket_client(sprintf('tcp://0.0.0.0:%d', $aPort), $errno, $errstr);

        if (!$client) {
            echo ClassFunctions::short($this) . ': Failed to send because: ' . $errstr . ': Continuing...';
            return;
        }

        fwrite($client, $anEncodedMessage);
        echo ClassFunctions::short($this) . ': Sent: ' . $anEncodedMessage;
        fclose($client);
    }

    protected function sendToServer($anEncodedMessage)
    {
        $this->sendTo(self::$HUB_PORT, $anEncodedMessage);
    }

    protected function sleepFor($aMillis)
    {
        sleep($aMillis / 1000);
    }

    protected function slothHub()
    {
        return false;
    }

    private function discoverClientPort()
    {
        $discovered = false;
        $discoveryPort = self::$HUB_PORT + 1;
        $errorPort = $discoveryPort + 20;

        while (!$discovered && $discoveryPort < $errorPort) {
            $this->socket = stream_socket_client(sprintf('tcp://0.0.0.0:%d', $discoveryPort));

            if (!$this->socket) {
                $discoveryPort++;
                continue;
            }

            $discovered = true;
        }

        if (!$discovered) {
            throw new RuntimeException('No ports available.');
        }

        return $discoveryPort;
    }

    private function open()
    {
        if ($this->slothHub()) {
            $this->openHub();
        } else {
            $this->openClient();
        }
    }

    private function openClient()
    {
        try {
            $this->port = $this->discoverClientPort();
            stream_set_blocking($this->socket, 0);
            echo 'SLOTH CLIENT: Opened on port: ' . $this->port;
        } catch (Exception $e) {
            echo 'SLOTH CLIENT: Cannot connect because: ' . $e->getMessage();
        }
    }

    private function openHub()
    {
        try {
            $this->socket = stream_socket_server(sprintf('tcp://0.0.0.0:%d', self::$HUB_PORT));
            stream_set_blocking($this->socket, true);
            $this->port = self::$HUB_PORT;
            echo 'SLOTH SERVER: Opened on port: ' . $this->port;
        } catch (Exception $e) {
            echo 'SLOTH SERVER: Cannot connect because: ' . $e->getMessage();
        }
    }
}
