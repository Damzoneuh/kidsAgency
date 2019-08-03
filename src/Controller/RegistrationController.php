<?php

namespace App\Controller;

use App\Entity\Img;
use App\Entity\Profile;
use App\Entity\Talents;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param \Swift_Mailer $mailer
     * @return Response
     * @throws \Exception
     * @Route("/register/{space}", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer, $space): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setIsAcceptGts(true);
            $user->setIsMailChecked(false);
            $user->setIsValidate(false);
            $user->setIsPaid(false);
            $user->setDeadline(new \DateTime('now'));
            $user->setCreatedAt(new \DateTime('now'));
            $user->setUpdatedAt(new \DateTime('now'));
            $token = self::generateToken();
            $user->setResetKey($token);
            if ($space == 'recruiter'){
                $user->setRoles(['ROLE_RECRUITER']);
            }
            else{
                $user->setRoles(['ROLE_CANDIDATE']);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $message = new \Swift_Message();
            $message->setTo($user->getEmail());
            $message->setFrom($this->getParameter('noreply.address'));
            $message->setSubject('Inscription kids agency');
            $message->setBody('
                <div>
                    <h1>Bienvenue !</h1>
                    <p>Vous venez de vous inscrire sur kids agency et nous vous en remerçion</p>
                    <p>Afin de valider votre inscription nous vous invitons à cliquer sur le lien ci-dessous</p>
                    <p><a href="' . $this->getParameter('app.url') . '/registration/confirmation/' . $token . '">Confirmer mon inscription</a> </p>
                </div>
            ', 'text/html');
            $mailer->send($message);

            return $this->redirectToRoute('index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     * @Route("/registration/consfirmation/{token}", name="user_confirm_registration")
     */
    public function confirmRegistration(Request $request, $token){
        //TODO finir l'inscription après checkout
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['reset_key' => $token]);
        if (null !== $user){
            $talents = $em->getRepository(Talents::class)->findAll();
            $form = $this->createFormBuilder();
            $form->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true
            ])
                ->add('lastname', TextType::class, [
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])
                ->add('description', TextareaType::class, [
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'required' => false
                ]);
            foreach ($talents as $talent){
                $form->add($talent->getTitle(), CheckboxType::class, [
                    'attr' => [
                        'class' => 'form-check-input'
                    ]
                ]);
            }
            $form->add('photo', FileType::class, [
                'attr' => [
                    'class' => 'form-control-file'
                ]
            ])
                ->add('submit', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-group btn-primary'
                    ]
                ]);
            $profileForm = $form->getForm();
            $profileForm->handleRequest($request);
            if ($profileForm->isSubmitted() && $profileForm->isValid()){
                $data = $profileForm->getData();
                /** @var $photo UploadedFile */
                $photo = $data['photo'];
                $profile = new Profile();
                $img = new Img();
                $profile->setLastname($data['lastname']);
                $profile->setFirstname($data['name']);
                $profile->setDescription($data['description']);
                $mime = $photo->getClientMimeType();
                if ($mime == 'image/jpeg' || $mime == 'image/png' || $mime == 'image/gif'){
                    $uniq = self::generateToken();
                    $photo->move($this->getParameter('img.path'), $uniq . '.' . $photo->getClientOriginalExtension());
                    $img->setPath($this->getParameter('img.path') . '/' . $uniq . '.' . $photo->getClientOriginalExtension());
                    $img->setTitle($uniq);
                    $em->persist($img);
                    $em->flush();
                    $profile->addImg($img);
                }
                else{
                    return $this->redirectToRoute('user_confirm_registration', ['token' => $token]);
                }
                foreach ($talents as $talent){
                    if (true === $data[$talent->getTitle()]){
                        $profile->addTalent($em->getRepository(Talents::class)->findOneBy(['title' => $talent->getTitle()]));
                    }
                }
                $em->persist($profile);
                $em->flush();
                $user->setProfile($profile);
                $em->flush();
                return $this->redirectToRoute('index');
            }
            return $this->render('registration/profile.html.twig', ['form' => $profileForm->createView()]);
        }
        return $this->render('registration/error.html.twig');
    }
    /**
     * @return String
     * @throws \Exception
     */
    protected function generateToken() : String {
        return bin2hex(random_bytes(10));
    }
}
