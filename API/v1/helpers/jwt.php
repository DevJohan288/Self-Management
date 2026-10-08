<?php
// Minimal JWT helper (HS256) - no external libs
function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data)
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $data .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_encode($payload, $secret, $expSeconds = 3600)
{
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload['iat'] = time();
    $payload['exp'] = time() + $expSeconds;

    $segments = [];
    $segments[] = base64url_encode(json_encode($header));
    $segments[] = base64url_encode(json_encode($payload));

    $signing_input = implode('.', $segments);
    $signature = hash_hmac('sha256', $signing_input, $secret, true);
    $segments[] = base64url_encode($signature);

    return implode('.', $segments);
}

function jwt_decode($token, $secret)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    list($bh, $bp, $bs) = $parts;
    $header = json_decode(base64url_decode($bh));
    $payload = json_decode(base64url_decode($bp));
    $signature = base64url_decode($bs);

    $valid = hash_hmac('sha256', $bh . '.' . $bp, $secret, true);
    if (!hash_equals($valid, $signature)) return null;

    if (isset($payload->exp) && time() > $payload->exp) return null;

    return $payload;
}
