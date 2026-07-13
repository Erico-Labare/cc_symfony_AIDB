<?php

namespace App\Tests\Entity;

use App\Entity\Compte;
use App\Entity\ResetPasswordRequest;
use PHPUnit\Framework\TestCase;

/**
 * Teste l'entité ResetPasswordRequest.
 *
 * Cette classe contient les tests unitaires pour vérifier le comportement de l'entité ResetPasswordRequest,
 * y compris la création et la récupération des propriétés.
 */
class ResetPasswordRequestTest extends TestCase
{
    /**
     * Teste la création d'une instance de ResetPasswordRequest.
     */
    public function testCanCreateResetPasswordRequest(): void
    {
        $user = $this->createMock(Compte::class);
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $selector = 'someSelector';
        $hashedToken = 'someHashedToken';

        $request = new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->assertInstanceOf(ResetPasswordRequest::class, $request);
        $this->assertNull($request->getId()); // L'ID doit être null avant la persistance
        $this->assertSame($user, $request->getUser());
        $this->assertSame($expiresAt->getTimestamp(), $request->getExpiresAt()->getTimestamp());
        $this->assertSame($selector, $request->getSelector());
        $this->assertSame($hashedToken, $request->getHashedToken());
    }

    /**
     * Teste la méthode getUser().
     */
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
