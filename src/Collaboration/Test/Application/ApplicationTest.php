<?php

namespace SaasOvation\Collaboration\Test\Application;

use PDO;
use PHPUnit_Framework_TestCase;

use SaasOvation\Collaboration\Test\BuildsAggregates;
use SaasOvation\Collaboration\Test\BuildsServiceContainer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use SaasOvation\Collaboration\Application\Calendar\CalendarApplicationService;
use SaasOvation\Collaboration\Application\Calendar\CalendarEntryApplicationService;
use SaasOvation\Collaboration\Application\Calendar\CalendarEntryQueryService;
use SaasOvation\Collaboration\Application\Calendar\CalendarQueryService;
use SaasOvation\Collaboration\Application\Forum\DiscussionApplicationService;
use SaasOvation\Collaboration\Application\Forum\DiscussionQueryService;
use SaasOvation\Collaboration\Application\Forum\ForumApplicationService;
use SaasOvation\Collaboration\Application\Forum\ForumQueryService;
use SaasOvation\Collaboration\Application\Forum\PostApplicationService;
use SaasOvation\Collaboration\Application\Forum\PostQueryService;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;
use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;
use SaasOvation\Collaboration\Domain\Model\Collaborator\CollaboratorService;
use SaasOvation\Collaboration\Test\StorageCleaner;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;

abstract class ApplicationTest extends PHPUnit_Framework_TestCase
{
    use BuildsAggregates;
    use BuildsServiceContainer;

    /**
     * @var CalendarApplicationService
     */
    protected $calendarApplicationService;

    /**
     * @var CalendarEntryApplicationService
     */
    protected $calendarEntryApplicationService;

    /**
     * @var CalendarEntryQueryService
     */
    protected $calendarEntryQueryService;

    /**
     * @var CalendarQueryService
     */
    protected $calendarQueryService;

    /**
     * @var CollaboratorService
     */
    protected $collaboratorService;

    /**
     * @var PDO
     */
    protected $dataSource;

    /**
     * @var DiscussionApplicationService
     */
    protected $discussionApplicationService;

    /**
     * @var DiscussionQueryService
     */
    protected $discussionQueryService;

    /**
     * @var ForumApplicationService
     */
    protected $forumApplicationService;

    /**
     * @var ForumQueryService
     */
    protected $forumQueryService;

    /**
     * @var PostApplicationService
     */
    protected $postApplicationService;

    /**
     * @var PostQueryService
     */
    protected $postQueryService;

    /**
     * @var StorageCleaner
     */
    private $storageCleaner;

    protected function setUp()
    {
        DomainEventPublisher::instance()->reset();

        if (null === $this->container) {
            $this->buildAndCompileServiceContainer();
        }

        if ($this->dataSource === null) {
            $this->dataSource = $this->container->get('collaborationDataSource');
        }

        // Initialize MySQLProjectionEventDispatcher
        $this->container->get('mysqlProjectionDispatcher');
        $this->container->get('mysqlCalendarEntryProjection');
        $this->container->get('mysqlCalendarProjection');
        $this->container->get('mysqlDiscussionProjection');
        $this->container->get('mysqlForumProjection');
        $this->container->get('mysqlPostProjection');

        DomainRegistry::setContainer($this->container);
        
        $this->calendarApplicationService = $this->container->get('calendarApplicationService');
        $this->calendarQueryService = $this->container->get('calendarQueryService');

        $this->calendarEntryApplicationService = $this->container->get('calendarEntryApplicationService');
        $this->calendarEntryQueryService = $this->container->get('calendarEntryQueryService');
        
        $this->collaboratorService = $this->container->get('collaboratorService');
        
        $this->discussionApplicationService = $this->container->get('discussionApplicationService');
        $this->discussionQueryService = $this->container->get('discussionQueryService');
        
        $this->forumApplicationService = $this->container->get('forumApplicationService');
        $this->forumQueryService = $this->container->get('forumQueryService');
        
        $this->postApplicationService = $this->container->get('postApplicationService');
        $this->postQueryService = $this->container->get('postQueryService');
        
        $this->storageCleaner = new StorageCleaner($this->dataSource);
    }

    protected function tearDown()
    {
        $this->storageCleaner->clean();
        $this->dataSource = $this->container = null;
    }
}
