# WP Engine Git Push Deployment Guide

Complete guide for deploying CNF WordPress theme to WP Engine using Git push.

---

## 🎯 Goal

Deploy WordPress theme + MU-plugin to WP Engine development site using Git push (auto-deploys on push - no SSH/SFTP needed!).

**Your Setup:**
- **Development Site:** `westgategroup.wpenginepowered.com`
- **Git Remote:** `git@git.wpengine.com:westgategroup.git`
- **SSH Access:** `git@git.wpengine.com` (for info)

---

## ⚡ Quick Start (5 Minutes)

```bash
# 1. Navigate to wp-theme folder
cd /Users/neil/Documents/Wordsco/cnf/wp-theme

# 2. Initialize git (if not already done)
git init
git branch -M main

# 3. Add WP Engine as remote
git remote add wpengine git@git.wpengine.com:westgategroup.git

# 4. Commit all files
git add .
git commit -m "Initial WordPress theme + MU-plugin"

# 5. Push to WP Engine (auto-deploys!)
git push wpengine main

# WP Engine automatically:
# - Deploys theme to /wp-content/themes/cnf-headless/
# - Deploys MU-plugin to /wp-content/mu-plugins/cnf-setup/
# - Ready to use!
```

**That's it!** Your theme is live on WP Engine.

---

## 📁 Repository Structure for WP Engine

WP Engine expects this structure:

```
wp-theme/ (git root)
├── .gitignore
├── README.md
├── DEPLOYMENT-GUIDE.md          # This file
├── wp-schema.ts                  # Schema definition (TypeScript)
├── wp-schema.example.ts          # Example schema
├── package.json                  # For npm run build:schema
├── cnf-headless-theme/          # Theme → deploys to /wp-content/themes/cnf-headless/
│   ├── style.css
│   ├── functions.php
│   ├── index.php
│   ├── inc/
│   │   └── rest-api/
│   │       └── bootstrap.php
│   └── README.md
└── mu-plugins/                   # MU-plugins → deploys to /wp-content/mu-plugins/
    └── cnf-setup/
        ├── cnf-setup.php
        ├── schema.json          # Compiled from wp-schema.ts
        ├── setup.log
        ├── README.md
        └── includes/
            ├── schema-reader.php
            ├── pods-builder.php
            ├── content-seeder.php
            ├── media-uploader.php
            └── dashboard-customizer.php
```

**Important:** WP Engine automatically maps:
- `cnf-headless-theme/` → `/wp-content/themes/cnf-headless/`
- `mu-plugins/` → `/wp-content/mu-plugins/`

---

## 🚀 Complete Setup Process

### Step 1: Verify Git Access

```bash
# Test SSH connection to WP Engine
ssh -T git@git.wpengine.com

# Expected output:
# Hi there! You've successfully authenticated, but WP Engine Git does not provide shell access.
```

**If this fails:**
1. Add your SSH key to WP Engine User Portal
2. Go to: https://my.wpengine.com/ → User Profile → Git Push
3. Add your public SSH key (`cat ~/.ssh/id_ed25519.pub`)

### Step 2: Check Remote Information

```bash
# Get info about your WP Engine git remotes
ssh git@git.wpengine.com info

# Should show:
# westgategroup (development)
```

### Step 3: Configure wp-theme as Separate Repo

**Current status:** wp-theme is inside main CNF repo
**Goal:** Make wp-theme a separate, independent repo

**Option A: Keep wp-theme Inside CNF (Easier)**

```bash
cd /Users/neil/Documents/Wordsco/cnf

# Create subtree for wp-theme
git subtree split --prefix=wp-theme -b wp-theme-branch

# Create new repo from subtree
cd ..
git clone cnf cnf-wordpress-theme -b wp-theme-branch

cd cnf-wordpress-theme
git remote remove origin
git remote add wpengine git@git.wpengine.com:westgategroup.git
```

**Option B: Move wp-theme Out of CNF (Cleaner)**

```bash
# Copy wp-theme folder out of CNF
cd /Users/neil/Documents/Wordsco
cp -r cnf/wp-theme cnf-wordpress-theme

cd cnf-wordpress-theme

# Initialize as new git repo
git init
git branch -M main

# Add WP Engine remote
git remote add wpengine git@git.wpengine.com:westgategroup.git

# Commit everything
git add .
git commit -m "Initial WordPress theme + MU-plugin for WP Engine"
```

