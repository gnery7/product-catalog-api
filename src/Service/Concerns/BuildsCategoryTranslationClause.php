<?php

namespace Contatoseguro\TesteBackend\Service\Concerns;

trait BuildsCategoryTranslationClause
{
    private function buildCategoryTranslationClause(?string $lang): array
    {
        if ($lang === null) {
            return [
                'titleExpr' => 'c.title',
                'join' => '',
                'binds' => [],
            ];
        }

        return [
            'titleExpr' => 'COALESCE(ct.label, c.title)',
            'join' => ' LEFT JOIN category_translation ct ON ct.category_id = c.id AND ct.lang_code = :lang',
            'binds' => [':lang' => $lang],
        ];
    }
}
