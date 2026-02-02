# Stagewise Setup Guide for PHP Project

## What is Stagewise?

Stagewise is a frontend coding agent that works inside your browser. It helps you make changes to your frontend code by:
- Understanding what you want to change through natural language
- Using browser context to see your actual UI
- Making changes directly in your local codebase

## Prerequisites

1. **Your PHP app must be running** - Make sure XAMPP is running and your site is accessible at `http://localhost/nhom2` (or your configured URL)

2. **Node.js installed** - Stagewise uses npx, which requires Node.js. Check if you have it:
   ```bash
   node --version
   ```
   If not installed, download from [nodejs.org](https://nodejs.org/)

## Setup Steps

### Step 1: Start Your PHP Application

Make sure your XAMPP server is running:
- Start Apache from XAMPP Control Panel
- Verify your site is accessible at `http://localhost/nhom2`

### Step 2: Install Node.js (if not already installed)

If you don't have Node.js installed, use nvm (Node Version Manager):

```bash
# Install nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash

# Load nvm in current session
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Install latest LTS Node.js
nvm install --lts
nvm use --lts
```

### Step 3: Run Stagewise

Open a terminal/command prompt and navigate to your project root:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/nhom2
```

Load nvm and run stagewise with your app port (XAMPP uses port 80):

```bash
# Load nvm
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Run stagewise (XAMPP Apache runs on port 80)
npx stagewise@latest -a 80 -w "/Applications/XAMPP/xamppfiles/htdocs/nhom2"
```

**Note:** The `-a 80` flag tells stagewise your app is running on port 80 (XAMPP default). The `-w` flag specifies your workspace path.

For local development without authentication, you can use bridge mode:

```bash
npx stagewise@latest -b -a 80 -w "/Applications/XAMPP/xamppfiles/htdocs/nhom2"
```

### Step 4: Follow the CLI Setup (if not using bridge mode)

If you're not using bridge mode (`-b` flag), the stagewise CLI will guide you through:
1. Creating a stagewise account (if you don't have one) - opens browser for authentication
2. Connecting to your local development server
3. Configuring the agent (you can use stagewise agent, Cursor, GitHub Copilot, etc.)

**Bridge Mode (`-b` flag):** Runs locally without authentication or the coding agent server. Useful for local development.

### Step 5: Use Stagewise

Once setup is complete:
1. Open your browser and navigate to your PHP app (e.g., `http://localhost/nhom2`)
2. Stagewise will inject its toolbar into your page
3. Click on elements you want to modify
4. Tell stagewise what changes you want to make
5. Watch it make the changes in your codebase!

## Configuration

Stagewise should work out of the box with your PHP project. However, if you need to configure it:

- The stagewise CLI will create a configuration file if needed
- You can specify your local server URL during setup
- Stagewise automatically detects your framework and file structure

## Tips

- **Keep your dev server running** - Stagewise needs your app to be accessible
- **Use a compatible agent** - Stagewise works with:
  - ✅ stagewise agent (recommended)
  - ✅ Cursor
  - ✅ GitHub Copilot
  - ✅ Windsurf
  - ✅ And more...

- **Click elements to provide context** - Clicking on UI elements helps stagewise understand what you want to change

## Troubleshooting

**Issue: Can't connect to local server**
- Make sure XAMPP Apache is running
- Verify your site URL is correct
- Check firewall settings

**Issue: Stagewise toolbar not appearing**
- Refresh your browser page
- Check browser console for errors
- Make sure you completed the CLI setup

**Issue: Changes not being saved**
- Verify file permissions in your project directory
- Check that stagewise has write access to your files

## Resources

- [Stagewise GitHub](https://github.com/stagewise-io/stagewise)
- [Stagewise Website](https://stagewise.io)
- [Discord Community](https://discord.gg/stagewise)

## Next Steps

After setup, try:
1. Click on a product card in your bakery website
2. Ask stagewise to "change the card hover effect" or "update the product card styling"
3. Watch stagewise make the changes in your `style.css` or PHP files!

