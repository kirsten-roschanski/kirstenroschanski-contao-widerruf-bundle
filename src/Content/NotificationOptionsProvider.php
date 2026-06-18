<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\Content;

use Contao\Database;

class NotificationOptionsProvider
{
    /**
     * @return array<int, string>
     */
    public function getFormSubmitNotifications(): array
    {
        return $this->getNotificationsByType('widerruf_form_submit');
    }

    /**
     * @return array<int, string>
     */
    public function getStatusChangeNotifications(): array
    {
        return $this->getNotificationsByType('widerruf_status_change');
    }

    /**
     * @return array<int, string>
     */
    private function getNotificationsByType(string $type): array
    {
        $options = [];
        $result = Database::getInstance()
            ->prepare('SELECT id, title FROM tl_nc_notification WHERE type = ? ORDER BY title')
            ->execute($type);

        while ($result->next()) {
            $options[(int) $result->id] = (string) $result->title;
        }

        return $options;
    }
}
