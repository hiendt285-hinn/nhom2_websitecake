#!/bin/bash

# Stagewise Runner Script for PHP Project
# This script loads nvm and runs stagewise with the correct settings

# Load nvm
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "Node.js is not installed. Please install it first:"
    echo "  nvm install --lts"
    exit 1
fi

# Get the current directory (project root)
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Run stagewise in bridge mode (local, no auth) with app port 80
echo "Starting stagewise..."
echo "Project directory: $PROJECT_DIR"
echo "App port: 80 (XAMPP Apache)"
echo ""
echo "Press Ctrl+C to stop stagewise"
echo ""

npx stagewise@latest -b -a 80 -w "$PROJECT_DIR"













