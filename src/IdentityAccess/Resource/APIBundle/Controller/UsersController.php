<?php

namespace SaasOvation\IdentityAccess\Resource\APIBundle\Controller;

use Exception;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use FOS\RestBundle\Controller\Annotations\Get;
use SaasOvation\IdentityAccess\Application\Command\AuthenticateUserCommand;
use SaasOvation\IdentityAccess\Application\Representation\UserInRoleRepresentation;
use SaasOvation\IdentityAccess\Domain\Model\Identity\User;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @RouteResource("User")
 */
class UsersController extends AbstractResource
{
    /**
     * @Cache(maxage="30")
     * @Get("/tenants/{aTenantId}/users/{aUsername}/autenticatedWith/{aPassword}")
     */
    public function getUserAutenticatedwithAction($aTenantId, $aUsername, $aPassword)
    {
        $userDescriptor = $this->identityApplicationService()->authenticateUser(
            new AuthenticateUserCommand(
                $aTenantId,
                $aUsername,
                $aPassword
            )
        );

        if ($userDescriptor->isNullDescriptor()) {
            throw new NotFoundHttpException();
        }

        return $this->handleView(View::create($userDescriptor, 200));
    }

    public function getAction($aTenantId, $aUsername)
    {
        $user = $this->identityApplicationService()->user($aTenantId, $aUsername);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        return $this->userResponse($user);
    }

    /**
     * @Cache(maxage="60")
     */
    public function getInroleAction($aTenantId, $aUsername, $aRoleName)
    {
        $response = null;

        $user = null;

        try {
            $user = $this->accessApplicationService()->userInRole(
                $aTenantId,
                $aUsername,
                $aRoleName
            );
        } catch (Exception $e) {
            // fall through
        }

        if (null !== $user) {
            $response = $this->handleView(
                View::create(
                    new UserInRoleRepresentation($user, $aRoleName),
                    200
                )
            );
        } else {
            $response = $this->handleView(View::create(null, 204));
        }

        return $response;
    }

    private function userResponse(User $aUser)
    {
        $response = $this->handleView(View::create($aUser, 200));
        $response->setEtag(
            $this->userETag($aUser)
        );

        return $response;
    }

    private function userETag(User $aUser)
    {
        return md5($aUser);
    }
}
