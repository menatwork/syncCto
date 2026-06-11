<?php

namespace MenAtWork\SyncCto\EventListener;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Input;

#[AsHook('parseBackendTemplate')]
class TurboFormFixListener
{
    public function __invoke(string $buffer, string $template): string
    {
        // Turbo was introduced in Contao 5.4 — no fix needed below that
        if (version_compare(ContaoCoreBundle::getVersion(), '5.4', '<')) {
            return $buffer;
        }

        if (!str_starts_with($template, 'dcbe_general_')) {
            return $buffer;
        }

        $table = Input::get('table') ?? '';
        if (!str_starts_with($table, 'tl_syncCto') && !str_starts_with($table, 'tl_synccto')) {
            return $buffer;
        }

        return str_replace('<form ', '<form data-turbo="false" ', $buffer);
    }
}
