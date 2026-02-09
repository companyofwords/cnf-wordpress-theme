# CNF WordPress + React Headless CMS Template

A modern, schema-driven WordPress headless CMS with React Router 7 frontend. Build production-ready WordPress sites in minutes, not days.

## Features

- **Schema-Driven Setup**: Define your entire WordPress structure in TypeScript
- **Zero Manual Configuration**: MU-plugin automatically creates post types, fields, content, and media
- **React Router 7**: Modern React framework with optimized data loading
- **Bootstrap API**: Single API call loads all site data
- **Type-Safe**: TypeScript types flow from schema → WordPress → React
- **Secure Forms**: Ninja Forms integration with client portal
- **Reusable Template**: Clone and customize for unlimited projects
- **Managed Hosting Ready**: Optimized for WP Engine, Kinsta, Flywheel

## Quick Start

### Prerequisites

- Node.js 18+
- PHP 8.0+
- WordPress 6.0+
- npm or yarn

### 1. Clone the Template

```bash
git clone https://github.com/your-org/cnf-wp-template cnf-machines
cd cnf-machines
```

### 2. Install WordPress

```bash
# Download WordPress
wp core download

# Create wp-config.php
wp config create --dbname=cnf_wp --dbuser=root --dbpass=password

# Install WordPress
wp core install --url=http://cnf-machines.local --title="CNF Machines" --admin_user=admin --admin_password=password --admin_email=admin@example.com
```

### 3. Install Required Plugins

```bash
wp plugin install pods ninja-forms jwt-authentication-for-wp-rest-api --activate
```

### 4. Customize Your Schema

```bash
# Copy the example schema
cp wp-schema.example.ts wp-schema.ts

# Edit wp-schema.ts with your content structure
vim wp-schema.ts
```

### 5. Prepare Media Assets

```bash
# Add your images/videos to media-library folder
mkdir -p media-library/images
cp /path/to/your/images/* media-library/images/
```

### 6. Deploy MU-Plugin and Theme

```bash
# Copy MU-plugin
cp -r mu-plugins/cnf-setup wp-content/mu-plugins/

# Copy theme
cp -r themes/custom-headless wp-content/themes/
wp theme activate custom-headless
```

### 7. Build and Execute Schema

```bash
# Install dependencies
npm install

# Compile schema from TypeScript to JSON
npm run build:schema

# MU-plugin will auto-execute on next WordPress admin page load
# OR manually trigger via WP-CLI:
wp eval-file wp-content/mu-plugins/cnf-setup/execute-schema.php
```

### 8. Set Up React Frontend

```bash
cd react-frontend

# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build
```

### 9. Configure Integrations

Update `wp-schema.ts` with your API keys:

```typescript
export const integrations = {
  google_analytics: {
    tracking_id: 'G-XXXXXXXXXX' // Your GA4 ID
  },
  email_marketing: {
    api_key: process.env.MAILCHIMP_API_KEY // Set via .env
  }
};
```

### 10. Deploy

```bash
# Push WordPress files to managed hosting
# Deploy React build to your hosting/CDN
```

**Total Setup Time: ~30 minutes**

## Project Structure

```
cnf-wp/
├── wp-schema.ts                    # Your site structure (edit this!)
├── wp-schema.example.ts            # Example schema (reference)
├── PRD.md                          # Complete project documentation
├── README.md                       # This file
│
├── wp-content/
│   ├── mu-plugins/
│   │   └── cnf-setup/              # Automated setup plugin
│   │       ├── cnf-setup.php
│   │       ├── includes/
│   │       │   ├── schema-reader.php
│   │       │   ├── pods-builder.php
│   │       │   ├── content-seeder.php
│   │       │   ├── media-uploader.php
│   │       │   └── dashboard-customizer.php
│   │       └── schema.json         # Compiled from wp-schema.ts
│   │
│   └── themes/
│       └── custom-headless/        # Minimal headless theme
│           ├── functions.php
│           └── inc/
│               ├── api/
│               │   ├── bootstrap-endpoint.php
│               │   ├── ninja-forms-api.php
│               │   └── custom-endpoints.php
│               └── dashboard-customization.php
│
├── react-frontend/                 # React Router 7 app
│   ├── app/
│   │   ├── root.tsx               # Bootstrap loader (critical!)
│   │   ├── routes/                # File-based routing
│   │   ├── components/
│   │   ├── services/
│   │   │   ├── bootstrap.ts
│   │   │   └── api.ts
│   │   ├── hooks/
│   │   │   └── useBootstrapData.ts
│   │   └── types/
│   │       └── wordpress.ts       # Types from wp-schema.ts
│   ├── package.json
│   └── vite.config.ts
│
└── media-library/                  # Pre-organized media
    ├── images/
    ├── videos/
    └── documents/
```

