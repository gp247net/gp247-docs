<?php
/**
 * Generate the REST API v2 reference pages of gp247-docs from api/openapi.json.
 *
 * Writes api/api-reference-front{_vi,,_ja}.md and api/api-reference-admin{_vi,,_ja}.md:
 * plain Markdown (no HTML), rendered by gp247.net like every other documentation page.
 * The English text comes from the spec; Vietnamese and Japanese come from
 * api/reference-i18n/{vi,ja}.json, a map "English text" => "translation". A missing
 * translation stops the run; a translation no longer used is reported as stale.
 *
 * Usage (from the gp247-docs root):
 *   php tools/openapi-to-markdown.php           write the pages
 *   php tools/openapi-to-markdown.php --check   write nothing; exit 1 when a page is stale
 *                                               or a translation is missing / unused
 *
 * Plain PHP, no Composer packages, no network.
 *
 * @aidlc-unit compat-foundation
 * @aidlc-story US-CMP-api-browsable-reference
 * @aidlc-adr compat-foundation_api-reference-viewer
 */

declare(strict_types=1);

const API_DIR = __DIR__ . '/../api';

/** Languages: code => [file suffix, label in the language bar, flag, "current" marker, footer]. */
const LANGUAGES = [
    'vi' => ['_vi', 'Tiếng Việt', '🇻🇳', ' (hiện tại)', '<sub>📅 **Cập nhật lần cuối:** %s · ✍️ **Tác giả (Author):** GP247</sub>'],
    'en' => ['', 'English', '🇬🇧', ' (current)', '<sub>📅 **Last updated:** %s · ✍️ **Author:** GP247</sub>'],
    'ja' => ['_ja', '日本語', '🇯🇵', '（現在）', '<sub>📅 **最終更新:** %s · ✍️ **作成者 (Author):** GP247</sub>'],
];

/** Page groups: base name => [tag prefix, path prefix]. */
const GROUPS = [
    'api-reference-front' => ['Front · ', '/api/v2/front/'],
    'api-reference-admin' => ['Admin · ', '/api/v2/core/'],
];

/** Envelope schemas that wrap a resource; never shown as a data object of their own. */
const ENVELOPES = ['ListEnvelope', 'ListMeta', 'ListLinks', 'Error'];

/** HTTP methods of an OpenAPI path item, in page order. */
const METHODS = ['get', 'post', 'put', 'patch', 'delete'];

/**
 * Translation of one English text into the page language (English returns itself).
 *
 * @param string $text English source text.
 * @return string
 */
function t(string $text): string
{
    global $lang, $dict, $used, $missing;
    if ($lang === 'en' || $text === '') {
        return $text;
    }
    $used[$lang][$text] = true;
    if (!array_key_exists($text, $dict[$lang])) {
        $missing[$lang][$text] = true;
        return $text;
    }
    return $dict[$lang][$text];
}

/**
 * Heading id as GitHub and gp247.net build it (lower case, punctuation dropped, space => "-").
 *
 * @param string $heading
 * @return string
 */
function heading_id(string $heading): string
{
    $text = mb_strtolower(str_replace(['`', '*'], '', trim($heading)));
    $text = (string) preg_replace('/[^\p{L}\p{M}\p{N}\s_-]/u', '', $text);
    return str_replace(' ', '-', $text);
}

/**
 * Text made safe for a Markdown table cell or a line of prose: no HTML can form, no cell breaks.
 *
 * @param string $text
 * @return string
 */
function cell(string $text): string
{
    return str_replace(['|', '<', '>', "\r", "\n"], ['\\|', '&lt;', '&gt;', ' ', ' '], trim($text));
}

/**
 * Resolve a local $ref ("#/components/schemas/Product") to [name, schema].
 *
 * @param array  $spec
 * @param string $ref
 * @return array{0: string, 1: array}
 */
function resolve(array $spec, string $ref): array
{
    $name = basename($ref);
    return [$name, $spec['components']['schemas'][$name] ?? []];
}

/**
 * Human type of a schema node, linking referenced data objects.
 *
 * @param array $node
 * @return string
 */
function type_of(array $node): string
{
    if (isset($node['$ref'])) {
        $name = basename($node['$ref']);
        return '[' . $name . '](#' . heading_id($name) . ')';
    }
    $type = $node['type'] ?? 'object';
    if (is_array($type)) {
        $type = implode(' \| ', $type);
    }
    if (($node['type'] ?? null) === 'array' || (is_array($node['type'] ?? null) && in_array('array', $node['type'], true))) {
        return t('list of') . ' ' . type_of($node['items'] ?? []);
    }
    return '`' . $type . '`' . (isset($node['format']) ? ' (' . $node['format'] . ')' : '');
}

