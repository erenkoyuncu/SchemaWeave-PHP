<?php
namespace SchemaWeave;

/**
 * Lightweight structural validator for SchemaWeave-generated JSON-LD.
 *
 * This is intentionally not a replacement for Schema.org/Google validators.
 * It catches package-level integrity issues such as duplicate IDs and missing
 * core fields before output reaches external validation tools.
 */
final class GraphValidator
{
    public function validate(array $document): array
    {
        $issues = [];

        if (($document['@context'] ?? null) !== 'https://schema.org') {
            $issues[] = $this->issue('error', 'context.invalid', 'The JSON-LD document must use https://schema.org as @context.');
        }

        $graph = $document['@graph'] ?? null;
        if (!is_array($graph)) {
            $issues[] = $this->issue('error', 'graph.missing', 'The JSON-LD document must contain an @graph array.');
            return $issues;
        }

        $seenIds = [];
        foreach ($graph as $index => $entity) {
            if (!is_array($entity)) {
                $issues[] = $this->issue('error', 'entity.invalid', 'Graph item #' . ($index + 1) . ' is not an object.');
                continue;
            }

            $type = trim((string) ($entity['@type'] ?? ''));
            if ($type === '') {
                $issues[] = $this->issue('error', 'entity.type_missing', 'Graph item #' . ($index + 1) . ' has no @type.');
            }

            $id = trim((string) ($entity['@id'] ?? ''));
            if ($id !== '') {
                if (isset($seenIds[$id])) {
                    $issues[] = $this->issue('error', 'entity.id_duplicate', 'Duplicate @id detected: ' . $id);
                }
                $seenIds[$id] = true;
            }

            $issues = array_merge($issues, $this->validateEntity($entity, $index));
        }

        return $issues;
    }

    public function hasErrors(array $issues): bool
    {
        foreach ($issues as $issue) {
            if (($issue['severity'] ?? '') === 'error') {
                return true;
            }
        }

        return false;
    }

    private function validateEntity(array $entity, int $index): array
    {
        $issues = [];
        $type = (string) ($entity['@type'] ?? '');
        $prefix = $type !== '' ? $type : 'Graph item #' . ($index + 1);

        if (in_array($type, ['WebPage', 'AboutPage', 'ContactPage', 'CollectionPage', 'ProfilePage', 'SearchResultsPage'], true)) {
            $this->requireText($issues, $entity, 'name', $prefix);
            $this->requireUrl($issues, $entity, 'url', $prefix);
        }

        if ($type === 'Product') {
            $this->requireText($issues, $entity, 'name', $prefix);
            $this->requireUrl($issues, $entity, 'url', $prefix);

            if (isset($entity['offers'])) {
                $offers = $this->normalizeEntities($entity['offers']);
                foreach ($offers as $offer) {
                    $offerType = (string) ($offer['@type'] ?? '');
                    if (!in_array($offerType, ['Offer', 'AggregateOffer'], true)) {
                        $issues[] = $this->issue('warning', 'product.offer_type', 'Product offers should use @type Offer or AggregateOffer.');
                    }

                    if ($offerType === 'AggregateOffer') {
                        if (!isset($offer['lowPrice']) || !isset($offer['highPrice'])) {
                            $issues[] = $this->issue('error', 'product.aggregate_offer_range_missing', 'AggregateOffer is missing lowPrice or highPrice.');
                        }
                    } elseif (!isset($offer['price']) || trim((string) $offer['price']) === '') {
                        $issues[] = $this->issue('error', 'product.offer_price_missing', 'Product Offer is missing price.');
                    }

                    if (empty($offer['priceCurrency'])) {
                        $issues[] = $this->issue('warning', 'product.offer_currency_missing', 'Product Offer is missing priceCurrency.');
                    }
                }
            }

            if (isset($entity['aggregateRating']) && is_array($entity['aggregateRating'])) {
                if (!isset($entity['aggregateRating']['ratingValue'])) {
                    $issues[] = $this->issue('error', 'product.rating_value_missing', 'AggregateRating is missing ratingValue.');
                }
                if (!isset($entity['aggregateRating']['ratingCount']) && !isset($entity['aggregateRating']['reviewCount'])) {
                    $issues[] = $this->issue('warning', 'product.rating_count_missing', 'AggregateRating should include ratingCount or reviewCount.');
                }
            }
        }

        if ($type === 'BlogPosting') {
            $this->requireText($issues, $entity, 'headline', $prefix);
            $this->requireUrl($issues, $entity, 'url', $prefix);
        }

        if ($type === 'FAQPage') {
            $questions = $entity['mainEntity'] ?? [];
            if (!is_array($questions) || empty($questions)) {
                $issues[] = $this->issue('error', 'faq.empty', 'FAQPage must contain at least one Question.');
            } else {
                foreach ($questions as $question) {
                    if (!is_array($question)) {
                        continue;
                    }
                    if (($question['@type'] ?? '') !== 'Question' || trim((string) ($question['name'] ?? '')) === '') {
                        $issues[] = $this->issue('error', 'faq.question_invalid', 'FAQPage contains an invalid Question.');
                    }
                    $answer = $question['acceptedAnswer'] ?? null;
                    if (!is_array($answer) || trim((string) ($answer['text'] ?? '')) === '') {
                        $issues[] = $this->issue('error', 'faq.answer_invalid', 'FAQ Question is missing acceptedAnswer.text.');
                    }
                }
            }
        }

        if ($type === 'BreadcrumbList') {
            $items = $entity['itemListElement'] ?? [];
            if (!is_array($items) || empty($items)) {
                $issues[] = $this->issue('warning', 'breadcrumb.empty', 'BreadcrumbList has no list items.');
            }
        }

        return $issues;
    }

    private function requireText(array &$issues, array $entity, string $field, string $entityName): void
    {
        if (!isset($entity[$field]) || trim((string) $entity[$field]) === '') {
            $issues[] = $this->issue('error', 'field.missing', $entityName . ' is missing ' . $field . '.');
        }
    }

    private function requireUrl(array &$issues, array $entity, string $field, string $entityName): void
    {
        $value = trim((string) ($entity[$field] ?? ''));
        if ($value === '' || filter_var($value, FILTER_VALIDATE_URL) === false) {
            $issues[] = $this->issue('error', 'url.invalid', $entityName . ' has an invalid ' . $field . '.');
        }
    }

    private function normalizeEntities($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        if (isset($value['@type'])) {
            return [$value];
        }

        return array_values(array_filter($value, 'is_array'));
    }

    private function issue(string $severity, string $code, string $message): array
    {
        return [
            'severity' => $severity,
            'code' => $code,
            'message' => $message,
        ];
    }
}
