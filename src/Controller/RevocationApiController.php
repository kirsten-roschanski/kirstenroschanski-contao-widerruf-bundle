<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\Controller;

use Doctrine\DBAL\Connection;
use Kirstenroschanski\ContaoWiderrufBundle\Checkout\RevocationService;
use Markocupic\ContaoAltchaAntispam\Altcha\AltchaValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RevocationApiController
{
    public function __construct(
        private readonly RevocationService $revocationService,
        private readonly AltchaValidator $altchaValidator,
        private readonly Connection $connection,
    ) {
    }

    #[Route('/_widerruf', name: 'widerruf_submit', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = json_decode((string) $request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

            $cteId = (int) ($payload['cte_id'] ?? 0);
            $altchaRequired = $this->isAltchaEnabledForCte($cteId);
            $altchaRaw = $payload['altcha'] ?? '';
            $altchaPayload = \is_array($altchaRaw)
                ? (string) ($altchaRaw['payload'] ?? '')
                : (string) $altchaRaw;

            $altchaPayload = trim($altchaPayload);

            if ($altchaRequired && ('' === $altchaPayload || !$this->altchaValidator->validate($altchaPayload))) {
                throw new \InvalidArgumentException('Anti-Spam-Prüfung fehlgeschlagen. Bitte erneut versuchen.');
            }

            unset($payload['altcha'], $payload['cte_id']);

            $result = $this->revocationService->submitRevocation((array) $payload);

            return new JsonResponse([
                'success' => true,
                'revocation_id' => $result['revocation_id'],
            ]);
        } catch (\Throwable $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    private function isAltchaEnabledForCte(int $cteId): bool
    {
        if ($cteId <= 0) {
            return true;
        }

        try {
            $value = $this->connection->fetchOne(
                'SELECT widerruf_enable_altcha FROM tl_content WHERE id = :id LIMIT 1',
                ['id' => $cteId]
            );

            return '1' === (string) $value;
        } catch (\Throwable) {
            return true;
        }
    }
}