/**
 * Example value of a schema node (follows $ref, one item per list).
 *
 * @param array $spec
 * @param array $node
 * @param int   $depth
 * @return mixed
 */
function example(array $spec, array $node, int $depth = 0)
{
    if (array_key_exists('example', $node)) {
        return $node['example'];
    }
    if (isset($node['$ref'])) {
        return $depth > 3 ? new stdClass() : example($spec, resolve($spec, $node['$ref'])[1], $depth + 1);
    }
    if (isset($node['allOf'])) {
        $merged = [];
        foreach ($node['allOf'] as $part) {
            $value = example($spec, $part, $depth);
            $merged = array_merge($merged, is_array($value) ? $value : (array) $value);
        }
        return $merged;
    }
    $type = is_array($node['type'] ?? null) ? $node['type'][0] : ($node['type'] ?? 'object');
    if ($type === 'array') {
        return [example($spec, $node['items'] ?? [], $depth + 1)];
    }
    if ($type === 'object' || isset($node['properties'])) {
        $out = [];
        foreach ($node['properties'] ?? [] as $field => $child) {
            if (!($child['x-gp247-include'] ?? false)) {
                $out[$field] = example($spec, $child, $depth + 1);
            }
        }
        return $out ?: new stdClass();
    }
    return ['integer' => 0, 'number' => 0, 'boolean' => false][$type] ?? '';
}

/**
 * The success response of an operation: [status, schema node or null].
 *
 * @param array $operation
 * @return array{0: string, 1: ?array}
 */
function success(array $operation): array
{
    foreach ($operation['responses'] ?? [] as $status => $response) {
        if ((int) $status >= 200 && (int) $status < 300) {
            return [(string) $status, $response['content']['application/json']['schema'] ?? null];
        }
    }
    return ['200', null];
}

/**
 * What the success body carries: [kind ("list"|"single"|"object"), data object name or null].
 *
 * @param array      $spec
 * @param array|null $schema
 * @return array{0: string, 1: ?string}
 */
function body_kind(array $spec, ?array $schema): array
{
    if ($schema === null) {
        return ['object', null];
    }
    $parts = $schema['allOf'] ?? [$schema];
    $list = false;
    foreach ($parts as $part) {
        if (($part['$ref'] ?? '') === '#/components/schemas/ListEnvelope') {
            $list = true;
        }
        $data = $part['properties']['data'] ?? null;
        if ($data !== null) {
            if (isset($data['items']['$ref'])) {
                return [$list ? 'list' : 'array', basename($data['items']['$ref'])];
            }
            if (isset($data['$ref'])) {
                return [$list ? 'list' : 'single', basename($data['$ref'])];
            }
        }
    }
    return ['object', null];
}

/**
 * Who may call an operation, as one translated sentence.
 *
 * @param array  $operation
 * @param string $prefix Path prefix of the group.
 * @return string
 */
function sign_in(array $operation, string $prefix): string
{
    $schemes = [];
    foreach ($operation['security'] ?? [] as $alternative) {
        $schemes[] = array_keys($alternative);
    }
    $flat = array_merge([], ...$schemes);
    $bearer = in_array('bearerAuth', $flat, true);
    $anonymous = in_array([], $schemes, true) || $schemes === [] || $flat === ['apiConnection', 'apiKey'];
    $cart = in_array('cartToken', $flat, true);
    if ($prefix === '/api/v2/core/') {
        return $bearer ? t('Admin token (Bearer).') : t('None — this call signs in.');
    }
    if ($cart) {
        return t('Customer token (Bearer), or the cart token of a guest cart.');
    }
    if ($bearer && !$anonymous) {
        return t('Customer token (Bearer).');
    }
    return $bearer ? t('Optional — a customer token (Bearer) adds the customer\'s own data.') : t('None — public.');
}

/**
 * Markdown table of a schema's own properties (nested data objects are linked, not inlined).
 *
 * @param array $schema
 * @return string[] Lines.
 */
