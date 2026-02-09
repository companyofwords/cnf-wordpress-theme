# Product Requirements Document: WordPress + React Headless Theme

## 1. Project Overview

### 1.1 Purpose
Develop a custom WordPress theme that serves as a headless CMS, using the WordPress REST API to securely deliver content to a React-based front-end application.

### 1.2 Goals
- Decouple content management from presentation layer
- Provide a modern, performant user experience with React
- Maintain WordPress's content management capabilities
- Ensure secure API communication between WordPress and React
- Enable scalable, maintainable architecture
- Automate content structure setup through code (Infrastructure as Code approach)
- Provide programmatic WordPress dashboard customization
- Enable efficient data bootstrapping with optimized API calls
- Implement secure contact form handling with client portal access
- **Create a reusable template system for programmatically generating WordPress installs in the future**
- Eliminate manual WordPress configuration through complete automation

## 2. Technical Architecture

### 2.1 Backend (WordPress)
- **Platform**: WordPress (latest stable version)
- **Theme Type**: Headless/API-first custom theme
- **API**: WordPress REST API (wp-json)
- **Authentication**: JWT tokens or Application Passwords
- **Custom Fields**: Pods Framework (not ACF)
- **Custom Post Types**: Defined programmatically via Pods API
- **Custom Endpoints**: Extend REST API for specific functionality
- **Setup Automation**: MU-Plugin for programmatic site configuration
- **Content Management**: TypeScript-driven schema and content definitions
- **Dashboard**: Programmatically customized WordPress admin interface

### 2.2 Frontend (React)
- **Framework**: React 18+ with TypeScript
- **Routing Framework**: React Router 7 (with data loading capabilities)
- **Build Tool**: Vite (integrated with React Router 7)
- **Data Loading**: React Router 7 loaders in root.tsx for bootstrap data
- **Data Access**: useMatches() hook to access loaded data throughout app
- **State Management**: Primarily route-based data, Context API for global state
- **API Client**: Fetch API (native, works seamlessly with React Router loaders)
- **Styling**: CSS Modules, Styled Components, or Tailwind CSS
- **Type Safety**: TypeScript for all components, loaders, and API interactions

### 2.3 Schema & Configuration Management
- **Schema Definition**: TypeScript constants file (`wp-schema.ts`)
- **Content Types**: Pods post types, taxonomies, and fields defined in TS
- **Static Data**: Site content, menu items, settings in TS constants
- **Media Assets**: Predefined media library structure with programmatic upload
- **Form Definitions**: Ninja Forms configuration in TypeScript
- **Dashboard Config**: Admin UI customization defined in code

### 2.4 Deployment Architecture
```
┌─────────────────────────────────────────────────────────────┐
│ Development Phase: Schema-Driven Setup                     │
├─────────────────────────────────────────────────────────────┤
│ wp-schema.ts → MU-Plugin Setup Script → WordPress Database │
│     ↓              ↓                          ↓             │
│ Types/Fields   Content/Media              Pods Config      │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Runtime: React Router 7 Data Flow                          │
├─────────────────────────────────────────────────────────────┤
│ User Request → React Router 7                              │
│                    ↓                                        │
│              root.tsx loader                                │
│                    ↓                                        │
│         /wp-json/custom/v1/bootstrap (Single Request)      │
│                    ↓                                        │
│    Returns: { siteSettings, menus, headerFooter, etc. }    │
│                    ↓                                        │
│         Cached in route matches                             │
│                    ↓                                        │
│    All components access via useMatches()                   │
│                    ↓                                        │
│         Route loaders fetch page-specific data              │
│                    ↓                                        │
│         /wp-json/pods/v1/{pod_name}/{id}                   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Form Submission Flow                                        │
├─────────────────────────────────────────────────────────────┤
│ User Form Submit → React Router 7 Action                   │
│                         ↓                                   │
│              POST /wp-json/custom/v1/forms/{id}/submit     │
│                         ↓                                   │
│              Ninja Forms + WordPress Database               │
│                         ↓                                   │
│         Client Portal (Authenticated Route)                 │
│                         ↓                                   │
│         GET /wp-json/custom/v1/forms/{id}/submissions      │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Reusability: Template → New Project                        │
├─────────────────────────────────────────────────────────────┤
│ Template Repo → Clone → Edit wp-schema.ts → Build → Deploy │
│                                    ↓                        │
│                         New WordPress Instance              │
│                         (30 min setup time)                 │
└─────────────────────────────────────────────────────────────┘
```

## 3. Security Requirements

### 3.1 Authentication & Authorization
- **Public Content**: Use standard REST API endpoints without authentication
- **Protected Content**: Implement JWT (JSON Web Tokens) or WordPress Application Passwords
- **User Roles**: Respect WordPress user roles and capabilities
- **CORS**: Configure proper CORS headers for cross-origin requests

### 3.2 API Security
- Enable HTTPS for all API communications
- Implement rate limiting to prevent abuse
- Validate and sanitize all inputs
- Use nonces for authenticated requests
- Implement API key authentication for sensitive endpoints
- Whitelist allowed origins

### 3.3 Data Protection
- Sanitize output to prevent XSS attacks
- Implement Content Security Policy (CSP)
- Protect against SQL injection through parameterized queries
- Secure file upload handling
- Implement request throttling

## 4. Core Features

### 4.1 WordPress Backend Features

#### 4.1.1 Automated Setup System (MU-Plugin)
- **Schema Processor**: Reads TypeScript schema and creates WordPress structure
- **Pods Integration**: Programmatically creates:
  - Custom post types
  - Custom taxonomies
  - Pod fields and field groups
  - Relationships between content types
- **Content Seeding**: Automatically imports content from TS constants
- **Media Library Setup**: Programmatically uploads and organizes media files
- **Menu Creation**: Generates navigation menus from schema definitions
- **User Roles**: Creates custom roles and capabilities as needed
- **Settings Configuration**: Populates site options and settings

#### 4.1.2 Core WordPress Features
- Custom headless theme with minimal PHP templates (admin fallback only)
- Pods Framework for flexible custom fields (not ACF)
- Custom REST API endpoints for:
  - Bootstrap endpoint (all site data in one request)
  - Menu structures
  - Site settings/options
  - Custom Pods content types
  - Search functionality
  - Ninja Forms submission endpoint
  - Contact message retrieval (authenticated)
- Media management and optimization
- SEO metadata exposure via API

#### 4.1.3 Dashboard Customization
- Programmatic admin menu modification
- Custom admin dashboard widgets
- Hidden/removed default WordPress elements
- Custom branding (logo, colors, login page)
- Role-based dashboard views
- Quick links and shortcuts for common tasks
- Custom admin columns for post types
- Bulk actions for content management

#### 4.1.4 Ninja Forms Integration
- REST API hooks for form submissions
- Secure POST endpoint for React frontend
- Form validation via API
- Submission storage in WordPress database
- Client portal authentication for viewing submissions
- Email notifications on form submission
- Anti-spam protection (nonce verification, rate limiting)

### 4.2 React Frontend Features

#### 4.2.1 Core Features
- Dynamic routing based on WordPress content
- Component-based page templates
- Post/Page rendering with custom Pods fields
- Navigation menu rendering from bootstrap data
- Search functionality
- Archive/Category/Tag pages
- Pagination
- 404 error handling
- Loading states and skeleton screens
- Error boundaries
- TypeScript types matching WordPress schema

#### 4.2.2 Optimized Data Loading
- **Bootstrap API**: Single API call to fetch all initial site data
  - Site settings and configuration
  - Navigation menus
  - Global content (header/footer)
  - Initial page/post data
  - Custom Pods content
