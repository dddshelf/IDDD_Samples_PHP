<?php

namespace SaasOvation\Common\Port\Adapter\Persistence;

use Exception;
use Icecave\Collections\Map;
use PDO;
use PDOStatement;

use RuntimeException;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Event\Sourcing\DispatchableDomainEvent;
use SaasOvation\Common\Event\Sourcing\EventDispatcher;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Verraes\ClassFunctions\ClassFunctions;

abstract class AbstractProjection implements EventDispatcher
{
    /**
     * @var string
     */
    protected static $PROJECTION_METHOD_NAME = 'when';

    /**
     * @var PDO
     */
    private $connection;

    public function __construct(PDO $connection, EventDispatcher $aParentEventDispatcher)
    {
        $aParentEventDispatcher->registerEventDispatcher($this);
        $this->connection = $connection;
    }

    public function dispatch(DispatchableDomainEvent $aDispatchableDomainEvent)
    {
        $this->projectWhen($aDispatchableDomainEvent);
    }

    public function registerEventDispatcher(EventDispatcher $anEventDispatcher)
    {
        throw new BadMethodCallException('Cannot register additional dispatchers.');
    }

    public function understands(DispatchableDomainEvent $aDispatchableDomainEvent)
    {
        $understoodEventTypes = $this->understoodEventTypes();

        if (!is_array($understoodEventTypes) || empty($understoodEventTypes)) {
            throw new BadMethodCallException('A list of supported event types must be provided');
        }

        return $this->understandsAnyOf(
            get_class($aDispatchableDomainEvent->domainEvent()),
            $understoodEventTypes
        );
    }

    protected function connection()
    {
        return $this->connection;
    }

    protected function execute(PDOStatement $aStatement)
    {
        try {
            $aStatement->execute();
        } finally {
            $aStatement->closeCursor();
        }
    }

    protected function exists($aQuery)
    {
        $exists = false;
        $anArguments = func_get_args();
        $statement = null;

        try {
            $statement = $this->connection()->prepare($aQuery);
            $anArguments = array_slice($anArguments, 1);

            for ($idx = 0; $idx < count($anArguments); ++$idx) {
                $statement->bindValue($idx + 1, $anArguments[$idx]);
            }

            $statement->execute();

            if ($statement->fetch()) {
                $exists = true;
            }

        } finally {
            if (null !== $statement) {
                try {
                    $statement->closeCursor();
                } catch (Exception $e) {
                    // ignore
                }
            }
        }

        return $exists;
    }

    protected function projectWhen(DispatchableDomainEvent $aDispatchableDomainEvent)
    {
        if (!$this->understands($aDispatchableDomainEvent)) {
            return;
        }

        $domainEvent = $aDispatchableDomainEvent->domainEvent();
        $aProjectMethod = static::$PROJECTION_METHOD_NAME . ClassFunctions::short($domainEvent);

        try {

            $this->$aProjectMethod($domainEvent);

        } catch (Exception $e) {
            if (null !== $e->getPrevious()) {
                throw new RuntimeException(
                    'Method ' . static::$PROJECTION_METHOD_NAME . '(' . ClassFunctions::fqcn($domainEvent) . ') failed. See cause: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }

            throw new RuntimeException(
                'Method ' . static::$PROJECTION_METHOD_NAME . '(' . ClassFunctions::fqcn($domainEvent) . ') failed. See cause: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );

        }
    }

    protected function understandsAnyOf($aDispatchedType, array $anUnderstoodEventTypes)
    {
        return in_array($aDispatchedType, $anUnderstoodEventTypes);
    }

    abstract protected function understoodEventTypes();
}
