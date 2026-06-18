<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Markocupic\ContaoAltchaAntispam\Controller\AltchaController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Twig\Environment;

#[AsContentElement('widerruf', category: 'includes')]
class RevocationController extends AbstractContentElementController
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Environment $twig,
    ) {
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
        $this->setTemplateValue($template, 'widerruf_content_id', (int) ($model->id ?? 0));
        $this->setTemplateValue($template, 'altcha_enabled', '1' === (string) ($model->widerruf_enable_altcha ?? '1'));
        $this->setTemplateValue($template, 'widerruf_locale', $isGerman ? 'de' : 'en');
        $texts = $this->getTexts();
        $this->setTemplateValue($template, 'widerruf_texts', $texts);
        $modelData = $model->row();
        $configuredSuccessMessage = (string) (($modelData['widerruf_success_message'] ?? '') ?: ($modelData['mgm_revocation_success_message'] ?? ''));

        $this->setTemplateValue($template, 'success_message', '' !== $configuredSuccessMessage
            ? $configuredSuccessMessage
            : (string) ($texts['success_default'] ?? ''));

        if ($template instanceof FragmentTemplate) {
            return $template->getResponse();
        }

        if (is_object($template) && method_exists($template, 'parse')) {
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

    private function getTexts(): array
    {
        $texts = $GLOBALS['TL_LANG']['MGM_WIDERRUF'] ?? [];

        if (!\is_array($texts)) {
            return [];
        }

        return $texts;
    }
}
