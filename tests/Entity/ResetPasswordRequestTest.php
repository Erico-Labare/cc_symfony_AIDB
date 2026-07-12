<?php

namespace App\Tests\Entity;

use App\Entity\Compte;
use App\Entity\ResetPasswordRequest;
use PHPUnit\Framework\TestCase;

class ResetPasswordRequestTest extends TestCase
{
    public function testCanCreateResetPasswordRequest(): void
    {
        $user = $this->createMock(Compte::class);
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $selector = 'someSelector';
        $hashedToken = 'someHashedToken';

        $request = new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->assertInstanceOf(ResetPasswordRequest::class, $request);
        $this->assertNull($request->getId()); // ID should be null before persisting
        $this->assertSame($user, $request->getUser());
        $this->assertSame($expiresAt->getTimestamp(), $request->getExpiresAt()->getTimestamp());
        $this->assertSame($selector, $request->getSelector());
        $this->assertSame($hashedToken, $request->getHashedToken());
    }

    public function testGetUser(): void
    {
        $user = $this->createMock(Compte::class);
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $selector = 'someSelector';
        $hashedToken = 'someHashedToken';

        $request = new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->assertSame($user, $request->getUser());
    }
}
