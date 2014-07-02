<?php

namespace SaasOvation\Collaboration\Domain\Model;

use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRepository;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarIdentityService;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarRepository;
use SaasOvation\Collaboration\Domain\Model\Collaborator\CollaboratorService;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionRepository;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumIdentityService;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumRepository;
use SaasOvation\Collaboration\Domain\Model\Forum\PostRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DomainRegistry
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @return CalendarIdentityService
     */
    public static function calendarIdentityService()
    {
        return static::$container->get('calendarIdentityService');
    }

    /**
     * @return CalendarEntryRepository
     */
    public static function calendarEntryRepository()
    {
        return static::$container->get('calendarEntryRepository');
    }

    /**
     * @return CalendarRepository
     */
    public static function calendarRepository()
    {
        return static::$container->get('calendarRepository');
    }

    /**
     * @return CollaboratorService
     */
    public static function collaboratorService()
    {
        return static::$container->get('collaboratorService');
    }

    /**
     * @return DiscussionRepository
     */
    public static function discussionRepository()
    {
        return static::$container->get('discussionRepository');
    }

    /**
     * @return ForumIdentityService
     */
    public static function forumIdentityService()
    {
        return static::$container->get('forumIdentityService');
    }

    /**
     * @return ForumRepository
     */
    public static function forumRepository()
    {
        return static::$container->get('forumRepository');
    }

    /**
     * @return PostRepository
     */
    public static function postRepository()
    {
        return static::$container->get('postRepository');
    }

    public static function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }
}