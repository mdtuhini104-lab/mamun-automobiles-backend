<?php
/**
 * Mamun Automobiles ERP - Quality Assurance & Safety Verification Script
 * This script runs locally or in CI/CD to verify compliance with production engineering policies.
 */

define('BACKEND_DIR', dirname(__DIR__));
define('FRONTEND_DIR', dirname(dirname(__DIR__)) . '/frontend');

$failures = [];

function log_info($msg) {
    echo "\033[34m[INFO]\033[0m $msg\n";
}

function log_success($msg) {
    echo "\033[32m[PASS]\033[0m $msg\n";
}

function log_failure($msg) {
    global $failures;
    echo "\033[31m[FAIL]\033[0m $msg\n";
    $failures[] = $msg;
}

// ==========================================
// Check 1: Recursive PHP Lint Verification
// ==========================================
log_info("Running recursive PHP syntax validation...");
$phpFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BACKEND_DIR));
$lintPassed = true;
foreach ($phpFiles as $file) {
    if ($file->isDir()) continue;
    $filePath = $file->getPathname();
    $normalizedPath = str_replace('\\', '/', $filePath);
    if (strpos($normalizedPath, '/vendor/') !== false) continue;
    if (strpos($normalizedPath, '/node_modules/') !== false) continue;
    if (strpos($normalizedPath, '/bootstrap/cache/') !== false) continue;
    if ($file->getExtension() !== 'php') continue;

    $output = [];
    $retval = 0;
    exec("php -l " . escapeshellarg($filePath) . " 2>&1", $output, $retval);
    if ($retval !== 0) {
        log_failure("Syntax error in: $filePath\n    " . implode("\n    ", $output));
        $lintPassed = false;
    }
}
if ($lintPassed) {
    log_success("All PHP files passed syntax checks.");
}

// ==========================================
// Check 2: Encoding Safety (Check for UTF-16 corruption)
// ==========================================
log_info("Validating file encodings (Ensuring no UTF-16LE / null-byte corruption)...");
$encodingPassed = true;
foreach ($phpFiles as $file) {
    if ($file->isDir()) continue;
    $filePath = $file->getPathname();
    $normalizedPath = str_replace('\\', '/', $filePath);
    if (strpos($normalizedPath, '/vendor/') !== false) continue;
    if (strpos($normalizedPath, '/node_modules/') !== false) continue;
    if ($file->getExtension() !== 'php') continue;

    $content = file_get_contents($filePath);
    // Check for UTF-16 BOMs
    $bom = substr($content, 0, 2);
    if ($bom === "\xFF\xFE" || $bom === "\xFE\xFF") {
        log_failure("UTF-16 BOM detected in: $filePath");
        $encodingPassed = false;
    }
    // Check for excessive null bytes (characteristic of UTF-16 encoded ASCII)
    if (substr_count($content, "\x00") > 10) {
        log_failure("Possible UTF-16 encoding or null-byte corruption detected in: $filePath");
        $encodingPassed = false;
    }
}
if ($encodingPassed) {
    log_success("All backend PHP files verified as UTF-8 safe.");
}

// ==========================================
// Check 3: Check alert() statements in frontend/src
// ==========================================
log_info("Scanning frontend source code for illegal alert() calls...");
$alertPassed = true;
if (is_dir(FRONTEND_DIR . '/src')) {
    $frontendFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FRONTEND_DIR . '/src'));
    foreach ($frontendFiles as $file) {
        if ($file->isDir()) continue;
        $filePath = $file->getPathname();
        if ($file->getExtension() !== 'vue' && $file->getExtension() !== 'js') continue;

        $content = file_get_contents($filePath);
        // Match generic alert(...) but not comments or strings containing alert
        if (preg_match('/\balert\s*\(/', $content)) {
            log_failure("Generic alert() call detected in: $filePath");
            $alertPassed = false;
        }
    }
} else {
    log_failure("Frontend source folder not found at: " . FRONTEND_DIR . '/src');
    $alertPassed = false;
}
if ($alertPassed) {
    log_success("No generic alert() statements found in frontend/src.");
}

// ==========================================
// Check 4: Check direct axios imports in frontend/src
// ==========================================
log_info("Checking for direct axios imports (must use services/api.js instance)...");
$axiosPassed = true;
if (is_dir(FRONTEND_DIR . '/src')) {
    $frontendFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FRONTEND_DIR . '/src'));
    foreach ($frontendFiles as $file) {
        if ($file->isDir()) continue;
        $filePath = $file->getPathname();
        if ($file->getExtension() !== 'vue' && $file->getExtension() !== 'js') continue;
        if (basename($filePath) === 'api.js') continue;

        $content = file_get_contents($filePath);
        if (strpos($content, "import axios from 'axios'") !== false || strpos($content, 'import axios from "axios"') !== false) {
            log_failure("Direct axios import found in: $filePath (must use api.js)");
            $axiosPassed = false;
        }
    }
}
if ($axiosPassed) {
    log_success("All API requests correctly channel through services/api.js.");
}

// ==========================================
// Check 5: Laravel Boot & Route Compilation Check
// ==========================================
log_info("Verifying Laravel application boot and routing...");
$output = [];
$retval = 0;
exec("php " . escapeshellarg(BACKEND_DIR . '/artisan') . " route:list --path=api 2>&1", $output, $retval);
if ($retval !== 0) {
    log_failure("Laravel failed to boot or compile routing. Output:\n" . implode("\n", $output));
} else {
    log_success("Laravel booted successfully and routes compiled.");
}

// ==========================================
// Final Report
// ==========================================
echo "\n==========================================\n";
echo "Mamun Automobiles Safety Validation Report\n";
echo "==========================================\n";
if (empty($failures)) {
    echo "\033[32mSUCCESS: All safety validations passed! Codebase is deployment-safe.\033[0m\n";
    exit(0);
} else {
    echo "\033[31mFAILURE: " . count($failures) . " safety validations failed! Fix errors before deploying.\033[0m\n";
    exit(1);
}