- Subsequent lazy loading for dynamic content
- Intelligent caching strategy
- Prefetching for anticipated navigation

#### 4.2.3 Contact Form System
- Ninja Forms integration via React components
- Client-side form validation matching server rules
- Secure form submission with nonce tokens
- Real-time validation feedback
- Success/error message handling
- File upload support (if needed)

#### 4.2.4 Client Portal
- Authenticated area for viewing contact form submissions
- Role-based access control (admin vs. client users)
- Filterable/searchable submission list
- Individual submission detail view
- Mark as read/unread functionality
- Export submissions (CSV/PDF)
- Real-time notification of new submissions (optional)

### 4.3 Performance Features
- Lazy loading for images and components
- Code splitting
- Caching strategy (browser cache, service workers)
- CDN integration for static assets
- Image optimization (WebP, responsive images)
- Pre-fetching for common routes

## 5. API Endpoints

### 5.1 Standard WordPress Endpoints
```
GET /wp-json/wp/v2/posts
GET /wp-json/wp/v2/pages
GET /wp-json/wp/v2/categories
GET /wp-json/wp/v2/tags
GET /wp-json/wp/v2/media
GET /wp-json/wp/v2/users
GET /wp-json/wp/v2/comments
```

### 5.2 Custom Endpoints

#### 5.2.1 Bootstrap & Core Endpoints
```
GET  /wp-json/custom/v1/bootstrap
     Returns: {
       siteSettings: {},
       menus: {},
       headerFooter: {},
       homepageData: {},
       podsConfig: {}
     }

GET  /wp-json/custom/v1/menus/{location}
GET  /wp-json/custom/v1/site-settings
GET  /wp-json/custom/v1/search?query={term}
GET  /wp-json/custom/v1/header-footer
```

#### 5.2.2 Ninja Forms Endpoints
```
POST /wp-json/custom/v1/forms/{form_id}/submit
     Body: { fields: {}, nonce: '' }
     Returns: { success: boolean, message: string, submission_id: number }

GET  /wp-json/custom/v1/forms/{form_id}/submissions (authenticated)
     Returns: [ { id, date, fields, status, read } ]

GET  /wp-json/custom/v1/forms/submissions/{id} (authenticated)
     Returns: { id, date, fields, status, read, metadata }

PUT  /wp-json/custom/v1/forms/submissions/{id}/status (authenticated)
     Body: { read: boolean }

GET  /wp-json/custom/v1/forms/submissions/export (authenticated)
     Returns: CSV or PDF file
```

#### 5.2.3 Pods Content Endpoints
```
GET  /wp-json/pods/v1/{pod_name}
GET  /wp-json/pods/v1/{pod_name}/{id}
POST /wp-json/pods/v1/{pod_name} (authenticated)
PUT  /wp-json/pods/v1/{pod_name}/{id} (authenticated)
```

### 5.3 Authentication Endpoints
```
POST /wp-json/jwt-auth/v1/token (if using JWT)
POST /wp-json/wp/v2/users/me (with Application Password)
```

## 6. Development Phases

### Phase 0: Schema Design & Planning (Week 1)
**Objective**: Define entire site structure in TypeScript before any WordPress setup

**Tasks**:
- Design content architecture (post types, taxonomies, relationships)
- Define all Pods structures in `wp-schema.ts`
- Create TypeScript interfaces and types
- Plan content hierarchy and fields
- Design Ninja Forms structures
- Plan menu structures
- Define site settings and options
- Organize media library structure
- Create sample/seed content data
- Document dashboard customization requirements

**Deliverables**:
- Complete `wp-schema.ts` file
- TypeScript type definitions
- Sample content data
- Media assets organized in `/media-library/` folder
- Dashboard customization specification

### Phase 1: WordPress Foundation & Automation (Week 2)
**Objective**: Set up WordPress and create automated setup system

**Tasks**:
- Install WordPress (latest version)
- Install required plugins (Pods, Ninja Forms, JWT Auth)
- Create basic headless theme structure
- Build MU-plugin structure (`cnf-setup/`)
- Create schema reader (`schema-reader.php`)
- Implement Pods builder (`pods-builder.php`)
- Implement content seeder (`content-seeder.php`)
- Implement media uploader (`media-uploader.php`)
- Create TypeScript to JSON build script
- Test schema compilation process

**Deliverables**:
- WordPress installation
- MU-plugin that reads and processes schema
- Build script (`npm run build:schema`)
- Theme with minimal templates

### Phase 2: Schema Execution & Content Setup (Week 3)
**Objective**: Execute schema and populate WordPress with all content

**Tasks**:
- Compile `wp-schema.ts` to `schema.json`
- Run MU-plugin to create all Pods
- Verify all post types created correctly
- Verify all custom fields created correctly
- Seed all content from schema
- Upload all media files programmatically
- Create navigation menus
- Configure site settings
- Create Ninja Forms from schema
- Test all created content

**Deliverables**:
- Fully populated WordPress site
- All Pods configured
- All content seeded
- Media library populated
- Forms created and configured

### Phase 3: REST API & Security (Week 3-4)
**Objective**: Build secure API layer for React frontend

**Tasks**:
- Implement JWT authentication
- Configure CORS headers
- Create bootstrap endpoint (`/wp-json/custom/v1/bootstrap`)
- Create Pods REST endpoints
- Create Ninja Forms submission endpoint
- Create submissions retrieval endpoints (authenticated)
- Implement rate limiting
- Add nonce verification for forms
- Create custom menu endpoints
- Implement security headers (CSP, etc.)
- Add input validation and sanitization
- Test all API endpoints with Postman

**Deliverables**:
- Complete REST API layer
- Authentication system
- Security measures implemented
- API documentation

### Phase 4: Dashboard Customization (Week 4)
**Objective**: Create custom WordPress admin experience

**Tasks**:
- Implement dashboard customizer in MU-plugin
- Remove unnecessary admin menu items
- Add custom admin pages (e.g., Submissions viewer)
- Customize admin branding (logo, colors)
- Add custom dashboard widgets
- Create custom admin columns for post types
- Add quick action links
- Customize login page
- Set up role-based dashboard views
- Test admin experience for different user roles

**Deliverables**:
- Fully customized WordPress dashboard
- Custom admin pages
- Branded admin interface

### Phase 5: React Frontend Foundation (Week 5)
**Objective**: Set up React Router 7 application with TypeScript

**Tasks**:
- Initialize React Router 7 project with TypeScript
- Configure Vite for React Router 7
- Set up project structure (app/routes, app/components, app/services)
- Copy types from `wp-schema.ts` to `app/types/`
- Create **root.tsx with bootstrap loader** (critical!)
- Implement authentication context (`AuthContext.tsx`)
- Create API service layer (`api.ts`, `bootstrap.ts`)
- Create **useBootstrapData hook** using useMatches()
- Set up route structure (following React Router 7 file-based routing)
- Create basic layout components (Header, Footer) that consume bootstrap data
- Implement loading states and error boundaries
- Test bootstrap API integration with root loader
- Verify useMatches() data access pattern

**Deliverables**:
- React Router 7 app structure
- root.tsx with working bootstrap loader
- TypeScript configuration
- Bootstrap data accessible via useMatches() in all components
- File-based routing setup

### Phase 6: Content & Component Development (Week 5-6)
**Objective**: Build React components for all content types

**Tasks**:
- Create page templates
- Create post/archive templates
- Build custom Pods content components
- Implement dynamic routing based on WordPress data
- Build navigation menu component
- Create search functionality
- Implement pagination
- Add 404 error page
- Build breadcrumb component
- Create SEO meta tag system
- Test all content rendering