function field_table(array $schema): array
{
    $required = $schema['required'] ?? [];
    $lines = ['| ' . t('Field') . ' | ' . t('Type') . ' | ' . t('Meaning') . ' |', '|---|---|---|'];
    foreach ($schema['properties'] ?? [] as $field => $node) {
        $notes = [];
        if ($node['x-gp247-admin-only'] ?? false) {
            $notes[] = t('admin only');
        }
        if ($node['x-gp247-detail-only'] ?? false) {
            $notes[] = t('detail only');
        }
        if ($node['x-gp247-include'] ?? false) {
            $notes[] = t('only with include') . ' `' . $field . '`';
        }
        if (in_array($field, $required, true)) {
            $notes[] = t('required');
        }
        $meaning = cell(t((string) ($node['description'] ?? '')));
        $lines[] = '| `' . $field . '` | ' . type_of($node) . ' | ' . $meaning . ($notes ? ' *(' . implode(', ', $notes) . ')*' : '') . ' |';
    }
    return $lines;
}

/**
 * Markdown table of an operation's parameters (shared ones resolved from components).
 *
 * @param array $spec
 * @param array $operation
 * @return string[] Lines.
 */
function parameter_table(array $spec, array $operation): array
{
    if (empty($operation['parameters'])) {
        return [];
    }
    $lines = ['**' . t('Parameters') . '**', '', '| ' . t('Name') . ' | ' . t('In') . ' | ' . t('Type') . ' | ' . t('Meaning') . ' |', '|---|---|---|---|'];
    foreach ($operation['parameters'] as $parameter) {
        if (isset($parameter['$ref'])) {
            $parameter = $spec['components']['parameters'][basename($parameter['$ref'])] ?? [];
        }
        $name = '`' . ($parameter['name'] ?? '') . '`' . (($parameter['required'] ?? false) ? ' *(' . t('required') . ')*' : '');
        $lines[] = '| ' . $name . ' | ' . t((string) ($parameter['in'] ?? '')) . ' | ' . type_of($parameter['schema'] ?? []) . ' | '
            . cell(t((string) ($parameter['description'] ?? ''))) . ' |';
    }
    $lines[] = '';
    return $lines;
}

/**
 * Example curl command of an operation.
 *
 * @param array  $spec
 * @param string $method
 * @param string $path
 * @param array  $operation
 * @param string $prefix
 * @return string[] Lines.
 */
