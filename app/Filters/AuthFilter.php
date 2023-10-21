<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require APPPATH . 'Helpers/helpers.php';
class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    private function SECRET_KEY(): string
    {
        return formatSecretKey(getenv('encryption.key'));
    }
    public function before(RequestInterface $request, $arguments = null)
    {
        $key = self::SECRET_KEY();
        $header = $request->getHeaderLine("Authorization");
        $token = null;
        if (!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }
        if (is_null($token) || empty($token)) {
            $response = service('response');
            return $response->setStatusCode(401)->setJSON([
                'error' => true,
                'message' => 'Informe o Token!',
            ]);
        }
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
        } catch (ExpiredException $ex) {
            $response = service('response');
            return $response->setStatusCode(401)->setJSON([
                'error' => true,
                'message' => 'Token expirado!',
            ]);
        } catch (Exception $ex) {
            $response = service('response');
            return $response->setStatusCode(401)->setJSON([
                'error' => true,
                'message' => 'Token inválido!',
            ]);
        }
    }


    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
