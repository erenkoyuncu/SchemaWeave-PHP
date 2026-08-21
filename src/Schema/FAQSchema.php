<?php
namespace SchemaWeave\Schema;

use SchemaWeave\Support\Text;

final class FAQSchema
{
    public static function build(array $rows, string $pageUrl, string $language): ?array
    {
        $questions = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $question = Text::clean($row['question'] ?? ($row['name'] ?? ''), 500);
            $answer = Text::clean($row['answer'] ?? ($row['text'] ?? ''), 0);
            if ($question === '' || $answer === '') {
                continue;
            }
            $questions[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if (empty($questions)) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            '@id' => $pageUrl . '#faq',
            'url' => $pageUrl,
            'mainEntity' => $questions,
            'inLanguage' => $language,
        ];
    }
}
