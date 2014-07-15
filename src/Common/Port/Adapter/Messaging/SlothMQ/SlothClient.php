<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\SlothMQ;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use PhpCollection\Map;

class SlothClient extends SlothWorker
{
    /**
     * @var SlothClient
     */
    private static $instance;

    /**
     * @var Map
     */
    private $exchangeListeners;

    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = new SlothClient();
        }

        return static::$instance;
    }

    public function close()
    {
        echo 'SLOTH CLIENT: Closing...';

        parent::close();

        $listeners = new ArrayCollection($this->exchangeListeners->values());

        foreach ($listeners as $listener) {
            $this->unregister($listener);
        }

        echo 'SLOTH CLIENT: Closed.';
    }

    public function closeAll()
    {
        static::$instance = null;

        $this->close();

        $this->sendToServer('CLOSE:');
    }

    public function publish($anExchangeName, $aType, $aMessage)
    {
        $this->sendToServer(
            'PUBLISH:' . $anExchangeName . 'TYPE:' . $aType . 'MSG:' . $aMessage
        );
    }

    public function register(ExchangeListener $anExchangeListener)
    {
        $this->exchangeListeners->set($anExchangeListener->name(), $anExchangeListener);

        $this->sendToServer(
            'SUBSCRIBE:' . $this->port() . ':' . $anExchangeListener->exchangeName()
        );
    }

    public function unregister(ExchangeListener $anExchangeListener)
    {
        $this->exchangeListeners->remove($anExchangeListener->name());

        $this->sendToServer(
            'UNSUBSCRIBE:' . $this->port() . ':' . $anExchangeListener->exchangeName()
        );
    }

    protected function __construct()
    {
        $this->exchangeListeners = new Map();

        $this->attach();
        $this->receiveAll();
    }

    private function attach()
    {
        $this->sendToServer(
            'ATTACH:' . $this->port()
        );
    }

    private function dispatchMessage($anEncodedMessage)
    {
        $exchangeDivider = strpos($anEncodedMessage, 'PUBLISH:');
        $typeDivider = strpos($anEncodedMessage, 'TYPE:', $exchangeDivider + 8);
        $msgDivider = strpos($anEncodedMessage, 'MSG:', $typeDivider + 5);

        $exchangeName = substr($anEncodedMessage, $exchangeDivider + 8, $typeDivider);
        $type = substr($anEncodedMessage, $typeDivider + 5, $msgDivider);
        $message = substr($anEncodedMessage, $msgDivider + 4);

        $listeners = new ArrayCollection($this->exchangeListeners->values());

        foreach ($listeners as $listener) {
            if ($exchangeName === $listener->exchangeName() && $listener->listensTo($type)) {
                try {
                    echo 'SLOTH CLIENT: Dispatching: Exchange: ' . $exchangeName . ' Type: ' . $type . ' Msg: ' . $message;

                    $listener->filteredDispatch($type, $message);
                } catch (Exception $e) {
                    echo 'SLOTH CLIENT: Exception while dispatching message: ' . $e->getMessage() . ': ' . $anEncodedMessage;
                    echo $e->getTraceAsString();
                }
            }
        }
    }

    private function receiveAll()
    {
        while (!$this->isClosed()) {
            $receivedData = null;

            $receivedData = $this->receive();

            if (null !== $receivedData) {
                $this->dispatchMessage(trim($receivedData));
            } else {
                $this->sleepFor(10);
            }
        }
    }
}
