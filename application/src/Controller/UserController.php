<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\BrochureType;
use App\Form\UserFormType;
use App\Manager\ProductManager;
use App\Manager\UserFileManager;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Annotation;


class UserController extends AbstractController
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var BrochureType
     */
    private $productType;

    /**
     * @var ProductManager
     */
    private $productManager;

    /**
     * @var UserFileManager
     */
    private $userFileManager;

    /**
     * @param UserManager $userManager
     * @param FormFactoryInterface $formFactory
     * @param BrochureType $productType
     * @param ProductManager $productManager
     * @param UserFileManager $userFileManager
     */
    public function __construct(
        UserManager $userManager,
        FormFactoryInterface $formFactory,
        BrochureType $productType,
        ProductManager $productManager,
        UserFileManager $userFileManager
    )

    {
        $this->userManager = $userManager;
        $this->formFactory = $formFactory;
        $this->productType = $productType;
        $this->productManager = $productManager;
        $this->userFileManager = $userFileManager;
    }

    /**
     * @Route ("/", name="user", methods={"GET"})
   */
    public function loadAllUserAction()
    {
        /*try {
            echo 'Controller1';
            $tabUser = $this->userManager->loadAllUser();
            echo 'Controller2';

        } catch (NotFoundHttpException $exception) {
            return new JsonResponse(['error_message' =>
                $exception->getMessage()],
                $exception->getStatusCode());
        }
        */

        return $this->render('base.html.twig',[]);
    }

    /**
     * @Route ("/user/{id}", name="load_user", methods={"GET"})
     *
     * @param string $id
     *
     * @return JsonResponse
     */

    public function loadUserAction($id)
    {
        try {
            $result = $this->userManager->loadUser($id);

        } catch (NotFoundHttpException $exception) {

            return new JsonResponse(['error_message' =>
                $exception->getMessage()],
                $exception->getStatusCode());
        }
        return new JsonResponse($result);
    }


    /**
     * @Route ("/{id}", name="user_delete", methods={"DELETE"})
     * @param $id
     * @return JsonResponse
     */
    public function deleteUserAction($id)
    {
        /** @var User $user */
        try {
            $this->userManager->deleteUser($id);
        } catch (NotFoundHttpException $exception) {

            return new JsonResponse(['error_message' =>
                $exception->getMessage()],
                $exception->getStatusCode());
        }

        return new JsonResponse();
    }


    /**
     * @Route ("/user", name="create_user")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function createUserAction(Request $request)
    {
        /**je crée mon formlaire a partir de ma class UserFormType
         * Je le soumette et lui envoye ma request
         * true comme 2param pour qu'il me renvoie un tab associatif*/

        $form = $this
            ->formFactory->create(UserFormType::class);

        $form
            ->submit(json_decode($request->getContent(), true));

        $data = $form->getData();

        if (!$form->isValid()) {
            return new JsonResponse([(string)$form->getErrors(true)], 400);
        }
        dump($request->request);
        $id = uniqid();
        $this->userManager->createUser(
            $id,
            $data['firstName'],
            $data['lastName'],
            new \DateTime($data['birthday'])
        );

        dump($data);
        return new JsonResponse(array_merge(['id' => $id], $data));
    }

    /**
     * @Route("/modify/{id}", name="modify_user")
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */

    public function modifyUserAction(Request $request, $id)
    {
        $form = $this->formFactory->create(UserFormType::class);

        $form->submit(json_decode($request->getContent(), true));

        $data = $form->getData();

        if (!$form->handleRequest($request)->isValid()) {

            return new JsonResponse([(string)
            $form->getErrors(true)], 400);
        }


        $this->userManager->modifyUser($id,
            $data['firstName'],
            $data['lastName'],
            new \DateTime($data['birthday']));

        return new JsonResponse(array_merge(['id' => $id], $data));
    }

    /**
     * @Route ("/download", name="download_file", methods={"GET"})
     */
    public function downloadAction()
    {
        $path = __DIR__ . '/../../var/cache/userCSV.csv';

        $this->userFileManager->create($path);

        return $this->file($path);
    }


    /**
     * @Route ("/file", name="download_file", methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadFile(Request $request)
    {
        $form = $this->formFactory->create(BrochureType::class);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return new JsonResponse([(string)$form->getErrors(true)], 400);
        }

        return new JsonResponse();
    }

}
