<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Twig\Environment;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DefaultController extends AbstractController
{
    private $client;
    private $baseUrl;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->baseUrl = $_SERVER['RPC_SERVER'] . '/json-rpc';

    }

    /**
     * @throws TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public function getHistory(int $limit = 10): array
    {
        $options = [
            'json' => [
                'jsonrpc' => '2.0',
                'method' => 'balance.history',
                'params' => ["limit" => $limit],
                'id'=> 2
            ]
        ];
        try {
            $response = $this->client->request(
                'POST',
                $this->baseUrl,
                $options
            );
        } catch (TransportExceptionInterface $e) {
            return ['error' => true];
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            return ['error' => true];
        }
        $content = $response->toArray();
        if (isset($content['result'])) {
            return ['result' => $content['result']];
        } else  {
            return ['error' => true];
        }

    }

    /**
     * @throws TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public function getBalance(int $userId): array
    {
        $options = [
            'json' => [
                'jsonrpc' => '2.0',
                'method' => 'balance.userBalance',
                'params' => ["user_id" => $userId],
                'id'=> 1
            ]
        ];
        try {
            $response = $this->client->request(
                'POST',
                $this->baseUrl,
                $options
            );
        } catch (TransportExceptionInterface $e) {
            return ['error' => true];
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            return ['error' => true];
        }
        $content = $response->toArray();
        if (isset($content['result'])) {
            return ['result' => $content['result']];
        } else  {
            return ['error' => true];
        }

    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(Environment $twig): Response
    {
        $userId = 10;
        $history = $this->getHistory(20);
        $userBalance = $this->getBalance($userId);

        return new Response(
            $twig->render(
                'index.html.twig',
                [
                    'history' => (isset($history['result'])) ? $history['result'] : false ,
                    'userBalance' => (!isset($userBalance['error'])) ? $userBalance['result'] : 'unable to load...' ,
                    'userId' => $userId,
                ]
            )
        );

    }
}
