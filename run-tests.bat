@echo off
REM DynamicCRUD Test Runner
REM Usage: run-tests.bat [options]

echo ========================================
echo DynamicCRUD Test Suite
echo ========================================
echo.

if "%1"=="" (
    echo Running all tests...
    php vendor\phpunit\phpunit\phpunit --testdox
) else if "%1"=="coverage" (
    echo Generating coverage report...
    php vendor\phpunit\phpunit\phpunit --coverage-html coverage
    echo Coverage report generated in coverage/index.html
) else if "%1"=="verbose" (
    echo Running tests with verbose output...
    php vendor\phpunit\phpunit\phpunit --testdox --verbose
) else (
    echo Running specific test: %1
    php vendor\phpunit\phpunit\phpunit tests\%1Test.php --testdox
)

echo.
echo ========================================
echo Tests completed
echo ========================================
