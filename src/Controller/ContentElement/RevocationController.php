<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Markocupic\ContaoAltchaAntispam\Controller\AltchaController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsContentElement('widerruf', category: 'includes')]
class RevocationController extends AbstractContentElementController
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    protected function getResponse($template, ContentModel $model, Request $request): Response
    {
        $uuid = trim((string) $request->query->get('uuid', ''));
        $locale = strtolower((string) $request->getLocale());
        $isGerman = str_starts_with($locale, 'de');

        $submitUrl = '/_widerruf';
        $altchaChallengeUrl = '/_contao_altcha_challenge';

        try {
            $submitUrl = $this->urlGenerator->generate('widerruf_submit');
        } catch (\Throwable) {
            // Fallback for stale route cache.
        }

        try {
            $altchaChallengeUrl = $this->urlGenerator->generate(AltchaController::class);
        } catch (\Throwable) {
            // Fallback for stale route cache.
        }

        $this->setTemplateValue($template, 'submit_url', $submitUrl);
        $this->setTemplateValue($template, 'altcha_challenge_url', $altchaChallengeUrl);
        $this->setTemplateValue($template, 'prefill_uuid', $uuid);
        $this->setTemplateValue($template, 'widerruf_locale', $isGerman ? 'de' : 'en');
        $this->setTemplateValue($template, 'widerruf_texts', $this->getTexts($isGerman));
        $this->setTemplateValue($template, 'success_message', (string) ($model->mgm_revocation_success_message ?: ($isGerman
            ? 'Vielen Dank. Dein Widerruf wurde übermittelt. Eine Bestätigung wurde per E-Mail gesendet.'
            : 'Thank you. Your revocation has been submitted. A confirmation has been sent by email.')));

        if (\is_object($template) && method_exists($template, 'getResponse')) {
            return $template->getResponse();
        }

        if (\is_object($template) && method_exists($template, 'parse')) {
            return new Response((string) $template->parse());
        }

        return new Response('');
    }

    private function setTemplateValue(object $template, string $key, mixed $value): void
    {
        if (method_exists($template, 'set')) {
            $template->set($key, $value);

            return;
        }

        $template->{$key} = $value;
    }

    private function getTexts(bool $isGerman): array
    {
        if ($isGerman) {
            return [
                'headline_default' => 'Widerrufsformular',
                'consumer_name' => 'Name des Verbrauchers',
                'contract_reference' => 'Vertragsangaben (Datum/Bestellnummer/Rechnungsnummer)',
                'confirmation_email' => 'E-Mail-Adresse für die Bestätigung',
                'submit' => 'Widerruf absenden',
                'altcha_missing' => 'Bitte Anti-Spam-Prüfung abschließen.',
                'submit_error' => 'Widerruf konnte nicht übermittelt werden.',
                'submit_success' => 'Widerruf erfolgreich übermittelt.',
            ];
        }

        return [
            'headline_default' => 'Revocation Form',
            'consumer_name' => 'Consumer name',
            'contract_reference' => 'Contract details (date/order number/invoice number)',
            'confirmation_email' => 'Email address for confirmation',
            'submit' => 'Submit revocation',
            'altcha_missing' => 'Please complete the anti-spam verification.',
            'submit_error' => 'Revocation could not be submitted.',
            'submit_success' => 'Revocation submitted successfully.',
        ];
    }
}