### Step 4: Create .gitignore

```bash
cat > .gitignore << 'EOF'
# Node
node_modules/
npm-debug.log*
.npm

# Build artifacts
*.log

# OS files
.DS_Store
Thumbs.db

# IDE
.vscode/
.idea/
*.swp
*.swo

# WordPress
wp-config.php
.htaccess

# MU-plugin logs (keep schema.json)
mu-plugins/cnf-setup/setup.log

# Temp files
*.tmp
*.bak
*~
EOF
```

### Step 5: Compile Schema

```bash
# Make sure you have schema.json compiled
npm run build:schema

# Verify it exists
ls mu-plugins/cnf-setup/schema.json

# Should output the schema file
```

**If schema.json is missing:**
- Copy `wp-schema.example.ts` to `wp-schema.ts`
- Edit with your content
- Run `npm run build:schema`

### Step 6: Push to WP Engine

```bash
# First push (sets up upstream)
git push -u wpengine main

# Subsequent pushes
git push wpengine main
```

**WP Engine will:**
1. Receive the push
2. Deploy files to correct WordPress directories
3. Theme available at: `/wp-content/themes/cnf-headless/`
4. MU-plugin available at: `/wp-content/mu-plugins/cnf-setup/`

### Step 7: Activate Theme on WP Engine

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Activate theme
wp theme activate cnf-headless

# Verify
wp theme list
# Should show: cnf-headless | active
```

### Step 8: Install Required Plugins

```bash
# Still in WP Engine SSH
wp plugin install pods --activate
wp plugin install ninja-forms --activate

# Verify
wp plugin list
```

### Step 9: Verify MU-Plugin

```bash
# Check MU-plugin is loaded
wp plugin list --status=must-use

# Should show: cnf-setup
```

### Step 10: Trigger Automated Setup

**Option A: Via WP Admin**
1. Go to `https://westgategroup.wpenginepowered.com/wp-admin/`
2. Navigate to **Tools → CNF Setup**
3. Click **"Run Setup Now"**

**Option B: Via WP-CLI**

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Manually trigger setup
wp option delete cnf_setup_completed
# Then load any admin page, or:
wp eval 'do_action("admin_init");'
```

**Option C: Automatic**
Setup runs automatically on first WordPress admin page load!

### Step 11: Verify Setup Completed

```bash
# Check setup log
ssh westgategroup@westgategroup.ssh.wpengine.net
cat sites/westgategroup/wp-content/mu-plugins/cnf-setup/setup.log

# Should show:
# [2025-01-20 10:30:00] Starting CNF automated setup...
# [2025-01-20 10:30:01] Creating Pods...
# [2025-01-20 10:30:15] Pods created successfully
# ... etc ...
# [2025-01-20 10:32:30] CNF automated setup completed successfully!
```

---

## 🔄 Development Workflow

### Making Changes

```bash
# 1. Edit files locally
vim wp-schema.ts
vim cnf-headless-theme/functions.php

# 2. Rebuild schema if changed
npm run build:schema

# 3. Commit changes
git add .
git commit -m "Update schema with new fields"

# 4. Push to WP Engine (auto-deploys!)
git push wpengine main
```

### Updating Content

```bash
# 1. Edit wp-schema.ts with new content
vim wp-schema.ts

# 2. Recompile schema
npm run build:schema

# 3. Push to WP Engine
git add mu-plugins/cnf-setup/schema.json
git commit -m "Update content schema"
git push wpengine main

# 4. Reset setup to run again
ssh westgategroup@westgategroup.ssh.wpengine.net
wp option delete cnf_setup_completed

# 5. Setup runs automatically on next admin page load
```

### Testing Changes Locally First

```bash
# 1. Make changes
# 2. Test locally with LocalWP or MAMP
# 3. When satisfied, push to WP Engine development site
git push wpengine main

# 4. Test on WP Engine dev site
# 5. When ready, deploy to production (if you have prod environment)
```

---

## 🎨 Customizing for Your Site

### Update Schema with Your Content

```bash
# 1. Copy example schema
cp wp-schema.example.ts wp-schema.ts