**Deliverables**:
- Complete component library
- All content types rendering correctly
- Navigation system
- SEO implementation

### Phase 7: Ninja Forms Integration (Week 6-7)
**Objective**: Implement contact form and client portal

**Tasks**:
- Create React form components matching Ninja Forms schema
- Implement client-side validation
- Create form submission service (`forms.ts`)
- Implement nonce handling
- Build success/error message UI
- Create authenticated client portal route
- Build submission list component (`SubmissionList.tsx`)
- Build submission detail view (`SubmissionDetail.tsx`)
- Implement mark as read/unread functionality
- Add submission filtering and search
- Implement export functionality (CSV/PDF)
- Test form submission flow end-to-end
- Test client portal authentication and access

**Deliverables**:
- Working contact form
- Client portal for viewing submissions
- Export functionality

### Phase 8: Performance Optimization (Week 7)
**Objective**: Optimize for performance and user experience

**Tasks**:
- Implement code splitting
- Add lazy loading for components
- Optimize images (WebP, responsive images)
- Implement browser caching strategy
- Add service worker (optional PWA)
- Optimize bootstrap endpoint payload
- Implement prefetching for common routes
- Add loading skeletons
- Optimize API response sizes
- Run Lighthouse audits
- Performance testing and tuning

**Deliverables**:
- Optimized application
- Lighthouse score > 90
- Fast load times

### Phase 9: Testing & QA (Week 8)
**Objective**: Comprehensive testing across all systems

**Tasks**:
- Unit tests for React components (Jest)
- Integration tests for API calls
- End-to-end tests (Cypress/Playwright)
- Test schema setup process from scratch
- Test content seeding accuracy
- Test media upload process
- Security testing (penetration testing)
- Cross-browser testing
- Mobile responsiveness testing
- Form submission testing
- Authentication flow testing
- API endpoint testing
- Load testing
- Bug fixes and refinements

**Deliverables**:
- Test suite
- Bug-free application
- Security audit passed

### Phase 10: Documentation & Deployment (Week 8-9)
**Objective**: Document everything and deploy

**Tasks**:
- Write schema documentation
- Document API endpoints
- Create developer onboarding guide
- Write deployment guide
- Document setup process
- Create user manual for content editors
- Create admin guide for dashboard
- Set up production environment
- Configure production security
- Deploy React app
- Set up CI/CD pipeline
- Monitor initial deployment
- Performance monitoring setup

**Deliverables**:
- Complete documentation
- Deployed application
- Monitoring in place
- CI/CD pipeline

## 7. Technical Requirements

### 7.1 WordPress Requirements
- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache or Nginx with mod_rewrite
- SSL certificate

### 7.2 WordPress Plugins (Required)
- **Pods Framework** (for custom fields and content types) - REQUIRED
- **Ninja Forms** (for contact form functionality) - REQUIRED
- **JWT Authentication for WP REST API** or **Application Passwords** (for authentication) - REQUIRED

### 7.3 WordPress Plugins (Recommended)
- **Yoast SEO** or **RankMath** (for SEO metadata)
- **WP REST Cache** (for API response caching)
- **Safe SVG** (for SVG uploads)
- **WP Offload Media** (for CDN/S3 integration, optional)
- **Wordfence** or **Sucuri** (for security hardening)

### 7.4 Frontend Requirements
- Node.js 18+
- npm or yarn
- TypeScript 5+
- React Router 7 (framework mode)
- Modern browser support (last 2 versions)
- Vite (for React Router 7 builds)

### 7.5 Development Tools
- Git for version control
- ESLint and Prettier for code quality
- WordPress Coding Standards
- Postman or Insomnia for API testing
- ts-node for running TypeScript setup scripts
- WP-CLI for WordPress command-line operations

## 8. File Structure

### 8.1 Project Root Structure
```
cnf-wp/
├── wp-schema.ts                    # TypeScript schema definitions
├── wp-content/
│   ├── mu-plugins/
│   │   └── cnf-setup/              # MU-Plugin for automated setup
│   │       ├── cnf-setup.php       # Main plugin file
│   │       ├── includes/
│   │       │   ├── schema-reader.php
│   │       │   ├── pods-builder.php
│   │       │   ├── content-seeder.php
│   │       │   ├── media-uploader.php
│   │       │   └── dashboard-customizer.php
│   │       └── schema.json         # Compiled from wp-schema.ts
│   │
│   └── themes/
│       └── custom-headless/
│           ├── functions.php       # Theme setup, REST API customization
│           ├── style.css          # Theme header (required)
│           ├── index.php          # Fallback template
│           ├── inc/
│           │   ├── api/
│           │   │   ├── bootstrap-endpoint.php
│           │   │   ├── custom-endpoints.php
│           │   │   ├── ninja-forms-api.php
│           │   │   ├── auth.php
│           │   │   └── menu-endpoints.php
│           │   ├── dashboard-customization.php
│           │   ├── security.php
│           │   └── helpers.php
│           └── assets/
│               └── (minimal, most assets in React)
│
├── react-frontend/
│   └── (see section 8.3)
│
└── media-library/                  # Pre-organized media for upload
    ├── images/
    ├── videos/
    └── documents/
```

### 8.2 TypeScript Schema File (wp-schema.ts)
```typescript
// wp-schema.ts
export interface PodField {
  name: string;
  label: string;
  type: string;
  options?: any;
  required?: boolean;
}

export interface PodDefinition {
  name: string;
  label: string;
  type: 'post_type' | 'taxonomy' | 'pod';
  fields: PodField[];
  options?: any;
}

export interface ContentItem {
  post_type: string;
  title: string;
  content: string;
  fields?: Record<string, any>;
  featured_image?: string;
}

export interface MediaItem {
  filename: string;
  title: string;
  alt_text: string;
  caption?: string;
  description?: string;
}

export interface SiteSettings {
  site_name: string;
  tagline: string;
  logo?: string;
  // ... other settings
}

// Actual data
export const pods: PodDefinition[] = [ /* ... */ ];
export const content: ContentItem[] = [ /* ... */ ];
export const media: MediaItem[] = [ /* ... */ ];
export const siteSettings: SiteSettings = { /* ... */ };
export const menus = { /* ... */ };
export const ninjaForms = { /* ... */ };
```

### 8.3 React App Structure (React Router 7)
```
react-frontend/
├── public/
├── app/
│   ├── root.tsx                   # Root loader - bootstraps ALL data
│   ├── routes/
│   │   ├── _index.tsx            # Homepage route
│   │   ├── about.tsx             # About page route
│   │   ├── projects/
│   │   │   ├── _index.tsx        # Projects listing
│   │   │   └── $id.tsx           # Single project (dynamic)
│   │   ├── contact.tsx           # Contact form page
│   │   └── portal/
│   │       ├── _layout.tsx       # Portal layout (auth check)
│   │       ├── _index.tsx        # Submissions list
│   │       └── $id.tsx           # Single submission
│   ├── components/
│   │   ├── common/
│   │   ├── layout/
│   │   │   ├── Header.tsx
│   │   │   ├── Footer.tsx
│   │   │   └── Navigation.tsx
│   │   ├── templates/
│   │   ├── forms/
│   │   │   └── NinjaForm.tsx
│   │   └── portal/
│   │       ├── SubmissionList.tsx
│   │       └── SubmissionDetail.tsx
│   ├── services/
│   │   ├── api.ts                # Core API functions
│   │   ├── auth.ts               # Authentication service
│   │   ├── bootstrap.ts          # Bootstrap data fetcher
│   │   └── forms.ts              # Form submission service
│   ├── hooks/
│   │   ├── useBootstrapData.ts   # Access bootstrap via useMatches
│   │   ├── useForms.ts
│   │   └── useAuth.ts
│   ├── context/
│   │   └── AuthContext.tsx       # Auth state management
│   ├── types/
│   │   ├── wordpress.ts          # Types from wp-schema.ts
│   │   ├── api.ts                # API response types
│   │   └── routes.ts             # Route loader data types
│   └── utils/
│       ├── wordpress.ts          # WordPress data helpers
│       └── validation.ts
├── package.json
├── tsconfig.json
├── vite.config.ts
└── react-router.config.ts         # React Router 7 configuration
```

