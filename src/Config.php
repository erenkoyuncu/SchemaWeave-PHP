<?php
namespace SchemaWeave;

final class Config
{
    private array $values;

    public function __construct(array $values = [])
    {
        $defaults = [
            'enabled' => true,
            'base_url' => '',
            'site_name' => '',
            'default_language' => 'en-US',
            'home_name' => 'Home',
            'organization' => [],
            'locations' => [],
            'schemas' => [
                'organization' => true,
                'website' => true,
                'local_business' => true,
                'webpage' => true,
                'breadcrumb' => true,
                'product' => true,
                'blog_posting' => true,
                'item_list' => true,
                'faq' => true,
                'related' => true,
            ],
        ];

        $this->values = array_replace_recursive($defaults, $values);
        $this->values['base_url'] = rtrim((string) $this->values['base_url'], '/');
    }

    public function all(): array
    {
        return $this->values;
    }

    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }

    public function isEnabled(): bool
    {
        return !empty($this->values['enabled']);
    }

    public function schemaEnabled(string $schema): bool
    {
        $schemas = isset($this->values['schemas']) && is_array($this->values['schemas'])
            ? $this->values['schemas']
            : [];

        return !array_key_exists($schema, $schemas) || !empty($schemas[$schema]);
    }

    public function organization(): array
    {
        return is_array($this->values['organization']) ? $this->values['organization'] : [];
    }

    public function locations(): array
    {
        return is_array($this->values['locations']) ? $this->values['locations'] : [];
    }
}
