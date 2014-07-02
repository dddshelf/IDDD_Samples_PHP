<?php

namespace SaasOvation\Collaboration\Port\Adapter\Service;

use Exception;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

class HttpUserInRoleAdapter implements UserInRoleAdapter
{
    private static $HOST            = 'localhost';
    private static $PORT            = '8081';
    private static $PROTOCOL        = 'http';
    private static $URL_TEMPLATE    = '/idovation/tenants/{tenantId}/users/{username}/inRole/{role}';

    public function toCollaborator(Tenant $aTenant, $anIdentity, $aRoleName, $aCollaboratorClass)
    {
        $collaborator = null;

        try {
            $request = $this->buildRequest($aTenant, $anIdentity, $aRoleName);

            $response = $this->client->send($request);

            if (204 === (int) $response->getStatusCode()) {
                return;
            }

            if (200 === (int) $response->getStatusCode()) {
                $collaborator = (new CollaboratorTranslator())->toCollaboratorFromRepresentation(
                    $response->getEntity(),
                    $aCollaboratorClass
                );
            } else {
                throw new Exception(
                    'There was a problem requesting the user: ' . $anIdentity . ' in role: ' . $aRoleName . ' with resulting status: ' . $response->getStatus()
                );
            }

        } finally {
            return $collaborator;
        }
    }

    private function buildRequest(Tenant $aTenant, $anIdentity, $aRoleName)
    {
        $request = $this->client->createRequest($this->buildURLFor(static::$URL_TEMPLATE));

        $request->pathParameter('tenantId', $aTenant->id());
        $request->pathParameter('username', $anIdentity);
        $request->pathParameter('role', $aRoleName);

        return $request;
    }

    private function buildURLFor($aTemplate)
    {
        return static::$PROTOCOL . '://' . static::$HOST . ':' . static::$PORT . $aTemplate;
    }
}
