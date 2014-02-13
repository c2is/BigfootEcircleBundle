<?php

namespace Bigfoot\Bundle\EcircleBundle\Services;

use Camcima\Soap\Client;

/**
 * This class implements Ecircle. First, you have to edit the configuration file by adding your credentials. Then, you
 * have to create one class per Ecircle's Method with all parameters you need.
 *
 * Example : for the method SubscribeMemberByEmail you have to create the class SubscribeMemberByEmailOptions into the
 * Options Directory with the attributes : $email, $groupId, $session, $sendMessage.
 *
 * To use this client, you have to connect first with a scope (key of your credentials in case you have multiples
 * account) and then you'll be able to call your different methods.
 *
 * Example in a Symfony controller :
 *
 * $client = $this->get('bigfoot_ecircle.client')->connect('example_key');
 * $retour = $client->getUserByEmail('example@email.com');
 * $client->disconnect();
 *
 * @Author S.Huot s.huot@c2is.fr
 *
 */
class BigfootEcircleClient
{

    const OPTIONS_CLASS_PATTERN = 'Bigfoot\Bundle\EcircleBundle\Options\%sOptions';

    protected $sessionId = null;
    protected $client = null;
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function getClient()
    {
        if (!$this->client) {
            $ecircle = $this->container->getParameter('bigfoot_ecircle');
            $this->client = new Client($ecircle['client']['wsdl_url']);
        }
        
        return $this->client;
    }

    /**
     *
     * Get attributes from name class.
     *
     *
     * @param $name String Name of the class
     * @param null $scope String Key of the Ecircle account
     * @return mixed
     * @throws Exception
     */
    public function options($name, $scope = null, $method = 'parameter')
    {
        try {
            $optionsClassname = sprintf(self::OPTIONS_CLASS_PATTERN, $name);

            $options = new $optionsClassname;

            if ($scope) {
                if ($method == 'parameter') {
                    $ecircle = $this->container->getParameter('bigfoot_ecircle');
                    if (isset($ecircle['client']['request'][$scope])) {
                        foreach ($ecircle['client']['request'][$scope] as $attribute => $value) {
                            if (property_exists($options, $attribute)) {
                                $options->$attribute = $value;
                            }
                        }
                    }
                }
                else {
                    $em = $this->container->get('doctrine')->getManager();
                    $newsletter = $em->getRepository('SehBundle:Newsletter')->findOneByScope($scope);
                    if ($newsletter) {
                        $options->realm     = $newsletter->getRealm();
                        $options->user      = $newsletter->getUser();
                        $options->passwd    = $newsletter->getPassword();
                    }
                }
            }
            return $options;
        }
        catch (Exception $e) {
            throw new Exception(sprintf('Options %s does not exist', $name));
        }
    }

    /**
     *
     * Connect to Webservice
     *
     * @param $scope String Key of the Ecircle account
     * @return $this
     */
    public function connect($scope,$method = 'parameter')
    {
        $this->method = $method;
        $result = $this->getClient()->logon($this->options('Logon', $scope, $method));
        $this->sessionId = $result->logonReturn;

        return $this;
    }

    /**
     * Disconnect from Webservice
     *
     * @return $this
     */
    public function disconnect()
    {
        $logoutOptions = $this->options('Logout');
        $logoutOptions->session = $this->sessionId;

        $this->getClient()->logout($logoutOptions);

        $this->sessionId = null;

        return $this;
    }

    /**
     *
     * Retrieve user information by Email
     *
     * @param $email String Email of an user already registered in Ecircle
     * @return mixed
     * @throws Exception If the client is not found
     *
     */
    public function getUserByEmail($email, $groupId)
    {
        if (!$this->sessionId) {
            throw new Exception('Client no connected');
        }

        $lookupUserByEmailOptions             = $this->options('LookupUserByEmail');
        $lookupUserByEmailOptions->email      = $email;
        $lookupUserByEmailOptions->session    = $this->sessionId;
        $lookupUserByEmailOptions->groupId    = $groupId;

        $result = $this->getClient()->lookupMemberByEmail($lookupUserByEmailOptions);

        return $result;
    }

    /**
     *
     * Register an user into a Newsletter by Email
     *
     * @param $email Email of a new client
     * @param $groupId  Integer Id of the group in Ecircle
     * @return mixed
     * @throws Exception If the client is not found
     */
    public function subscribeMemberByEmail($email,$groupId, $sendMessage = false)
    {
        if (!$this->sessionId) {
            throw new Exception('Client no connected');
        }

        $subscribeMemberByEmailOptions = $this->options('SubscribeMemberByEmail');
        $subscribeMemberByEmailOptions->email   = $email;
        $subscribeMemberByEmailOptions->session = $this->sessionId;
        $subscribeMemberByEmailOptions->groupId = $groupId;
        $subscribeMemberByEmailOptions->sendMessage = $sendMessage;
        $result = $this->getClient()->subscribeMemberByEmail($subscribeMemberByEmailOptions);

        return $result;
    }

    /**
     *
     * Unregister an user into a Newsletter by Email
     *
     * @param $email Email of a new client
     * @param $groupId  Integer Id of the group in Ecircle
     * @return mixed
     * @throws Exception If the client is not found
     */
    public function unSubscribeMemberByEmail($email,$groupId, $sendMessage = false)
    {
        if (!$this->sessionId) {
            throw new Exception('Client no connected');
        }

        $unSubscribeMemberByEmailOptions = $this->options('UnSubscribeMemberByEmail');
        $unSubscribeMemberByEmailOptions->email   = $email;
        $unSubscribeMemberByEmailOptions->session = $this->sessionId;
        $unSubscribeMemberByEmailOptions->groupId = $groupId;
        $unSubscribeMemberByEmailOptions->sendMessage = $sendMessage;
        $result = $this->getClient()->unsubscribeMemberByEmail($unSubscribeMemberByEmailOptions);

        return $result;
    }
}
