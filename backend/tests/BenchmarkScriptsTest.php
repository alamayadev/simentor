<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class BenchmarkScriptsTest extends TestCase
{
    /**
     * Test that benchmark-endpoints.php file exists and is valid PHP
     */
    public function test_benchmark_endpoints_file_exists()
    {
        $filePath = __DIR__ . '/../benchmark-endpoints.php';
        $this->assertFileExists($filePath, 'benchmark-endpoints.php file should exist');
    }

    /**
     * Test that benchmark.php file exists and is valid PHP
     */
    public function test_benchmark_file_exists()
    {
        $filePath = __DIR__ . '/../benchmark.php';
        $this->assertFileExists($filePath, 'benchmark.php file should exist');
    }

    /**
     * Test that benchmark-endpoints.php is syntactically valid PHP
     */
    public function test_benchmark_endpoints_syntax()
    {
        $filePath = __DIR__ . '/../benchmark-endpoints.php';
        $output = [];
        $exitCode = 0;
        
        exec("php -l $filePath 2>&1", $output, $exitCode);
        
        $this->assertEquals(0, $exitCode, 
            'benchmark-endpoints.php should have valid PHP syntax. Output: ' . implode("\n", $output));
    }

    /**
     * Test that benchmark.php is syntactically valid PHP
     */
    public function test_benchmark_syntax()
    {
        $filePath = __DIR__ . '/../benchmark.php';
        $output = [];
        $exitCode = 0;
        
        exec("php -l $filePath 2>&1", $output, $exitCode);
        
        $this->assertEquals(0, $exitCode, 
            'benchmark.php should have valid PHP syntax. Output: ' . implode("\n", $output));
    }

    /**
     * Test that benchmark-endpoints.php contains expected endpoints
     */
    public function test_benchmark_endpoints_contains_expected_endpoints()
    {
        $filePath = __DIR__ . '/../benchmark-endpoints.php';
        $content = file_get_contents($filePath);
        
        $expectedEndpoints = [
            '/api/landing',
            '/api/enums',
            '/api/enums/fungsi-types',
            '/api/kantor/pegawai',
            '/api/kantor/kegiatan',
            '/api/kantor/penugasan',
            '/api/kantor/surat-tugas',
            '/api/kantor/bast',
            '/api/kantor/skp/stats',
            '/api/kantor/mitra',
            '/api/pdf/spk/1',
            '/api/pdf/bast/1',
        ];
        
        foreach ($expectedEndpoints as $endpoint) {
            $this->assertStringContainsString($endpoint, $content, 
                "benchmark-endpoints.php should contain endpoint: $endpoint");
        }
    }

    /**
     * Test that benchmark.php contains expected test scenarios
     */
    public function test_benchmark_contains_expected_scenarios()
    {
        $filePath = __DIR__ . '/../benchmark.php';
        $content = file_get_contents($filePath);
        
        $expectedScenarios = [
            'light_load',
            'medium_load',
            'heavy_load',
        ];
        
        foreach ($expectedScenarios as $scenario) {
            $this->assertStringContainsString($scenario, $content, 
                "benchmark.php should contain scenario: $scenario");
        }
    }

    /**
     * Test that benchmark.php contains expected endpoint categories
     */
    public function test_benchmark_contains_expected_categories()
    {
        $filePath = __DIR__ . '/../benchmark.php';
        $content = file_get_contents($filePath);
        
        $expectedCategories = [
            'public',
            'data_read',
            'admin',
        ];
        
        foreach ($expectedCategories as $category) {
            $this->assertStringContainsString($category, $content, 
                "benchmark.php should contain category: $category");
        }
    }

    /**
     * Test that benchmark-endpoints.php has proper structure
     */
    public function test_benchmark_endpoints_structure()
    {
        $filePath = __DIR__ . '/../benchmark-endpoints.php';
        $content = file_get_contents($filePath);
        
        // Check for key sections
        $this->assertStringContainsString('$endpoints', $content, 
            'benchmark-endpoints.php should define endpoints array');
        $this->assertStringContainsString('public', $content, 
            'benchmark-endpoints.php should have public endpoints section');
        $this->assertStringContainsString('authenticated', $content, 
            'benchmark-endpoints.php should have authenticated endpoints section');
        $this->assertStringContainsString('pdf', $content, 
            'benchmark-endpoints.php should have PDF endpoints section');
        $this->assertStringContainsString('admin', $content, 
            'benchmark-endpoints.php should have admin endpoints section');
    }

    /**
     * Test that benchmark.php has proper structure
     */
    public function test_benchmark_structure()
    {
        $filePath = __DIR__ . '/../benchmark.php';
        $content = file_get_contents($filePath);
        
        // Check for key functions and structures
        $this->assertStringContainsString('function makeRequest', $content, 
            'benchmark.php should define makeRequest function');
        $this->assertStringContainsString('function benchmarkEndpoint', $content, 
            'benchmark.php should define benchmarkEndpoint function');
        $this->assertStringContainsString('function formatDuration', $content, 
            'benchmark.php should define formatDuration function');
        $this->assertStringContainsString('function formatBytes', $content, 
            'benchmark.php should define formatBytes function');
    }

    /**
     * Test that benchmark-endpoints.php includes enums endpoint
     * (This is relevant to the task we just completed)
     */
    public function test_benchmark_endpoints_includes_enums_endpoint()
    {
        $filePath = __DIR__ . '/../benchmark-endpoints.php';
        $content = file_get_contents($filePath);
        
        $this->assertStringContainsString('/api/enums', $content, 
            'benchmark-endpoints.php should include /api/enums endpoint');
        $this->assertStringContainsString('Enums API', $content, 
            'benchmark-endpoints.php should have description for Enums API');
    }

    /**
     * Test that benchmark scripts use correct base URL
     */
    public function test_benchmark_scripts_use_correct_base_url()
    {
        $endpointsFile = __DIR__ . '/../benchmark-endpoints.php';
        $benchmarkFile = __DIR__ . '/../benchmark.php';
        
        $endpointsContent = file_get_contents($endpointsFile);
        $benchmarkContent = file_get_contents($benchmarkFile);
        
        $this->assertStringContainsString('http://127.0.0.1:9001', $endpointsContent, 
            'benchmark-endpoints.php should use correct base URL');
        $this->assertStringContainsString('http://127.0.0.1:9001', $benchmarkContent, 
            'benchmark.php should use correct base URL');
    }
}