### 8.4 MU-Plugin Setup Flow
```
1. wp-schema.ts (TypeScript)
   ↓ (build script)
2. schema.json (compiled JSON)
   ↓ (read by MU-plugin)
3. cnf-setup.php processes:
   - Pods creation (pods-builder.php)
   - Content seeding (content-seeder.php)
   - Media uploads (media-uploader.php)
   - Dashboard customization (dashboard-customizer.php)
   ↓
4. WordPress Database (fully configured)
```

## 9. Schema-Driven Setup System

### 9.1 Overview
The schema-driven setup system allows you to define your entire WordPress structure in TypeScript, which is then automatically created in WordPress without manual configuration.

### 9.2 Setup Process

#### 9.2.1 Define Schema (wp-schema.ts)
```typescript
// Example: Define a custom post type with Pods fields
export const pods: PodDefinition[] = [
  {
    name: 'project',
    label: 'Projects',
    type: 'post_type',
    options: {
      public: true,
      has_archive: true,
      supports: ['title', 'editor', 'thumbnail']
    },
    fields: [
      {
        name: 'client_name',
        label: 'Client Name',
        type: 'text',
        required: true
      },
      {
        name: 'project_url',
        label: 'Project URL',
        type: 'website'
      },
      {
        name: 'completion_date',
        label: 'Completion Date',
        type: 'date'
      }
    ]
  }
];

// Example: Seed content
export const content: ContentItem[] = [
  {
    post_type: 'project',
    title: 'E-commerce Platform',
    content: 'Built a comprehensive e-commerce platform...',
    fields: {
      client_name: 'Acme Corp',
      project_url: 'https://acme-shop.com',
      completion_date: '2025-01-15'
    },
    featured_image: 'project-acme.jpg'
  }
];

// Example: Media to upload
export const media: MediaItem[] = [
  {
    filename: 'project-acme.jpg',
    title: 'Acme E-commerce Platform',
    alt_text: 'Screenshot of Acme e-commerce platform',
    caption: 'Homepage design'
  }
];
```

#### 9.2.2 Build Schema JSON
```bash
# Compile TypeScript to JSON
npm run build:schema
# Outputs: wp-content/mu-plugins/cnf-setup/schema.json
```

#### 9.2.3 MU-Plugin Processes Schema
The MU-plugin automatically runs on WordPress activation and:

1. **Reads schema.json**
2. **Creates Pods** using Pods API:
   ```php
   pods_api()->save_pod([
     'name' => 'project',
     'type' => 'post_type',
     'storage' => 'meta',
     'fields' => [...]
   ]);
   ```

3. **Seeds Content**:
   ```php
   $post_id = wp_insert_post([
     'post_type' => 'project',
     'post_title' => 'E-commerce Platform',
     'post_content' => '...',
     'post_status' => 'publish'
   ]);

   // Add Pod fields
   $pod = pods('project', $post_id);
   $pod->save('client_name', 'Acme Corp');
   ```

4. **Uploads Media**:
   ```php
   $file_path = '/path/to/media-library/images/project-acme.jpg';
   $attachment_id = wp_upload_bits($filename, null, file_get_contents($file_path));
   update_post_meta($attachment_id, '_wp_attachment_image_alt', 'Screenshot...');
   ```

5. **Creates Menus**:
   ```php
   $menu_id = wp_create_nav_menu('Primary Menu');
   wp_update_nav_menu_item($menu_id, 0, [
     'menu-item-title' => 'Home',
     'menu-item-url' => home_url('/'),
     'menu-item-status' => 'publish'
   ]);
   ```

6. **Customizes Dashboard**:
   ```php
   remove_menu_page('edit-comments.php');
   add_menu_page('Contact Submissions', 'Contact', 'manage_options', 'submissions');
   ```

### 9.3 Ninja Forms Integration

#### 9.3.1 Define Forms in Schema
```typescript
export const ninjaForms = [
  {
    title: 'Contact Form',
    fields: [
      { type: 'textbox', label: 'Name', required: true, key: 'name' },
      { type: 'email', label: 'Email', required: true, key: 'email' },
      { type: 'textarea', label: 'Message', required: true, key: 'message' }
    ],
    settings: {
      submit_button_text: 'Send Message',
      success_message: 'Thank you for contacting us!'
    }
  }
];
```

#### 9.3.2 MU-Plugin Creates Forms
```php
// Create Ninja Form programmatically
$form_id = Ninja_Forms()->form()->create([
  'title' => 'Contact Form'
]);

// Add fields
foreach ($form_fields as $field) {
  Ninja_Forms()->form($form_id)->field()->create($field);
}
```

### 9.4 Benefits of This Approach
- **Version Control**: Entire site structure in Git
- **Reproducible**: Deploy to multiple environments identically
- **Type Safety**: TypeScript catches errors before WordPress setup
- **Documentation**: Schema serves as living documentation
- **No Manual Setup**: Eliminates human error in field configuration
- **Migration Friendly**: Easy to update and migrate structures

## 10. Data Flow Examples

### 10.1 Bootstrap API Call with React Router 7 (Initial Page Load)

**app/root.tsx** - Root loader fetches all bootstrap data
```typescript
import { LoaderFunctionArgs } from 'react-router';
import { fetchBootstrap } from './services/bootstrap';
import type { BootstrapData } from './types/api';

// Loader runs on server/initial load - fetches ALL site data
export async function loader({ request }: LoaderFunctionArgs) {
  const bootstrapData = await fetchBootstrap();

  return {
    bootstrap: bootstrapData,
    timestamp: Date.now()
  };
}

export default function Root() {
  return (
    <html lang="en">
      <head>
        <meta charSet="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <Links />
      </head>
      <body>
        <Outlet />
        <ScrollRestoration />
        <Scripts />
      </body>
    </html>
  );
}
```

**app/services/bootstrap.ts** - Bootstrap fetch function
```typescript
export interface BootstrapData {
  siteSettings: {
    name: string;
    tagline: string;
    logo: string;
  };
  menus: {
    primary: MenuItem[];
    footer?: MenuItem[];
  };
  headerFooter: {
    header: any;
    footer: any;
  };
  homepageData: {
    hero: any;
    featured_projects: any[];
  };
}

export async function fetchBootstrap(): Promise<BootstrapData> {
  const response = await fetch('https://yoursite.com/wp-json/custom/v1/bootstrap');

  if (!response.ok) {
    throw new Error('Failed to fetch bootstrap data');
  }

  return response.json();
}
```

**app/hooks/useBootstrapData.ts** - Access bootstrap data anywhere with useMatches
```typescript
import { useMatches } from 'react-router';
import type { BootstrapData } from '../types/api';

interface RootLoaderData {
  bootstrap: BootstrapData;
  timestamp: number;
}

export function useBootstrapData() {
  const matches = useMatches();

  // Find the root route match (id: "root")
  const rootMatch = matches.find(match => match.id === 'root');

  if (!rootMatch) {
    throw new Error('Root match not found');
  }

  const { bootstrap } = rootMatch.data as RootLoaderData;

  return bootstrap;
}

// Usage in any component:
export function useSiteSettings() {
  const bootstrap = useBootstrapData();
  return bootstrap.siteSettings;
}

export function useMenus() {
  const bootstrap = useBootstrapData();
  return bootstrap.menus;
}
```

