<?php

namespace Tests\Feature;

use Tests\TestCase;

class SwaggerDocsTest extends TestCase
{
    /**
     * Test that the Swagger UI interface renders successfully.
     */
    public function test_swagger_ui_page_loads_successfully(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
        $response->assertSee('KK Wholesalers ERP');
        $response->assertSee('SwaggerUIBundle');
        $response->assertSee('/docs/openapi.yaml');
    }

    /**
     * Test that /api/documentation redirects to /docs.
     */
    public function test_api_documentation_alias_redirects_to_docs(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertRedirect('/docs');
    }

    /**
     * Test that the OpenAPI YAML specification is served properly.
     */
    public function test_openapi_spec_is_accessible_and_valid(): void
    {
        $response = $this->get('/docs/openapi.yaml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/yaml; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('openapi: 3.0.3', $content);
        $this->assertStringContainsString('KK Wholesalers ERP — Route & API Documentation', $content);

        // Assert key functional domains are documented
        $this->assertStringContainsString('Authentication & Session', $content);
        $this->assertStringContainsString('Point of Sale (POS) & Sales', $content);
        $this->assertStringContainsString('Inventory Management', $content);
        $this->assertStringContainsString('Inter-Store Transfers', $content);
        $this->assertStringContainsString('Stock Movement Audit Ledger', $content);
        $this->assertStringContainsString('Products Catalog', $content);
        $this->assertStringContainsString('Branches & Stores', $content);
        $this->assertStringContainsString('User Management & RBAC', $content);

        // Assert critical endpoint paths
        $this->assertStringContainsString('/sales/pos:', $content);
        $this->assertStringContainsString('/inventory/receive:', $content);
        $this->assertStringContainsString('/inventory/adjust:', $content);
        $this->assertStringContainsString('/transfers:', $content);
        $this->assertStringContainsString('/movements:', $content);
        // Assert that the served content parses as structurally valid YAML without syntax or duplicate key errors
        $parsed = \Symfony\Component\Yaml\Yaml::parse($content);
        $this->assertIsArray($parsed);
        $this->assertSame('3.0.3', $parsed['openapi'] ?? null);
        $this->assertArrayHasKey('paths', $parsed);
        $this->assertArrayHasKey('/stores/{store}', $parsed['paths']);
        $this->assertArrayHasKey('get', $parsed['paths']['/stores/{store}']);
        $this->assertArrayHasKey('put', $parsed['paths']['/stores/{store}']);
        $this->assertArrayHasKey('/users', $parsed['paths']);
        $this->assertArrayHasKey('get', $parsed['paths']['/users']);
        $this->assertArrayHasKey('post', $parsed['paths']['/users']);
    }
}
