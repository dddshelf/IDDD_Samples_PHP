<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\SlothMQ;

use PhpCollection\Map;
use Symfony\Component\Process\PhpProcess;

class SlothServer extends SlothWorker
{
    /**
     * @var Map
     */
    private $clientRegistrations;

    public static function executeInProcessDetachedServer()
    {
        $process = new PhpProcess(<<<EOPHP
<?php

require_once __DIR__ . '/vendor/autoload.php';

SaasOvation\Common\Port\Adapter\Messaging\SlothServer::executeNewServer();

EOPHP
        );

        $process->setWorkingDirectory(__DIR__ . '/../../../../../../');
        $process->start();
    }

    public static function executeNewServer()
    {
        (new SlothServer())->execute();
    }

    public static function main($anArguments)
    {
        self::executeNewServer();
    }

    public function __construct()
    {
        parent::__construct();

        $this->clientRegistrations = new Map();
    }

    public function execute()
    {
        while (!$this->isClosed()) {
            $receivedData = $this->receive();

            if (null !== $receivedData) {
                $this->handleMessage($receivedData);
            }
        }
    }

    protected function slothHub()
    {
        return true;
    }

    private function attachData($aReceivedData)
    {
        $port = intval(substr($aReceivedData, 7));

        return $this->attach($port);
    }

    private function attach($aPort)
    {
        $clientRegistration = $this->clientRegistrations->get($aPort);

        if (null === $clientRegistration) {
            $clientRegistration = new ClientRegistration($aPort);
            $this->clientRegistrations->set($aPort, $clientRegistration);
        }

        return $clientRegistration;
    }

    private function handleMessage($aReceivedData)
    {
        echo 'SLOTH SERVER: Handling: ' . $aReceivedData;

        if (0 === strpos($aReceivedData, 'ATTACH:')) {
            $this->attachData($aReceivedData);
        } elseif (0 === strpos($aReceivedData, 'CLOSE:')) {
            $this->close();
        } elseif (0 === strpos($aReceivedData, 'PUBLISH:')) {
            $this->publishToClients($aReceivedData);
        } elseif (0 === strpos($aReceivedData, 'SUBSCRIBE:')) {
            $this->subscribeClientTo(substr($aReceivedData, 10));
        } elseif (0 === strpos($aReceivedData, 'UNSUBSCRIBE:')) {
            $this->unsubscribeClientFrom(substr($aReceivedData, 12));
        } else {
            echo 'SLOTH SERVER: Does not understand: ' . $aReceivedData;
        }
    }

    private function publishToClients($anExchangeMessage)
    {
        $exchangeDivider = strpos($anExchangeMessage, 'PUBLISH:');
        $typeDivider = strpos($anExchangeMessage, 'TYPE:', $exchangeDivider + 8);

        if (-1 === $exchangeDivider) {
            echo 'SLOTH SERVER: PUBLISH: No exchange name; ignoring: ' . $anExchangeMessage;
        } elseif (-1 === $typeDivider) {
            echo 'SLOTH SERVER: PUBLISH: No TYPE; ignoring: ' . $anExchangeMessage;
        } else {
            $exchangeName = substr($anExchangeMessage, $exchangeDivider + 8, $typeDivider);

            foreach ($this->clientRegistrations->values() as $clientSubscriptions) {
                if ($clientSubscriptions->isSubscribedTo($exchangeName)) {
                    $this->sendTo($clientSubscriptions->port(), $anExchangeMessage);
                }
            }
        }
    }

    private function subscribeClientTo($aPortWithExchangeName)
    {
        $parts = explode(':', $aPortWithExchangeName);
        $port = intval($parts[0]);
        $exchangeName = $parts[1];

        $clientRegistration = $this->clientRegistrations->get($port);

        if (null === $clientRegistration) {
            $clientRegistration = $this->attach($port);
        }

        $clientRegistration->addSubscription($exchangeName);

        echo 'SLOTH SERVER: Subscribed: ' . $clientRegistration . ' TO: ' . $exchangeName;
    }

    private function unsubscribeClientFrom($aPortWithExchangeName)
    {
        $parts = explode(':', $aPortWithExchangeName);
        $port = intval($parts[0]);
        $exchangeName = $parts[1];

        $clientRegistration = $this->clientRegistrations->get($port);

        if ($clientRegistration !== null) {
            $clientRegistration->removeSubscription($exchangeName);

            echo 'SLOTH SERVER: Unsubscribed: ' . $clientRegistration . ' FROM: ' . $exchangeName;
        }
    }
}
