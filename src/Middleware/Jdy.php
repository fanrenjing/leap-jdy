<?php
namespace Leap\Jdy\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Jdy
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->check($request);

        return $next($request);
    }

    /**
     * @param $request
     * @return bool
     */
    private function check($request): bool
    {
        $timestamp = $request->get('timestamp');
        $nonce = $request->get('nonce');

        if (!$timestamp) {
            Log::warning('[Jdy] check no timestamp', [
                'request_param' => $request->all(),
                'header' => $request->header(),
            ]);
            throw new HttpException(Response::HTTP_FORBIDDEN, 'no timestamp');
        }

        if ($this->verifyIsExpire($timestamp)) {
            Log::warning('[Jdy] check expire timestamp', [
                'request_param' => $request->all(),
                'header' => $request->header(),
            ]);
            throw new HttpException(Response::HTTP_FORBIDDEN, 'expire timestamp');
        }

        if (!$nonce) {
            Log::warning('[Jdy] check no nonce', [
                'request_param' => $request->all(),
                'header' => $request->header(),
            ]);
            throw new HttpException(Response::HTTP_FORBIDDEN, 'no nonce');
        }

        $signature = $request->header('x-jdy-signature');
        $deliverid = $request->header('x-jdy-deliverid');

        if (!$signature) {
            Log::warning('[Jdy] check no auth signature', [
                'request_param' => $request->all(),
                'header' => $request->header(),
            ]);
            throw new HttpException(Response::HTTP_FORBIDDEN, 'no auth signature');
        }

        if (!$deliverid) {
            Log::warning('[Jdy] check no auth deliverid', [
                'request_param' => $request->all(),
                'header' => $request->header(),
            ]);
            throw new HttpException(Response::HTTP_FORBIDDEN, 'no auth deliverid');
        }

        $payload = $request->getContent();
        $content = $this->getSignature($nonce, $payload, $timestamp);
        if (0 !== strcmp($signature, $content)) {
            Log::warning('[Jdy] check sign verify failed', [
                'request_param' => $request->all(),
                'header' => $request->header(),
                'payload' => $payload,
                'php_input' => file_get_contents("php://input"),
            ]);
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'sign verify failed');
        }

        return true;
    }

    /**
     * @param $timestamp
     * @return bool
     */
    private function verifyIsExpire($timestamp): bool
    {
        $expire = config('jdy.expire');
        if ($expire > 0 && time() - $timestamp > $expire) {
            return true;
        }
        return false;
    }

    /**
     * @param string $nonce
     * @param string $payload
     * @param int $timestamp
     * @return string
     */
    private function getSignature (string $nonce, string $payload, int $timestamp): string
    {
        $secret = config('jdy.secret');
        $content = $nonce . ':' . $payload . ':' . $secret . ':' . $timestamp;
        return sha1($content);
    }

}