<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    /**
     * @Route("/api/me")
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function apiMe()
    {
        /*
         * Por defecto, cuando llamas a $this->json(), pasa los datos a la claseJsonResponse de Symfony.
         * Y entonces esa clase llama a la función json_encode() de PHP en nuestro objeto User.
         * En PHP, a menos que hagas un trabajo extra, cuando pasas un objeto ajson_encode(),
         * lo único que hace es incluir las propiedades pública --> SERIALIZER
         */
        return $this->json($this->getUser(), 200, [], [
            'groups' => ['user:read']
        ]);
    }
}