**app/components/layout/Header.tsx** - Using bootstrap data in components
```typescript
import { useBootstrapData } from '~/hooks/useBootstrapData';

export function Header() {
  const { siteSettings, menus } = useBootstrapData();

  return (
    <header>
      <img src={siteSettings.logo} alt={siteSettings.name} />
      <nav>
        {menus.primary.map(item => (
          <a key={item.id} href={item.url}>{item.title}</a>
        ))}
      </nav>
    </header>
  );
}
```

**Bootstrap API Response Example**
```json
{
  "siteSettings": {
    "name": "My Site",
    "tagline": "Just another headless site",
    "logo": "https://site.com/wp-content/uploads/logo.png"
  },
  "menus": {
    "primary": [
      { "id": 1, "title": "Home", "url": "/", "children": [] },
      { "id": 2, "title": "About", "url": "/about", "children": [] }
    ]
  },
  "headerFooter": {
    "header": { "cta_text": "Get Started", "cta_link": "/contact" },
    "footer": { "copyright": "© 2025 My Site", "social_links": [] }
  },
  "homepageData": {
    "hero": {
      "headline": "Welcome to Our Site",
      "subheadline": "Build amazing things",
      "background_image": "https://..."
    },
    "featured_projects": [
      { "id": 1, "title": "Project A", "thumbnail": "..." }
    ]
  }
}
```

**Key Benefits of React Router 7 Approach:**
1. **Single Request**: All bootstrap data loaded once in root loader
2. **No Loading States**: Data available immediately via useMatches (no useState/useEffect)
3. **Type Safe**: TypeScript types flow from loader to components
4. **Server Rendering Ready**: Loaders run on server for SSR
5. **Automatic Revalidation**: React Router handles cache invalidation
6. **No Prop Drilling**: Any component can access bootstrap data via useMatches

### 10.2 Fetching Pods Content with React Router 7 Loaders

**app/routes/projects/$id.tsx** - Dynamic route with loader
```typescript
import { useLoaderData } from 'react-router';
import type { LoaderFunctionArgs } from 'react-router';
import { useBootstrapData } from '~/hooks/useBootstrapData';

interface Project {
  id: number;
  title: string;
  content: string;
  client_name: string;
  project_url: string;
  completion_date: string;
}

// Loader fetches individual project data
export async function loader({ params }: LoaderFunctionArgs) {
  const { id } = params;

  const response = await fetch(
    `https://yoursite.com/wp-json/pods/v1/project/${id}`
  );

  if (!response.ok) {
    throw new Response('Project not found', { status: 404 });
  }

  const project: Project = await response.json();

  return { project };
}

// Component uses loader data + bootstrap data from root
export default function ProjectDetail() {
  const { project } = useLoaderData<typeof loader>();
  const { siteSettings } = useBootstrapData(); // From root.tsx loader

  return (
    <div>
      <h1>{project.title}</h1>
      <p>Client: {project.client_name}</p>
      <a href={project.project_url} target="_blank" rel="noopener">
        Visit Site
      </a>
      <time>{project.completion_date}</time>
      <div dangerouslySetInnerHTML={{ __html: project.content }} />

      {/* Site settings available from bootstrap */}
      <footer>© {siteSettings.name}</footer>
    </div>
  );
}
```

**app/routes/projects/_index.tsx** - Projects listing
```typescript
import { useLoaderData } from 'react-router';
import type { LoaderFunctionArgs } from 'react-router';

export async function loader({ request }: LoaderFunctionArgs) {
  const url = new URL(request.url);
  const page = url.searchParams.get('page') || '1';

  const response = await fetch(
    `https://yoursite.com/wp-json/pods/v1/project?per_page=10&page=${page}`
  );

  const projects = await response.json();
  const totalPages = parseInt(response.headers.get('X-WP-TotalPages') || '1');

  return { projects, totalPages, currentPage: parseInt(page) };
}

export default function ProjectsIndex() {
  const { projects, totalPages, currentPage } = useLoaderData<typeof loader>();

  return (
    <div>
      <h1>Our Projects</h1>
      <div className="projects-grid">
        {projects.map(project => (
          <a key={project.id} href={`/projects/${project.id}`}>
            <h2>{project.title}</h2>
            <p>{project.client_name}</p>
          </a>
        ))}
      </div>

      {/* Pagination */}
      <div className="pagination">
        {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
          <a
            key={page}
            href={`/projects?page=${page}`}
            className={page === currentPage ? 'active' : ''}
          >
            {page}
          </a>
        ))}
      </div>
    </div>
  );
}
```

### 10.3 Authentication Flow
```typescript
// Login
POST /wp-json/jwt-auth/v1/token
Body: { username, password }
Response: {
  token: "eyJ0eXAiOiJKV1QiLCJhbGc...",
  user_email: "user@example.com",
  user_nicename: "admin"
}

// Store token
localStorage.setItem('authToken', token);

// Authenticated Request
GET /wp-json/wp/v2/posts
Headers: {
  Authorization: `Bearer ${token}`,
  'Content-Type': 'application/json'
}
```

### 10.4 Ninja Forms Submission Flow
```typescript
// React Component: Contact Form
const ContactForm = () => {
  const [formData, setFormData] = useState({ name: '', email: '', message: '' });
  const [nonce, setNonce] = useState('');

  // Get nonce on component mount
  useEffect(() => {
    fetch('/wp-json/custom/v1/nonce')
      .then(res => res.json())
      .then(data => setNonce(data.nonce));
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();

    const response = await fetch('/wp-json/custom/v1/forms/1/submit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        fields: {
          name: formData.name,
          email: formData.email,
          message: formData.message
        },
        nonce: nonce
      })
    });

    const result = await response.json();

    if (result.success) {
      alert(result.message); // "Thank you for contacting us!"
    } else {
      alert('Error: ' + result.message);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        value={formData.name}
        onChange={(e) => setFormData({...formData, name: e.target.value})}
        placeholder="Name"
        required
      />
      {/* other fields */}
      <button type="submit">Send Message</button>
    </form>
  );
};
```

### 10.5 Client Portal - Viewing Submissions
```typescript
// React Component: Submission List (Authenticated)
const SubmissionList = () => {
  const [submissions, setSubmissions] = useState([]);
  const { token } = useAuth();

  useEffect(() => {
    fetch('/wp-json/custom/v1/forms/1/submissions', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    .then(res => res.json())
    .then(data => setSubmissions(data));
  }, [token]);

  const markAsRead = async (id) => {
    await fetch(`/wp-json/custom/v1/forms/submissions/${id}/status`, {
      method: 'PUT',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ read: true })
    });
  };

  return (
    <div className="submission-list">
      {submissions.map(sub => (
        <div key={sub.id} className={sub.read ? 'read' : 'unread'}>
          <h3>{sub.fields.name}</h3>
          <p>{sub.fields.email}</p>
          <p>{sub.fields.message}</p>
          <time>{new Date(sub.date).toLocaleDateString()}</time>
          {!sub.read && (
            <button onClick={() => markAsRead(sub.id)}>Mark as Read</button>
          )}
        </div>
      ))}
    </div>
  );
};
```

## 11. SEO Considerations

- Server-side rendering (SSR) with Next.js or Gatsby (optional upgrade)
- Meta tags from WordPress Yoast/RankMath
- Dynamic meta tag injection in React
- Sitemap generation
- Schema.org markup
- Open Graph tags
- Twitter Card tags

## 12. Testing Strategy

### 12.1 Backend Testing
- REST API endpoint testing
- Authentication testing
- Security vulnerability scanning
- Load testing for API performance

### 12.2 Frontend Testing
- Unit tests for React components (Jest, React Testing Library)
- Integration tests for API calls
- End-to-end testing (Cypress, Playwright)
- Cross-browser testing
- Responsive design testing

### 12.3 Schema & Setup Testing
- Validate TypeScript schema before compilation
- Test MU-plugin setup process in staging environment
- Verify all Pods are created correctly
- Confirm content seeding accuracy
- Test media upload process
- Validate Ninja Forms creation

## 13. Success Metrics

- API response time < 200ms
- First Contentful Paint < 1.5s
- Time to Interactive < 3.5s
- Zero security vulnerabilities
- 100% uptime for production environment
- Lighthouse score > 90 (Performance, Accessibility, Best Practices, SEO)

## 14. Reusable WordPress Install System

### 14.1 Template-Based Approach
This project is designed not just as a single website, but as a **reusable template system** for programmatically creating WordPress installations in the future.

### 14.2 How Reusability Works

#### 14.2.1 Schema as Blueprint
Each new WordPress site starts with its own `wp-schema.ts` file:

```typescript
// project-a/wp-schema.ts
export const pods = [
  { name: 'product', type: 'post_type', fields: [...] }
];

