<?php


use EdgeAuth\DigestTokens;
use PHPUnit\Framework\TestCase;

final class WhenVerifyingABadToken extends TestCase
{
    private $token = 'DIGEST:bad-token';

    public function testFailsToVerify()
    {
        $result = (new DigestTokens())->verifyAndDecode('bad-secret', $this->token);
        $this->assertFalse($result->verified);
        $this->assertEquals('bad-token', $result->code);
        $this->assertFalse(isset($result->value));
    }
}