## How It Works

### 1. Define Structure in TypeScript

```typescript
// wp-schema.ts
export const pods: PodDefinition[] = [
  {
    name: 'cnf_machine',
    label: 'CNF Machines',
    type: 'post_type',
    fields: [
      { name: 'dimensions', label: 'Dimensions', type: 'text' },
      { name: 'weight', label: 'Weight', type: 'number' }
      // ... more fields
    ]
  }
];
```

### 2. MU-Plugin Creates WordPress Structure

When you activate the MU-plugin, it:
1. Reads `schema.json` (compiled from `wp-schema.ts`)
2. Creates all Pods (post types, fields, taxonomies)
3. Seeds content from your schema
4. Uploads media files
5. Creates navigation menus
6. Customizes the WordPress dashboard
7. Creates Ninja Forms

### 3. React Router 7 Loads Data

```typescript
// app/root.tsx
export async function loader() {
  const bootstrap = await fetchBootstrap();
  return { bootstrap };
}

// app/components/Header.tsx
export function Header() {
  const { siteSettings, menus } = useBootstrapData(); // From root loader
  return <header>...</header>;
}
```

### 4. Single Bootstrap Request

Instead of 5+ API requests:
```
❌ /wp-json/wp/v2/settings
❌ /wp-json/wp/v2/menus
❌ /wp-json/custom/v1/header
❌ /wp-json/custom/v1/footer
❌ /wp-json/wp/v2/pages/1
```

One optimized request:
```
✅ /wp-json/custom/v1/bootstrap
   → Returns: { siteSettings, menus, headerFooter, homepageData }
```

## Key Concepts

### Schema-Driven Development

Your `wp-schema.ts` file is the **single source of truth**:

- ✅ Version controlled in Git
- ✅ Type-safe with TypeScript
- ✅ Testable before deployment
- ✅ Reproducible across environments
- ✅ Self-documenting

### React Router 7 Data Loading

**Traditional React (❌ Old Way)**:
```typescript
// Multiple useState, useEffect in every component
const [data, setData] = useState(null);
useEffect(() => {
  fetch('/api/data').then(res => setData(res));
}, []);
```

**React Router 7 (✅ New Way)**:
```typescript
// Data loaded once in route loader
export async function loader() {
  return await fetchData();
}

// Component receives data, no loading states
export default function Component() {
  const data = useLoaderData();
  return <div>{data.title}</div>;
}
```

### Bootstrap Pattern

All global data loaded once in `root.tsx`:
- Site settings (name, logo, contact info)
- Navigation menus
- Header/footer content
- Homepage data
- Any other global content

Accessed anywhere via `useMatches()`:
```typescript
const bootstrap = useBootstrapData();
const { siteSettings, menus } = bootstrap;
```

## NPM Scripts

```bash
# Schema compilation
npm run build:schema          # Compile TypeScript schema to JSON

# React development
npm run dev                   # Start dev server (React Router 7)
npm run build                 # Build for production
npm run preview               # Preview production build

# WordPress (WP-CLI)
npm run wp:install            # Install WordPress
npm run wp:plugins            # Install required plugins
npm run wp:execute-schema     # Manually trigger schema execution
npm run wp:reset              # Reset WordPress (⚠️ destroys data)
```

## Environment Variables

Create `.env` file:

```env
# WordPress
WP_HOME=http://cnf-machines.local
WP_SITEURL=http://cnf-machines.local/wp

# API
VITE_WP_API_URL=http://cnf-machines.local/wp-json

# Integrations
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
MAILCHIMP_API_KEY=your-api-key-here

# Development
VITE_DEV_MODE=true
```

## Deployment

### Development
```bash
npm run dev                    # React dev server
# WordPress: Local (LocalWP, MAMP, or wp-env)
```

### Staging
```bash
npm run build                  # Build React for production
# Deploy to staging environment on managed hosting
```

### Production
```bash
npm run build                  # Build React for production
# Deploy WordPress to WP Engine/Kinsta/Flywheel
# Deploy React build to hosting/CDN
```

## API Endpoints

