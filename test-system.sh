#!/bin/bash

# Test System Dependencies Script
# This script checks if all required dependencies are installed

echo "Testing system dependencies..."
echo "=============================="

# Function to check if a command exists
check_command() {
    local cmd=$1
    local name=${2:-$1}
    
    if command -v "$cmd" >/dev/null 2>&1; then
        echo "✅ $name is installed: $(which $cmd)"
        return 0
    else
        echo "❌ $name is NOT installed"
        return 1
    fi
}

# Track overall status
all_installed=true

# Check each dependency
echo ""
check_command "docker" "Docker" || all_installed=false
check_command "docker-compose" "Docker Compose" || all_installed=false
check_command "make" "Make" || all_installed=false
check_command "k6" "K6" || all_installed=false

echo ""
echo "=============================="

if [ "$all_installed" = true ]; then
    echo "🎉 All dependencies are installed! You're ready to go."
    exit 0
else
    echo "⚠️  Some dependencies are missing. Please check the setup section in README.md"
    exit 1
fi 