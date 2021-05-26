<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DefaultController extends AbstractController
{
    private $client;
    private $baseUrl;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->baseUrl = $_SERVER['RPC_SERVER'] . '/json-rpc';

    }

    public function getHistory(int $limit = 10): array
    {
        $options = [
            'json' => [
                'jsonrpc' => '2.0',
                'method' => 'balance.history',
                'params' => ["limit0" => $limit],
                'id'=> 2
            ]
        ];
        $response = $this->client->request(
            'POST',
            $this->baseUrl,
            $options
        );

        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            return [];
        }
        $content = $response->toArray();
        if (isset($content['result'])) {
            return $content['result'];
        } else {
            return [];
        }

    }

    public function getBalance(int $userId): float
    {
        $options = [
            'json' => [
                'jsonrpc' => '2.0',
                'method' => 'balance.userBalance',
                'params' => ["user_id" => $userId],
                'id'=> 1
            ]
        ];
        $response = $this->client->request(
            'POST',
            $this->baseUrl,
            $options
        );

        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            return false;
        }
        $content = $response->toArray();
        if (isset($content['result'])) {
            return $content['result'];
        } else {
            return false;
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
        try {
            return new Response(
                $twig->render(
                    'index.html.twig',
                    [
                        'history' => ($history != []) ? $history : 'unable to load...' ,
                        'userBalance' => ($userBalance != false) ? $userBalance : 'unable to load...' ,
                        'userId' => $userId
                    ]
                )
            );
        } catch (LoaderError $e) {
        } catch (RuntimeError $e) {
        } catch (SyntaxError $e) {
        }
    }
}
