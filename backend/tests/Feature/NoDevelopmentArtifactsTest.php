<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class NoDevelopmentArtifactsTest extends TestCase
{
    public function test_no_var_dump_in_app_directory()
    {
        // SECURITY FIX: Verify no var_dump statements remain in production code
        $files = File::allFiles(app_path());
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $this->assertStringNotContainsString('var_dump', $content,
                    "Found var_dump in {$file->getPathname()}");
            }
        }
    }

    public function test_no_print_r_in_app_directory()
    {
        // SECURITY FIX: Verify no print_r statements remain in production code
        $files = File::allFiles(app_path());
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $this->assertStringNotContainsString('print_r', $content,
                    "Found print_r in {$file->getPathname()}");
            }
        }
    }

    public function test_no_dd_in_app_directory()
    {
        // SECURITY FIX: Verify no dd() dump-and-die calls remain in production code
        $files = File::allFiles(app_path());
        $found_dd = false;
        $dd_locations = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $lines = explode("\n", $content);

                foreach ($lines as $lineNumber => $line) {
                    // Skip commented lines
                    if (preg_match('/^\s*\/\//', $line)) {
                        continue;
                    }
                    if (preg_match('/^\s*#/', $line)) {
                        continue;
                    }

                    // Check for dd() on non-commented lines
                    // Use negative lookbehind to avoid matching "add(" or other substrings
                    if (preg_match('/(?<![a-zA-Z])dd\s*\(/', $line)) {
                        $found_dd = true;
                        $dd_locations[] = "Found dd() in {$file->getPathname()}:" . ($lineNumber + 1) . " Line content: {$line}";
                    }
                }
            }
        }

        $this->assertFalse($found_dd, "Found dd() calls in the following locations:\n" . implode("\n", $dd_locations));
    }

    public function test_no_log_debug_in_app_directory()
    {
        // SECURITY FIX: Verify no Log::debug statements remain in production code
        $files = File::allFiles(app_path());
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $this->assertStringNotContainsString('Log::debug', $content,
                    "Found Log::debug in {$file->getPathname()}");
            }
        }
    }

    public function test_no_todo_comments_in_app_directory()
    {
        // SECURITY FIX: Verify no TODO comments remain in production code
        $files = File::allFiles(app_path());
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                // Match TODO or FIXME comments (case-insensitive)
                $this->assertDoesNotMatchRegularExpression('/\/\/\s*(TODO|FIXME)/i', $content,
                    "Found TODO/FIXME comment in {$file->getPathname()}");
            }
        }
    }
}