### Public Endpoints
```
GET  /wp-json/custom/v1/bootstrap
GET  /wp-json/wp/v2/cnf_machine
GET  /wp-json/wp/v2/cnf_machine/{id}
GET  /wp-json/wp/v2/cnf_use
GET  /wp-json/wp/v2/faq
POST /wp-json/custom/v1/forms/{form_id}/submit
```

### Authenticated Endpoints (Admin only)
```
GET  /wp-json/custom/v1/forms/{form_id}/submissions
GET  /wp-json/custom/v1/forms/submissions/{id}
PUT  /wp-json/custom/v1/forms/submissions/{id}/status
GET  /wp-json/custom/v1/forms/submissions/export
```

## Customization Guide

### Adding a New Post Type

1. Edit `wp-schema.ts`:
```typescript
export const pods: PodDefinition[] = [
  // ... existing post types
  {
    name: 'product',
    label: 'Products',
    type: 'post_type',
    fields: [
      { name: 'price', label: 'Price', type: 'currency' }
    ]
  }
];
```

2. Rebuild schema:
```bash
npm run build:schema
```

3. Re-execute schema (safe, won't delete existing data):
```bash
npm run wp:execute-schema
```

### Adding a New Form Field

1. Edit `wp-schema.ts`:
```typescript
export const ninjaForms: NinjaFormDefinition[] = [
  {
    title: 'Contact Form',
    fields: [
      // ... existing fields
      {
        type: 'textbox',
        label: 'Company',
        key: 'company',
        required: false
      }
    ]
  }
];
```

2. Rebuild and execute schema

### Customizing Dashboard

Edit `wp-schema.ts`:
```typescript
export const dashboardCustomization = {
  remove_menu_items: [
    'edit-comments.php',
    'edit.php' // Remove default posts
  ],
  menu_order: [
    'index.php',
    'edit.php?post_type=cnf_machine'
    // ... custom order
  ]
};
```

## Template Variants

This repository can serve as a base for multiple template variants:

```
cnf-wp-templates/
├── variants/
│   ├── cnf/              # CNF machines (this project)
│   ├── portfolio/        # Portfolio/agency sites
│   ├── business/         # Business/corporate sites
│   └── blog/             # Blog/content sites
```

Each variant has its own `wp-schema.ts` but shares the same MU-plugin and base React structure.

### Creating a New Variant

```bash
# Clone the base template
git clone https://github.com/your-org/cnf-wp-template new-variant

# Edit schema for new use case
vim wp-schema.ts

# Follow setup steps 1-10
```

## Troubleshooting

### Schema not executing
```bash
# Check MU-plugin is active
wp plugin list

# Manually trigger schema
wp eval-file wp-content/mu-plugins/cnf-setup/execute-schema.php

# Check error logs
tail -f wp-content/debug.log
```

### React app can't connect to WordPress
```bash
# Check CORS settings in WordPress
# Verify .env VITE_WP_API_URL is correct
# Test API manually
curl http://yoursite.local/wp-json/custom/v1/bootstrap
```

### Bootstrap endpoint returning 404
```bash
# Flush permalinks
wp rewrite flush

# Check if custom endpoints are registered
wp rewrite list
```

### Types not matching between WP and React
```bash
# Rebuild schema
npm run build:schema

# Copy types to React
cp wp-schema.ts react-frontend/app/types/wordpress.ts
```

## Resources

- [PRD.md](./PRD.md) - Complete product requirements document
- [wp-schema.example.ts](./wp-schema.example.ts) - Example schema with all options
- [React Router 7 Docs](https://reactrouter.com)
- [Pods Framework Docs](https://pods.io/docs/)
- [Ninja Forms API](https://ninjaforms.com/docs/)

## Support

- **Issues**: [GitHub Issues](https://github.com/your-org/cnf-wp-template/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-org/cnf-wp-template/discussions)
- **Documentation**: See [PRD.md](./PRD.md)

## License

MIT License - feel free to use for commercial and personal projects.

## Credits

Built with:
- [WordPress](https://wordpress.org)
- [React Router 7](https://reactrouter.com)
- [Pods Framework](https://pods.io)
- [Ninja Forms](https://ninjaforms.com)
- [TypeScript](https://typescriptlang.org)
- [Vite](https://vitejs.dev)

---

**Need help?** Open an issue or check the [PRD.md](./PRD.md) for detailed documentation.

**Want to contribute?** Pull requests welcome!
