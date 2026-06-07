<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class SecurityController extends AbstractController
{
    #[Route('/login-screen', name: 'app_login_screen', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('security/login.html.twig');
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Si el código llega aquí, es porque el firewall de Symfony
        // ya validó al usuario con éxito.
        return new JsonResponse(['success' => true, 'message' => 'LOGIN EXITOSO']);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $username = $request->request->get('reg-username');
        $email = $request->request->get('reg-email');
        $password = $request->request->get('reg-password');

        // 1. Validar que no falten datos esenciales en el envío
        if (!$username || !$email || !$password) {
            return new JsonResponse([
                'success' => false,
                'message' => 'DATOS DE TELEMETRÍA INCOMPLETOS'
            ], Response::HTTP_BAD_REQUEST);
        }

        // 2. Verificar si el ALIAS (Username) ya existe
        $existingUser = $em->getRepository(Usuario::class)->findOneBy(['username' => $username]);
        if ($existingUser) {
            return new JsonResponse([
                'success' => false,
                'message' => 'ALIAS YA REGISTRADO EN LA PARRILLA'
            ], Response::HTTP_BAD_REQUEST);
        }

        // 3. Verificar si el ENLACE (Email) ya existe (¡Evita la colisión en SQL!)
        $existingEmail = $em->getRepository(Usuario::class)->findOneBy(['email' => $email]);
        if ($existingEmail) {
            return new JsonResponse([
                'success' => false,
                'message' => 'ESTE CORREO YA TIENE UNA LICENCIA ACTIVA'
            ], Response::HTTP_BAD_REQUEST);
        }

        // 4. Crear el Piloto en la Base de Datos de manera segura
        $usuario = new Usuario();
        $usuario->setUsername($username);
        $usuario->setEmail($email);

        // Encriptar la contraseña de forma segura
        $hashedPassword = $passwordHasher->hashPassword($usuario, $password);
        $usuario->setPassword($hashedPassword);

        try {
            $em->persist($usuario);
            $em->flush(); // Guarda físicamente en MySQL
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'FALLO CRÍTICO AL GUARDAR EN BOXES: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'LICENCIA FIA EMITIDA CORRECTAMENTE'
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // Este método nunca se ejecuta, el firewall de seguridad 
        // intercepta la petición automáticamente.
        throw new \LogicException('Este código no debería ser alcanzado nunca.');
    }
}