function curl_example(array $spec, string $method, string $path, array $operation, string $prefix): array
{
    $url = 'https://shop.example.com' . preg_replace('/\{[^}]+\}/', '123', $path);
    $parts = ['curl' . ($method !== 'get' ? ' -X ' . strtoupper($method) : '') . ' "' . $url . '"', '  -H "Accept: application/json"'];
    $flat = array_merge([], ...array_map('array_keys', $operation['security'] ?? []));
    if (in_array('bearerAuth', $flat, true)) {
        $parts[] = '  -H "Authorization: Bearer <' . ($prefix === '/api/v2/core/' ? 'admin-token' : 'customer-token') . '>"';
    }
    $body = $operation['requestBody']['content']['application/json']['schema'] ?? null;
    if ($body !== null) {
        $parts[] = '  -H "Content-Type: application/json"';
        $parts[] = "  -d '" . json_encode(example($spec, $body), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "'";
    }
    return array_merge(['```bash'], [implode(" \\\n", $parts)], ['```', '']);
}

/**
 * Markdown of one operation.
 *
 * @param array  $spec
 * @param string $method
 * @param string $path
 * @param array  $operation
 * @param string $prefix
 * @param string $suffix Language file suffix.
 * @return string[] Lines.
 */
function operation_section(array $spec, string $method, string $path, array $operation, string $prefix, string $suffix): array
{
    $lines = ['### ' . t($operation['summary']) . ' — `' . strtoupper($method) . ' ' . $path . '`', '', cell(t($operation['description'])), ''];
    $facts = [[t('Sign-in'), sign_in($operation, $prefix)]];
    if ($prefix === '/api/v2/core/') {
        $permission = $operation['x-gp247-permission'] ?? null;
        $facts[] = [t('Permission'), $permission === null ? t('None beyond a valid admin token.') : cell(t($permission))];
    }
    if ($method === 'get') {
        $facts[] = [t('Caching'), t('`ETag` — send `If-None-Match` to get `304` when nothing changed.')];
    }
    $facts[] = [t('Since'), '`' . $operation['x-gp247-since'] . '`'];
    $lines[] = '| | |';
    $lines[] = '|---|---|';
    foreach ($facts as [$label, $value]) {
        $lines[] = '| ' . $label . ' | ' . $value . ' |';
    }
    $lines[] = '';
    $lines = array_merge($lines, parameter_table($spec, $operation));

    $body = $operation['requestBody']['content']['application/json']['schema'] ?? null;
    if ($body !== null && isset($body['$ref'])) {
        [$name] = resolve($spec, $body['$ref']);
        $lines[] = '**' . t('Request body') . '**: [' . $name . '](#' . heading_id($name) . ')';
        $lines[] = '';
    }
    $lines[] = '**' . t('Example request') . '**';
    $lines[] = '';
    $lines = array_merge($lines, curl_example($spec, $method, $path, $operation, $prefix));

    [$status, $schema] = success($operation);
    [$kind, $object] = body_kind($spec, $schema);
    $link = $object === null ? '' : '[' . $object . '](#' . heading_id($object) . ')';
    $returns = match ($kind) {
        'list'   => '`data`: ' . t('list of') . ' ' . $link . ' — ' . t('with `meta` (paging) and `links`.'),
        'array'  => '`data`: ' . t('list of') . ' ' . $link,
        'single' => '`data`: ' . $link,
        default  => t('See the example.'),
    };
    $lines[] = '**' . t('Response') . '** `' . $status . '` — ' . $returns;
    $lines[] = '';
    $lines[] = '```json';
    $lines[] = json_encode($schema === null ? new stdClass() : example($spec, $schema), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $lines[] = '```';
    $lines[] = '';

    $codes = $operation['x-gp247-error-codes'] ?? [];
    if ($codes) {
        $links = array_map(fn ($c) => '[`' . $c . '`](./api-troubleshooting' . $suffix . '.md#' . heading_id($c) . ')', $codes);
        $lines[] = '**' . t('Errors') . '**: ' . implode(' · ', $links);
        $lines[] = '';
    }
    return $lines;
}

/**
 * Data objects (schemas) used by a group's operations, in first-use order, following nested refs.
 *
 * @param array $spec
 * @param array $operations
 * @return string[]
 */
function used_schemas(array $spec, array $operations): array
{
    $seen = [];
    $queue = [];
    foreach ($operations as [, , $operation]) {
        preg_match_all('#"\#/components/schemas/([A-Za-z0-9]+)"#', json_encode($operation, JSON_UNESCAPED_SLASHES), $m);
        array_push($queue, ...$m[1]);
    }
    while ($queue) {
        $name = array_shift($queue);
        if (isset($seen[$name]) || in_array($name, ENVELOPES, true)) {
            continue;
        }
        $seen[$name] = true;
        preg_match_all('#"\#/components/schemas/([A-Za-z0-9]+)"#', json_encode($spec['components']['schemas'][$name] ?? [], JSON_UNESCAPED_SLASHES), $m);
        array_push($queue, ...$m[1]);
    }
    return array_keys($seen);
}

/**
 * Markdown of one reference page in one language.
 *
 * @param array  $spec
 * @param string $page   Base name.
 * @param string $code   Language code.
 * @return string
 */
function render_page(array $spec, string $page, string $code): string
{
    [$tagPrefix, $pathPrefix] = GROUPS[$page];
    $suffix = LANGUAGES[$code][0];

    $bar = [];
    foreach (LANGUAGES as $other => [$otherSuffix, $label, $flag, $current]) {
        $bar[] = $other === $code ? $flag . ' ' . $label . $current : '[' . $flag . ' ' . $label . '](./' . $page . $otherSuffix . '.md)';
    }
    $title = $page === 'api-reference-front' ? t('API v2 reference — Storefront (Front)') : t('API v2 reference — Admin (Core)');
    $lines = ['> 🌐 **' . t('Language') . ':** ' . implode(' · ', $bar), '', '# ' . $title, ''];
    $lines[] = '> ' . t('This page is generated from openapi.json by tools/openapi-to-markdown.php — do not edit it by hand.');
    $lines[] = '';
    $lines[] = t($page === 'api-reference-front'
        ? 'Every endpoint under `api/v2/front` — the storefront, customer accounts, cart and checkout — with who may call it, its parameters, an example call and what it returns.'
        : 'Every endpoint under `api/v2/core` — data for administrators and back-office systems (ERP, CRM, warehouse) — with the admin permission it needs, its parameters, an example call and what it returns.');
    $lines[] = '';
    // WHY one translatable sentence with placeholders: word order around the links differs per language.
    $lines[] = strtr(t('New to the API? Start with {quickstart}. Rules shared by every endpoint (response format, paging, `fields`, `include`, `304`) are in {conventions}; each error code is explained in {troubleshooting}.'), [
        '{quickstart}'      => '[' . t('Quick start') . '](./api-quickstart' . $suffix . '.md)',
        '{conventions}'     => '[' . t('Conventions') . '](./api-conventions' . $suffix . '.md)',
        '{troubleshooting}' => '[' . t('Troubleshooting') . '](./api-troubleshooting' . $suffix . '.md)',
    ]);
    $lines[] = '';

    $byTag = [];
    foreach ($spec['tags'] as $tag) {
        if (str_starts_with($tag['name'], $tagPrefix)) {
            $byTag[$tag['name']] = ['tag' => $tag, 'operations' => []];
        }
    }
    foreach ($spec['paths'] as $path => $item) {
        if (!str_starts_with($path, $pathPrefix)) {
            continue;
        }
        foreach (METHODS as $method) {
            if (isset($item[$method])) {
                $byTag[$item[$method]['tags'][0]]['operations'][] = [$method, $path, $item[$method]];
            }
        }
    }
    $byTag = array_filter($byTag, fn ($group) => $group['operations'] !== []);

    $lines[] = '## ' . t('Contents');
    $lines[] = '';
    foreach ($byTag as $name => $group) {
        $lines[] = '- [' . t($name) . '](#' . heading_id(t($name)) . ') — ' . cell(t($group['tag']['description']));
    }
    $lines[] = '- [' . t('Data objects') . '](#' . heading_id(t('Data objects')) . ')';
    $lines[] = '';

    $all = [];
    foreach ($byTag as $name => $group) {
        $lines[] = '## ' . t($name);
        $lines[] = '';
        $lines[] = cell(t($group['tag']['description']));
        $lines[] = '';
        foreach ($group['operations'] as [$method, $path, $operation]) {
            $lines = array_merge($lines, operation_section($spec, $method, $path, $operation, $pathPrefix, $suffix));
            $all[] = [$method, $path, $operation];
        }
    }

    $lines[] = '## ' . t('Data objects');
    $lines[] = '';
    $lines[] = t('Fields of the objects the endpoints above send and return. Dates are ISO 8601 strings; money is a number in the currency of the record.');
    $lines[] = '';
    foreach (used_schemas($spec, $all) as $name) {
        $schema = $spec['components']['schemas'][$name];
        $lines[] = '### ' . $name;
        $lines[] = '';
        if (!empty($schema['description'])) {
            $lines[] = cell(t($schema['description']));
            $lines[] = '';
        }
        $lines = array_merge($lines, field_table($schema), ['']);
    }

    // WHY a date from the spec, not today: --check must give the same page on any day.
    $lines[] = '---';
    $lines[] = '';
    $lines[] = sprintf(LANGUAGES[$code][4], $spec['info']['x-gp247-last-updated'] ?? '');

    return implode("\n", $lines) . "\n";
}

/**
 * Entry point.
 *
 * @param string[] $argv
 * @return int Exit code.
 */
function main(array $argv): int
{
    global $lang, $dict, $used, $missing;
    $check = in_array('--check', $argv, true);
    $spec = json_decode((string) file_get_contents(API_DIR . '/openapi.json'), true, 512, JSON_THROW_ON_ERROR);

    $dict = ['en' => []];
    foreach (['vi', 'ja'] as $code) {
        $file = API_DIR . '/reference-i18n/' . $code . '.json';
        $dict[$code] = is_file($file) ? json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR) : [];
    }
    $used = ['vi' => [], 'ja' => []];
    $missing = ['vi' => [], 'ja' => []];

    $stale = [];
    foreach (array_keys(GROUPS) as $page) {
        foreach (LANGUAGES as $code => [$suffix]) {
            $lang = $code;
            $target = API_DIR . '/' . $page . $suffix . '.md';
            $markdown = render_page($spec, $page, $code);
            // WHY line endings ignored: a Windows checkout with core.autocrlf turns the committed LF into CRLF.
            $current = is_file($target) ? str_replace("\r\n", "\n", (string) file_get_contents($target)) : null;
            if ($current !== $markdown) {
                $stale[] = basename($target);
                if (!$check) {
                    file_put_contents($target, $markdown);
                }
            }
        }
    }

    $problems = 0;
    foreach (['vi', 'ja'] as $code) {
        foreach (array_keys($missing[$code]) as $text) {
            fwrite(STDERR, "missing {$code} translation: {$text}\n");
            $problems++;
        }
        foreach (array_diff(array_keys($dict[$code]), array_keys($used[$code])) as $text) {
            fwrite(STDERR, "unused {$code} translation: {$text}\n");
            $problems++;
        }
    }
    foreach ($stale as $file) {
        fwrite($check ? STDERR : STDOUT, ($check ? 'stale: ' : 'written: ') . $file . "\n");
    }

    return ($problems > 0 || ($check && $stale !== [])) ? 1 : 0;
}

exit(main($argv));