# 2. Edit with your content structure
vim wp-schema.ts

# 3. Compile
npm run build:schema

# 4. Commit and push
git add .
git commit -m "Add custom schema for CNF machines"
git push wpengine main
```

### Modify Theme Functions

```bash
# Edit theme functions
vim cnf-headless-theme/functions.php

# Commit and push
git add cnf-headless-theme/functions.php
git commit -m "Update CORS allowed origins"
git push wpengine main

# Changes are live immediately!
```

---

## 📡 Testing API Endpoints

After deployment, test your API:

```bash
# Bootstrap endpoint (all data in one request)
curl https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap

# Individual endpoints
curl https://westgategroup.wpenginepowered.com/wp-json/wp/v2/cnf_machine
curl https://westgategroup.wpenginepowered.com/wp-json/wp/v2/cnf_use
curl https://westgategroup.wpenginepowered.com/wp-json/wp/v2/faq
```

---

## 🐛 Troubleshooting

### Push Rejected

**Error:** `! [rejected] main -> main (non-fast-forward)`

**Solution:**
```bash
# WP Engine may have initial commits
git pull wpengine main --allow-unrelated-histories
git push wpengine main
```

### Theme Not Showing in WP Admin

**Problem:** Theme not visible in Appearance → Themes

**Check:**
```bash
ssh westgategroup@westgategroup.ssh.wpengine.net
ls sites/westgategroup/wp-content/themes/

# Should show: cnf-headless/
```

**Fix:**
Ensure `style.css` has proper theme header.

### MU-Plugin Not Loading

**Check:**
```bash
wp plugin list --status=must-use

# If not showing, verify file location:
ls sites/westgategroup/wp-content/mu-plugins/cnf-setup/cnf-setup.php
```

**Fix:**
MU-plugin main file must be at: `mu-plugins/cnf-setup/cnf-setup.php`

### Setup Not Running

**Check prerequisites:**
```bash
# 1. Schema file exists?
ls sites/westgategroup/wp-content/mu-plugins/cnf-setup/schema.json

# 2. Pods active?
wp plugin list | grep pods

# 3. Check log
cat sites/westgategroup/wp-content/mu-plugins/cnf-setup/setup.log
```

### Git Push Hangs

**Problem:** `git push wpengine main` hangs forever

**Solution:**
```bash
# Check SSH connection
ssh -T git@git.wpengine.com

# Try verbose push
GIT_SSH_COMMAND="ssh -v" git push wpengine main
```

---

## 🔐 Security Best Practices

1. **Never commit sensitive data:**
   - Database credentials
   - API keys
   - Passwords

2. **Use .gitignore:**
   - Logs (except needed for debugging)
   - `wp-config.php`
   - `.htaccess`

3. **SSH keys:**
   - Use SSH keys, not passwords
   - Add key to WP Engine User Portal

4. **Branch protection:**
   - Use `main` for production
   - Use `develop` for testing (if you have multiple environments)

---

## 📚 Additional Resources

- **WP Engine Git Push Docs:** https://wpengine.com/support/git/
- **SSH Gateway Guide:** https://wpengine.com/support/ssh-gateway/
- **WP-CLI on WP Engine:** https://wpengine.com/support/wp-cli/

---

## ✅ Deployment Checklist

Before pushing to WP Engine:

- [ ] Schema compiled: `npm run build:schema`
- [ ] `.gitignore` created
- [ ] All files committed: `git status`
- [ ] Remote added: `git remote -v`
- [ ] SSH access verified: `ssh -T git@git.wpengine.com`
- [ ] Theme files in correct structure
- [ ] MU-plugin files in correct structure
- [ ] Schema.json exists in mu-plugins/cnf-setup/

After pushing to WP Engine:

- [ ] Theme deployed: `wp theme list`
- [ ] Theme activated: `wp theme activate cnf-headless`
- [ ] Pods plugin active: `wp plugin list | grep pods`
- [ ] MU-plugin loaded: `wp plugin list --status=must-use`
- [ ] Setup ran successfully: Check setup.log
- [ ] API endpoint working: Test bootstrap endpoint
- [ ] React app can connect: Test from frontend

---

**Built by [Wordsco](https://wordsco.uk)**

Good luck with your WP Engine deployment! 🚀