// project-b/wp-schema.ts
export const pods = [
  { name: 'team_member', type: 'post_type', fields: [...] }
];
```

#### 14.2.2 Reusable Components
The following components are completely reusable across projects:

**WordPress Components (Copy & Customize)**:
- `wp-content/mu-plugins/cnf-setup/` - The entire MU-plugin (no changes needed)
- `wp-content/themes/custom-headless/` - Theme structure (minimal customization)
- Build scripts for schema compilation

**React Components (Fork & Customize)**:
- React Router 7 structure
- Bootstrap loading pattern (root.tsx)
- useMatches data access pattern
- TypeScript type system
- API service layer

#### 14.2.3 Deployment Workflow for New Sites

**Step 1: Clone Template**
```bash
git clone https://github.com/your-org/cnf-wp-template new-project
cd new-project
```

**Step 2: Define New Schema**
```bash
# Edit wp-schema.ts with new project structure
vim wp-schema.ts
```

**Step 3: Prepare Assets**
```bash
# Add project-specific media to media-library/
cp -r /path/to/images media-library/images/
```

**Step 4: Install WordPress**
```bash
# Standard WordPress installation
wp core download
wp core config
wp core install
```

**Step 5: Install Plugins**
```bash
wp plugin install pods ninja-forms jwt-authentication-for-wp-rest-api --activate
```

**Step 6: Deploy MU-Plugin & Theme**
```bash
# Copy MU-plugin and theme to WordPress
cp -r mu-plugins/cnf-setup wp-content/mu-plugins/
cp -r themes/custom-headless wp-content/themes/
wp theme activate custom-headless
```

**Step 7: Compile & Execute Schema**
```bash
# Build schema JSON from TypeScript
npm run build:schema

# MU-plugin automatically executes on next page load
# OR manually trigger via WP-CLI:
wp eval-file wp-content/mu-plugins/cnf-setup/manual-setup.php
```

**Step 8: Build React Frontend**
```bash
cd react-frontend
npm install
npm run build
```

**Step 9: Deploy**
```bash
# Deploy to hosting
# WordPress + React build both deployed
```

**Total Time: ~30 minutes** (vs. hours/days of manual configuration)

### 14.3 Multi-Site Management Benefits

**Scenario: Agency managing 10 client sites**

**Traditional Approach**:
- 10 × (3-5 hours setup) = 30-50 hours manual configuration
- Inconsistent implementations
- Different plugin versions
- Hard to update all sites

**This Approach**:
- 10 × (30 minutes setup) = 5 hours total
- Identical architecture across all sites
- Version controlled configurations
- Update template, redeploy to all sites

### 14.4 Template Variants

You can create different template variants for different use cases:

```
cnf-wp-template/          # Base template
cnf-wp-portfolio/         # Portfolio site variant
cnf-wp-ecommerce/         # E-commerce variant
cnf-wp-blog/              # Blog variant
cnf-wp-directory/         # Directory listing variant
```

Each variant has:
- Pre-configured `wp-schema.ts` for that use case
- Customized React components
- Specific Pods configurations
- Sample content

### 14.5 Version Control Strategy

**Template Repository**:
```
cnf-wp-template/
├── wp-schema.example.ts     # Example schema (copy to wp-schema.ts)
├── mu-plugins/              # Reusable MU-plugin
├── themes/                  # Base theme
└── react-frontend/          # Base React structure
```

**Project Repositories** (forked from template):
```
client-a-website/
├── wp-schema.ts            # Client A specific schema
├── mu-plugins/             # Same as template
├── themes/                 # Potentially customized
└── react-frontend/         # Customized for client
```

### 14.6 Update Strategy

When you improve the template:
1. Update template repository
2. Cherry-pick improvements to client projects
3. Or maintain template as an npm package/git submodule

### 14.7 Documentation for Future Users

The reusable nature requires excellent documentation:
- **Quick Start Guide**: "Clone, edit schema, deploy"
- **Schema Guide**: How to define custom content types
- **Customization Guide**: Where to customize for specific needs
- **Troubleshooting**: Common issues and solutions

## 15. Key Innovations & Differentiators

This project employs several innovative approaches that set it apart from traditional WordPress development:

### 15.1 Infrastructure as Code (IaC) for WordPress
Unlike traditional WordPress development where content structure is manually configured through the admin interface, this project treats the entire site structure as code:

- **Single Source of Truth**: `wp-schema.ts` defines everything
- **Version Controlled**: All site structure in Git
- **Reproducible**: Deploy identical sites across environments
- **Type Safe**: TypeScript catches errors before they reach WordPress
- **Testable**: Schema can be validated before execution

### 15.2 Zero-Click Setup
The MU-plugin automation eliminates hours of manual configuration:

**Traditional Approach**:
1. Install WordPress
2. Install plugins
3. Manually create each custom post type
4. Manually configure each field
5. Manually create taxonomies
6. Manually upload media
7. Manually create content
8. Manually configure menus
9. Manually customize dashboard
*Time: Multiple hours to days*

**This Approach**:
1. Install WordPress and plugins
2. Run `npm run build:schema`
3. Activate MU-plugin
*Time: Minutes*

### 15.3 Type Safety Across Stack
TypeScript types flow through the entire application:

```
wp-schema.ts (Source of Truth)
    ↓
WordPress Pods (via MU-plugin)
    ↓
REST API Response
    ↓
