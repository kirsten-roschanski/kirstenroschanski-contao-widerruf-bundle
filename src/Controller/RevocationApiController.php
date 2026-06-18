<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\Controller;

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
    ) {
    }

    #[Route('/_widerruf', name: 'widerruf_submit', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = json_decode((string) $request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

            $altchaRaw = $payload['altcha'] ?? '';
            $altchaPayload = \is_array($altchaRaw)
                ? (string) ($altchaRaw['payload'] ?? '')
                : (string) $altchaRaw;

            $altchaPayload = trim($altchaPayload);

            if ('' === $altchaPayload || !$this->altchaValidator->validate($altchaPayload)) {
                throw new \InvalidArgumentException('Anti-Spam-Prüfung fehlgeschlagen. Bitte erneut versuchen.');
            }

            unset($payload['altcha']);

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
}
