<?php

namespace App\Security;

use App\Entity\Benutzer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        $mmResponse = self::mmCheck($credentials);
        if($mmResponse['exit_code']!="success")
        {
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }
        else
        {
            $user = $this->entityManager->getRepository(Benutzer::class)->findOneBy(['email' => $credentials['email']]);
            if (!$user) //No user in DB, create new
            {
                $user = new Benutzer();
                $user->setEmail($mmResponse['user']->email);
                $this->entityManager->persist($user);
            }
            if($user->getVorname()!= $mmResponse['user']->first_name)$user->setVorname($mmResponse['user']->first_name);
            if($user->getNachname()!= $mmResponse['user']->last_name)$user->setNachname($mmResponse['user']->last_name);
            $this->entityManager->flush();
        }
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Check the user's password or other credentials and return true or false
        // If there are no credentials to check, you can just return true
        return true;
        throw new \Exception('TODO: check the credentials inside '.__FILE__);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        return new RedirectResponse($this->urlGenerator->generate('food'));
        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('app_login');
    }

    public function mmCheck($credentials)
    {
        $curl = curl_init();

        $email = (strpos($credentials['email'], "@"))? $credentials['email'] : $credentials['email']. "@meeva.de";
        $payload = json_encode(array(
            'login_id' => $email,
            'password' => $_POST['password']
        ));

// Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://mattermost.meeva.de/api/v4/users/login',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $payload
        ));

// Send the request & save response to $resp
        $resp = curl_exec($curl);

        $response = json_decode($resp);
        if(!empty($response->status_code))
        {
            if ($response->status_code == '400')
            {
                // Kein Konto mit diesen Daten vorhanden
                return ["exit_code"=>"Kein Konto"];
            }else if ($response->status_code == '401')
            {
                // Passwort ist falsch
                return ["exit_code"=>"Passwort falsch"];
            }

        }else if ($response->first_name == '' && $response->last_name == '')
        {
            // Trage deinen Vor- oder Nachnamen bei Mattermost ein
            return ["exit_code"=>"Namen fehlen"];
        }else{
            //checkUserDB($response);
            //loginUserDB($response);
            return ["exit_code"=>"success", "user"=>$response];
        }
        curl_close($curl);
    }
}
