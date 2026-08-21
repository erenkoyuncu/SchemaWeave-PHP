<?php
namespace SchemaWeave;

final class SchemaGraph
{
    private array $graph;

    public function __construct(array $graph)
    {
        $this->graph = array_values($graph);
    }

    public function toArray(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => $this->graph,
        ];
    }


    public function validate(): array
    {
        return (new GraphValidator())->validate($this->toArray());
    }

    public function isValid(): bool
    {
        $validator = new GraphValidator();
        $issues = $validator->validate($this->toArray());
        return !$validator->hasErrors($issues);
    }

    public function toJson(int $flags = 0): string
    {
        $defaultFlags = JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT;
        $json = json_encode($this->toArray(), $defaultFlags | $flags);
        return $json === false ? '{}' : $json;
    }

    public function toScriptTag(bool $pretty = true): string
    {
        $flags = $pretty ? JSON_PRETTY_PRINT : 0;
        return '<script type="application/ld+json">' . $this->toJson($flags) . '</script>';
    }
}