React TypeScript Components
```

This ensures that the React frontend always expects exactly what WordPress provides.

### 15.4 Optimized Data Loading
The bootstrap endpoint solves the "multiple requests" problem:

**Traditional Headless WordPress**:
- 1 request for site settings
- 1 request for menus
- 1 request for header data
- 1 request for footer data
- 1 request for page content
*Total: 5+ requests on initial load*

**This Approach**:
- 1 request for bootstrap (contains all of the above)
*Total: 1 request on initial load*

### 15.5 Secure Forms with Client Portal
Most headless WordPress sites struggle with forms. This project provides a complete solution:

- Forms defined in schema
- Secure submission via REST API
- Authenticated client portal for viewing submissions
- No third-party services required (can be added optionally)
- Full control over form data

### 15.6 Developer Experience (DX)
This architecture provides exceptional DX:

- **No Context Switching**: Define structure in code, not admin UI
- **Modern Tooling**: TypeScript, ESLint, Prettier, Git
- **Fast Iteration**: Change schema, rebuild, see results
- **Clear Documentation**: Schema IS the documentation
- **Confidence**: Type safety prevents many common bugs

## 17. Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Schema compilation errors | High | TypeScript validation, comprehensive testing before WordPress setup |
| Pods API changes/incompatibility | High | Lock plugin versions, test updates in staging |
| MU-plugin setup failures | Critical | Extensive error logging, rollback mechanism, backup before setup |
| Media upload failures (large files) | Medium | Chunked uploads, increase PHP limits, retry logic |
| Ninja Forms spam submissions | Medium | Implement rate limiting, nonce verification, CAPTCHA integration |
| API rate limiting issues | High | Implement caching, optimize bootstrap endpoint, batch requests |
| CORS configuration errors | High | Test thoroughly in all environments, document configuration |
| Authentication vulnerabilities | Critical | Security audit, use JWT best practices, token expiration |
| SEO degradation (headless) | Medium | Consider SSR with Next.js, implement meta tags, generate sitemap |
| Performance bottlenecks | Medium | Implement lazy loading, code splitting, CDN for assets |
| TypeScript/PHP type mismatch | Medium | Generate PHP types from TS schema, validation layer |
| Content seeding data conflicts | Medium | Implement conflict resolution, idempotent operations |
| Client portal unauthorized access | Critical | Strict authentication checks, role-based access control |
| Dashboard customization breaking updates | Medium | Test WordPress updates in staging, minimize core modifications |

## 18. Future Enhancements

### 18.1 Performance & Infrastructure
- Progressive Web App (PWA) capabilities with offline support
- Advanced caching with Redis for API responses
- GraphQL layer (WPGraphQL) as alternative to REST
- Server-side rendering with Next.js for improved SEO
- Edge computing with Cloudflare Workers

### 18.2 Features & Functionality
- Multi-language support (WPML or Polylang integration)
- Real-time updates (WebSockets for live notifications)
- Advanced search with Elasticsearch or Algolia
- Content versioning and revision history in React
- Drag-and-drop page builder for editors
- AI-powered content suggestions

### 18.3 Forms & Interactions
- Multi-step forms with progress indicators
- Form analytics (completion rates, drop-off points)
- Conditional logic in forms based on user input
- File upload with drag-and-drop
- Digital signature capture
- Payment integration (Stripe, PayPal) for paid forms

### 18.4 Client Portal Enhancements
- Real-time notifications for new submissions
- Two-way messaging between clients and admin
- Custom reporting and analytics dashboard
- Automated workflows (e.g., auto-assign submissions)
- Integration with project management tools (Trello, Asana)
- Mobile app for client portal (React Native)

### 18.5 Schema & Automation
- Visual schema builder/editor (GUI for wp-schema.ts)
- Schema migration tools (upgrade/downgrade capabilities)
- Automated content migration from other platforms
- Schema validation and testing tools
- Version control for schema changes
- Automatic TypeScript type generation from Pods schema

### 18.6 Analytics & Monitoring
- Enhanced analytics integration (Google Analytics 4, Mixpanel)
- Real-time performance monitoring
- Error tracking and reporting (Sentry)
- User behavior tracking and heatmaps
- A/B testing framework
- Custom event tracking

### 18.7 Security Enhancements
- Two-factor authentication (2FA)
- Single Sign-On (SSO) integration
- Advanced rate limiting per user role
- Audit logs for all admin actions
- Automated security scanning
- GDPR compliance tools (data export, deletion)

### 18.8 Template System Enhancements
- CLI tool to scaffold new projects: `npx create-cnf-wp my-project`
- Interactive schema builder (GUI for creating wp-schema.ts)
- Template marketplace with pre-built variants
- Automated testing for schema validation
- Schema diffing tools (compare schema versions)
- Migration scripts for schema updates
- NPM package for MU-plugin (easier updates)
- Template versioning and changelog
- Documentation generator from schema
- Visual diagram generator (content type relationships)

## 19. Documentation Deliverables

### 19.1 Technical Documentation
- **Schema Documentation**: How to define and modify `wp-schema.ts`
- **API Documentation**: All endpoints, parameters, responses, authentication
- **MU-Plugin Documentation**: How the setup system works, troubleshooting
- **Component Library**: React component documentation with TypeScript types
- **Type Definitions**: Documentation of all TypeScript interfaces and types
- **Pods Integration**: How Pods are created and managed from schema

### 19.2 Operational Documentation
- **Deployment Guide**: Step-by-step deployment process for all environments
- **Setup Process**: How to run schema compilation and WordPress setup
- **Content Management**: How to add/modify content via schema
- **Media Management**: How to add media files programmatically
- **Dashboard Customization**: How to modify WordPress admin interface

### 19.3 User Documentation
- **Content Editor Manual**: Guide for using WordPress admin to manage content
- **Client Portal Guide**: How to access and view form submissions
- **Form Management**: How to view and manage contact form submissions

### 19.4 Security & Best Practices
- **Security Guide**: Authentication, API security, best practices
- **Performance Guide**: Optimization strategies, caching, CDN setup
- **Troubleshooting Guide**: Common issues and solutions

## 20. Project Specifications (CNF Template)

### 20.1 Custom Post Types & Fields

#### cnf-machines
**Purpose**: Showcase CNF machines with detailed specifications and media

**Fields**:
- `title` (text) - Machine name
- `content` (wysiwyg) - Full description
- `featured_image` (image) - Main product image
- `specifications` (paragraph) - General specifications text
- `dimensions` (text) - Dimensions (L x W x H)
- `weight` (number) - Weight in kg/lbs
- `power_requirements` (text) - Power specs
- `capacity` (text) - Machine capacity
- `performance_metrics` (wysiwyg) - Performance details
- `media_gallery` (file/gallery) - Multiple images, videos, 3D models, documents
- `datasheet_pdf` (file) - Downloadable specification sheet
- `video_url` (url) - YouTube/Vimeo video link

**Taxonomies**:
- `machine_category` - Categories for machines
- `machine_industry` - Industry applications

#### cnf-uses
**Purpose**: Document use cases and applications for machines

**Fields**:
- `title` (text) - Use case name
- `content` (wysiwyg) - Full description
- `featured_image` (image) - Representative image
- `related_machines` (relationship) - Link to cnf-machines post type
- `industry` (text) - Industry/sector
- `application_type` (text) - Type of application
- `case_study_title` (text) - Success story title
- `case_study_content` (wysiwyg) - Case study details
- `case_study_results` (wysiwyg) - Outcomes/results
- `before_image` (image) - Before photo
- `after_image` (image) - After photo
- `client_testimonial` (paragraph) - Optional quote

**Taxonomies**:
- `use_category` - Categorize by use type
- `industry_sector` - Industry categorization

#### faq
**Purpose**: Frequently asked questions

**Fields**:
- `question` (text) - The question
- `answer` (wysiwyg) - The answer
- `category` (text) - FAQ category
- `order` (number) - Display order

**Taxonomies**:
- `faq_category` - Group FAQs by topic

### 20.2 Forms Configuration

#### Contact Form (Basic)
**Fields**:
- Name (required)
- Email (required)
- Phone (optional)
- Message (required)

**Settings**:
- Success message: "Thank you for contacting us! We'll respond within 24 hours."
- Email notification to: admin email
- Store in database: Yes

#### Quote Request Form
**Fields**:
- Name (required)
- Email (required)
- Company (optional)
- Phone (optional)
- Machine Interest (dropdown - populated from cnf-machines)
- Project Details (textarea, required)
- Budget Range (dropdown: <$10k, $10k-$50k, $50k-$100k, >$100k, Not sure)
- Timeline (dropdown: Urgent, 1-3 months, 3-6 months, 6+ months)
- How did you hear about us? (text)

**Settings**:
- Success message: "Thank you for your quote request! Our team will contact you within 1 business day."
- Email notification to: admin + sales team
- Store in database: Yes
- Tag submissions: "quote-request"

### 20.3 Client Portal Access
- **Access Level**: Admin only
- **Capabilities**: View all form submissions, mark as read/unread, export to CSV
- **No client self-service**: Clients contact via forms, admins manage internally

### 20.4 Dashboard Customization

**Remove/Hide**:
- Comments menu item
- Default Posts (use custom post types instead)
- Default WordPress dashboard widgets
- Theme/plugin editor (security)
- Tools → Import/Export (prevent accidental issues)

**Custom Branding**:
- Upload custom admin logo
- Customize login page (logo, background, colors)
- Custom admin color scheme
- Footer text: "Powered by CNF WordPress System"

**Custom Admin Pages**:
- "Form Submissions" page - View contact and quote submissions
- "Analytics Dashboard" - Basic stats (page views, form submissions, popular machines)
- Quick links widget on dashboard

**Menu Reordering** (top to bottom):
1. Dashboard
2. Form Submissions (custom)
3. CNF Machines
4. CNF Uses
5. FAQs
6. Media Library
7. Menus
8. Users
9. Settings

### 20.5 Third-Party Integrations

**Google Analytics**:
- Track page views
- Track form submissions as events
- Track machine page views
- Track quote request conversions

**Email Marketing** (Mailchimp or SendGrid):
- Add contact form submissions to mailing list
- Optional: Add quote requesters to separate list
- Double opt-in for GDPR compliance

### 20.6 Template Variants Strategy

**Primary Template: CNF Template**
- cnf-machines, cnf-uses, faq post types
- Quote request + contact forms
- Machine-focused design
- Technical specification displays

**Secondary Template: Business/Corporate**
- Services post type
- Team members post type
- Testimonials post type
- Basic contact form
- About/services-focused design

**Template Repository Structure**:
```
cnf-wp-templates/
├── base/                        # Shared base components
│   ├── mu-plugins/cnf-setup/
│   ├── themes/base-headless/
│   └── react-frontend/base/
├── variants/
│   ├── cnf/                     # CNF machines template
│   │   ├── wp-schema.ts
│   │   └── react-frontend/
│   └── business/                # Business/corporate template
│       ├── wp-schema.ts
│       └── react-frontend/
└── README.md
```

### 20.7 Deployment Strategy

**Hosting**: Managed WordPress (WP Engine, Kinsta, or Flywheel)

**Environments**:
- **Development**: Local (LocalWP or MAMP)
- **Staging**: Managed WP staging environment
- **Production**: Managed WP production

**Schema Management**:
- **Re-runnable (safe)**: Schema can be re-executed to add new fields/types
- **Conflict resolution**: Existing content preserved, only new items added
- **Rollback**: Backup before schema updates

### 20.8 Development Approach

**Timeline**: Full-featured build (all PRD features before launch)

**Phases** (from Section 6):
- Phase 0: Schema Design (Week 1)
- Phase 1-4: WordPress setup, API, Dashboard (Weeks 2-4)
- Phase 5-6: React Router 7 frontend (Weeks 5-6)
- Phase 7: Forms & Portal (Weeks 6-7)
- Phase 8-10: Optimization, Testing, Deployment (Weeks 7-9)

**Total Timeline**: 9 weeks to production-ready system

## 21. Removed: Open Questions

All open questions have been answered and documented in Section 20: Project Specifications.

### 20.1 Content & Structure
- [ ] What specific custom post types are needed? (e.g., Projects, Team Members, Services)
- [ ] What custom taxonomies are required? (e.g., Project Categories, Service Types)
- [ ] What relationships exist between content types? (e.g., Projects linked to Team Members)
- [ ] What custom fields are needed for each post type?
- [ ] Will content editors need to preview content before publishing?
- [ ] Should the schema allow for content updates, or is it one-time setup only?

### 20.2 Forms & Client Portal
- [ ] What forms are needed beyond basic contact form? (e.g., quote requests, applications)
- [ ] What user roles should have access to the client portal?
- [ ] Should clients only see their own submissions, or all submissions?
- [ ] Are email notifications needed for form submissions? To whom?
- [ ] Should there be automated responses to form submissions?
- [ ] What export formats are needed? (CSV, PDF, Excel?)

### 20.3 Authentication & Users
- [ ] What level of user authentication is needed? (public only, user accounts, client accounts)
- [ ] What custom user roles are required? (e.g., Client, Editor, Manager)
- [ ] Should users be able to register themselves, or admin-created only?
- [ ] What capabilities should each user role have?
- [ ] Should users be created from the schema as well?

### 20.4 Media & Assets
- [ ] What is the expected size/volume of media files?
- [ ] Are there specific image size requirements for different contexts?
- [ ] Should video hosting be integrated (YouTube, Vimeo) or self-hosted?
- [ ] Are there any special file types that need to be supported?

### 20.5 Dashboard Customization
- [ ] What specific admin menu items should be hidden?
- [ ] What custom admin pages are needed?
- [ ] What custom dashboard widgets should be added?
- [ ] Should there be different dashboard views for different roles?
- [ ] What branding elements should be customized? (logo, colors, login page)

### 20.6 Integration & Third-Party Services
- [ ] Are there third-party integrations required? (payment gateways, CRM, email marketing)
- [ ] Should form submissions integrate with any external services? (Mailchimp, Salesforce, etc.)
- [ ] Is Google Analytics or other analytics needed?
- [ ] Are there any API integrations needed (weather, maps, social media)?

### 20.7 Performance & Hosting
- [ ] What is the expected traffic/scale?
- [ ] What are the hosting/infrastructure constraints?
- [ ] Is CDN integration required?
- [ ] Are there specific performance targets? (load time, Time to Interactive)
- [ ] Should the site work offline (PWA)?

### 20.8 SEO & Marketing
- [ ] Is multilingual support required?
- [ ] What is the SEO strategy? (SSR needed?)
- [ ] Are there specific schema.org markup requirements?
- [ ] Should there be social media sharing functionality?
- [ ] Are Open Graph tags needed for social sharing?

### 20.9 E-commerce (If Applicable)
- [ ] Will there be e-commerce functionality?
- [ ] What payment gateways are needed?
- [ ] Should WooCommerce be integrated, or custom solution?

### 20.10 Development & Deployment
- [ ] What environments are needed? (dev, staging, production)
- [ ] Should the schema setup be re-runnable or one-time only?
- [ ] How should schema updates be handled after initial setup?
- [ ] What is the branching strategy? (gitflow, trunk-based)
- [ ] What CI/CD platform will be used? (GitHub Actions, GitLab CI, Jenkins)

### 20.11 Reusability & Template System
- [ ] How should the template repository be structured for maximum reusability?
- [ ] What parts should be customizable vs. locked down in the template?
- [ ] Should there be different template variants for different use cases?
- [ ] How should template updates be distributed to existing projects?
- [ ] Should the MU-plugin be published as a standalone package?
- [ ] What naming conventions for project-specific forks?
- [ ] Should there be a CLI tool to scaffold new projects from the template?
- [ ] What level of documentation is needed for future developers using the template?
