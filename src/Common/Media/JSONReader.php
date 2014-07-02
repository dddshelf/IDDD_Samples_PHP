<?php

namespace SaasOvation\Common\Media;

use Exception;
use RuntimeException;
use SaasOvation\Common\Serializer\AbstractSerializer;

class JSONReader extends AbstractSerializer
{
    public function deserialize($aSerialization)
    {
        $parser = new JsonParser();

        try {
            $object = $parser->parse($aSerialization)->getAsJsonObject();

            return $object;

        } catch (Exception $e) {
            echo $e->getTraceAsString();

            throw new RuntimeException($e);
        }
    }
}