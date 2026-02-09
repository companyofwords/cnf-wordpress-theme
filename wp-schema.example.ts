/**
 * CNF WordPress Schema Definition
 *
 * This file defines the complete structure of the WordPress site:
 * - Custom post types (Pods)
 * - Custom fields
 * - Taxonomies
 * - Menus
 * - Forms (Ninja Forms)
 * - Site settings
 * - Sample content (optional seed data)
 *
 * After editing, compile with: npm run build:schema
 */

// ============================================================================
// TYPE DEFINITIONS
// ============================================================================

export interface PodField {
  name: string;
  label: string;
  type: string;
  required?: boolean;
  options?: any;
  description?: string;
}

export interface PodDefinition {
  name: string;
  label: string;
  label_singular: string;
  type: "post_type" | "taxonomy";
  storage?: "meta" | "table";
  fields: PodField[];
  options?: {
    public?: boolean;
    has_archive?: boolean;
    menu_icon?: string;
    supports?: string[];
    hierarchical?: boolean;
    rewrite?: { slug: string };
  };
}

export interface TaxonomyDefinition {
  name: string;
  label: string;
  label_singular: string;
  type: "taxonomy";
  post_types: string[];
  options?: {
    hierarchical?: boolean;
    public?: boolean;
    rewrite?: { slug: string };
  };
}

export interface ContentItem {
  post_type: string;
  title: string;
  content: string;
  status?: "publish" | "draft";
  fields?: Record<string, any>;
  featured_image?: string;
  terms?: Record<string, string[]>;
}

export interface MediaItem {
  filename: string;
  title: string;
  alt_text: string;
  caption?: string;
  description?: string;
}

export interface MenuItem {
  title: string;
  url: string;
  type?: "custom" | "post_type" | "taxonomy";
  object?: string;
  object_id?: number;
  children?: MenuItem[];
}

export interface MenuDefinition {
  location: string;
  name: string;
  items: MenuItem[];
}

export interface NinjaFormField {
  type: string;
  label: string;
  key: string;
  required?: boolean;
  options?: any;
  placeholder?: string;
  description?: string;
}

export interface NinjaFormDefinition {
  title: string;
  fields: NinjaFormField[];
  settings: {
    submit_button_text: string;
    success_message: string;
    email_to?: string;
    email_subject?: string;
  };
}

export interface SiteSettings {
  site_name: string;
  tagline: string;
  logo?: string;
  favicon?: string;
  contact_email: string;
  social_links?: {
    facebook?: string;
    twitter?: string;
    linkedin?: string;
    youtube?: string;
  };
}

// ============================================================================
// CUSTOM POST TYPES (PODS)
// ============================================================================

export const pods: PodDefinition[] = [
  // CNF Machines Post Type
  {
    name: "cnf_machine",
    label: "CNF Machines",
    label_singular: "CNF Machine",
    type: "post_type",
    storage: "meta",
    options: {
      public: true,
      has_archive: true,
      menu_icon: "dashicons-admin-tools",
      supports: ["title", "editor", "thumbnail", "excerpt"],
      rewrite: { slug: "machines" },
    },
    fields: [
      // Marketing Content (from full-wp.ts)
      {
        name: "title",
        label: "Marketing Title",
        type: "text",
        description: "e.g., 'COMPACT POWER', 'VERSATILE WORKHORSE'",
      },
      {
        name: "description",
        label: "Marketing Description",
        type: "wysiwyg",
        description: "Main marketing description paragraph",
      },

      // Basic Specifications
      {
        name: "specifications",
        label: "General Specifications",
        type: "wysiwyg",
        description: "General specification text",
      },
      {
        name: "dimensions",
        label: "Dimensions",
        type: "text",
        description: "Format: L x W x H (e.g., 2000 x 1500 x 1800 mm)",
      },
      {
        name: "weight",
        label: "Weight",
        type: "number",
        description: "Weight in kg",
      },
      {
        name: "power_requirements",
        label: "Power Requirements",
        type: "text",
        description: "e.g., Honda GX240 or Yanmar L70",
      },
      {
        name: "capacity",
        label: "Capacity",
        type: "text",
        description: "Machine capacity - e.g., '500kg load capacity'",
      },
      {
        name: "load_capacity",
        label: "Load Capacity (Detailed)",
        type: "text",
        description: "e.g., '500kg (1102 lbs)' or '700kg (1543 lbs) / 600kg (1322 lbs) high-tip'",
      },
      {
        name: "operating_weight",
        label: "Operating Weight",
        type: "text",
        description: "Operating weight as string",
      },
      {
        name: "engine",
        label: "Engine",
        type: "text",
        description: "Engine model - e.g., 'Honda GX240 or Yanmar L70'",
      },
      {
        name: "track_width",
        label: "Track Width",
        type: "text",
        description: "e.g., '680mm (26.7 inch)'",
      },
      {
        name: "speed",
        label: "Speed",
        type: "text",
        description: "e.g., '2 speeds: 2.5-3.5 km/h'",
      },
      {
        name: "performance_metrics",
        label: "Performance Metrics",
        type: "wysiwyg",
        description: "HTML list of performance details",
      },

      // Array Fields (CRITICAL - these need special handling)
      {
        name: "selling_points",
        label: "Selling Points",
        type: "paragraph",
        options: {
          repeatable: true,
        },
        description: "Array of selling points - one per row",
      },
      {
        name: "technical_specs",
        label: "Technical Specifications",
        type: "text",
        options: {
          repeatable: true,
        },
        description: "Array of technical specs - one per row",
      },
      {
        name: "actions",
        label: "Machine Actions/Features",
        type: "pick",
        options: {
          pick_object: "custom-simple",
          pick_custom: [
            "high-tip",
            "self-loading",
            "swivel",
            "high-tip-self-loading",
          ],
          pick_format_type: "multi",
          pick_format_multi: "checkbox",
        },
        description: "Select all applicable features",
      },
      {
        name: "gallery_images",
        label: "Gallery Images",
        type: "file",
        options: {
          file_format_type: "images",
          file_uploader: "plupload",
          file_limit: 20,
          multiple: true,
        },
        description: "Multiple gallery images with ID, guid, alt structure",
      },

      // Complex JSON Field (needs special handling)
      {
        name: "floating_stats",
        label: "Floating Stats/CTAs (JSON)",
        type: "code",
        options: {
          code_language: "json",
        },
        description: "JSON array: [{type, position, bg_color, delay, text, href, external}]",
      },

      // Files and Media
      {
        name: "media_gallery",
        label: "Media Gallery (Legacy)",
        type: "file",
        options: {
          file_format_type: "images",
          file_uploader: "plupload",
          file_limit: 10,
          multiple: true,
        },
        description: "Simplified media gallery",
      },
      {
        name: "datasheet_pdf",
        label: "Datasheet PDF",
        type: "file",
        options: {
          file_format_type: "any",
          file_type: "attachment",
        },
        description: "Downloadable specification sheet",
      },
      {
        name: "brochure_url",
        label: "Brochure URL",
        type: "website",
        description: "URL to brochure image or PDF",
      },
      {
        name: "video_url",
        label: "Video URL",
        type: "website",
        description: "YouTube or Vimeo video URL",
      },
      {
        name: "diagram",
        label: "Technical Diagram",
        type: "file",
        options: {
          file_format_type: "images",
        },
        description: "Technical diagram image",
      },

      // Machine Characteristics (for filtering)
      {
        name: "machine_weight_kg",
        label: "Machine Weight (kg)",
        type: "number",
        description: "Weight in kg for filtering - e.g., 500, 700, 900, 1500",
      },
      {
        name: "machine_width_cm",
        label: "Machine Width (cm)",
        type: "number",
        description: "Width in cm for filtering - e.g., 68, 78, 94",
      },
      {
        name: "bg_color",
        label: "Background Color",
        type: "pick",
        options: {
          pick_object: "custom-simple",
          pick_custom: ["light", "dark"],
          pick_format_type: "single",
          pick_format_single: "dropdown",
        },
        description: "UI background color for this machine section",
      },
      {
        name: "next_section_id",
        label: "Next Section ID",
        type: "text",
        description: "Slug of next machine (e.g., 't70', 't95', 't150')",
      },

      // Relationships
      {
        name: "recommended_uses",
        label: "Recommended Uses",
        type: "pick",
        options: {
          pick_object: "post_type",
          pick_val: "cnf_use",
          pick_format_type: "multi",
          pick_format_multi: "checkbox",
        },
        description: "Related use cases for this machine",
      },
    ],
  },

  // CNF Uses Post Type
  {
    name: "cnf_use",
    label: "CNF Uses",
    label_singular: "CNF Use",
    type: "post_type",
    storage: "meta",
    options: {
      public: true,
      has_archive: true,
      menu_icon: "dashicons-lightbulb",
      supports: ["title", "editor", "thumbnail"],
      rewrite: { slug: "uses" },
    },
    fields: [
      // Relationships
      {
        name: "related_machines",
        label: "Related Machines",
        type: "pick",
        options: {
          pick_object: "post_type",
          pick_val: "cnf_machine",
          pick_format_type: "multi",
          pick_format_multi: "checkbox",
        },
        description: "Select machines related to this use case",
      },

      // Content Fields
      {
        name: "intro",
        label: "Introduction",
        type: "paragraph",
        description: "Introduction text for the use case",
      },
      {
        name: "applications",
        label: "Applications List",
        type: "text",
        options: {
          repeatable: true,
        },
        description: "Array of application examples - one per row",
      },

      // Basic Info
      {
        name: "industry",
        label: "Industry",
        type: "text",
        description: "e.g., Construction, Landscaping, Agriculture",
      },
      {
        name: "application_type",
        label: "Application Type",
        type: "text",
        description: "Type of application or use case",
      },

      // Case Study Fields
      {
        name: "case_study_title",
        label: "Case Study Title",
        type: "text",
      },
      {
        name: "case_study_content",
        label: "Case Study Details",
        type: "wysiwyg",
        description: "Detailed case study content",
      },
      {
        name: "case_study_results",
        label: "Case Study Results",
        type: "wysiwyg",
        description: "Outcomes and results achieved",
      },
      {
        name: "before_image",
        label: "Before Image",
        type: "file",
        options: {
          file_format_type: "images",
        },
      },
      {
        name: "after_image",
        label: "After Image",
        type: "file",
        options: {
          file_format_type: "images",
        },
      },
      {
        name: "client_testimonial",
        label: "Client Testimonial",
        type: "paragraph",
        description: "Optional quote from the client",
      },
    ],
  },

  // FAQ Post Type
  {
    name: "faq",
    label: "FAQs",
    label_singular: "FAQ",
    type: "post_type",
    storage: "meta",
    options: {
      public: true,
      has_archive: true,
      menu_icon: "dashicons-editor-help",
      supports: ["title"],
      rewrite: { slug: "faq" },
    },
    fields: [
      {
        name: "question",
        label: "Question",
        type: "text",
        required: true,
      },
      {
        name: "answer",
        label: "Answer",
        type: "wysiwyg",
        required: true,
      },
      {
        name: "category",
        label: "Category",
        type: "text",
        description: "e.g., General, Technical, Purchasing",
      },
      {
        name: "key_question",
        label: "Key/Featured Question",
        type: "boolean",
        description: "Mark as a key/featured question for highlighting",
      },
      {
        name: "order",
        label: "Display Order",
        type: "number",
        description: "Lower numbers appear first",
      },
    ],
  },
];

// ============================================================================
// TAXONOMIES
// ============================================================================

export const taxonomies: TaxonomyDefinition[] = [
  {
    name: "machine_category",
    label: "Machine Categories",
    label_singular: "Machine Category",
    type: "taxonomy",
    post_types: ["cnf_machine"],
    options: {
      hierarchical: true,
      public: true,
      rewrite: { slug: "machine-category" },
    },
  },
  {
    name: "machine_industry",
    label: "Machine Industries",
    label_singular: "Machine Industry",
    type: "taxonomy",
    post_types: ["cnf_machine"],
    options: {
      hierarchical: false,
      public: true,
      rewrite: { slug: "industry" },
    },
  },
  {
    name: "use_category",
    label: "Use Categories",
    label_singular: "Use Category",
    type: "taxonomy",
    post_types: ["cnf_use"],
    options: {
      hierarchical: true,
      public: true,
      rewrite: { slug: "use-category" },
    },
  },
  {
    name: "faq_category",
    label: "FAQ Categories",
    label_singular: "FAQ Category",
    type: "taxonomy",
    post_types: ["faq"],
    options: {
      hierarchical: true,
      public: true,
      rewrite: { slug: "faq-category" },
    },
  },
];

// ============================================================================
// NINJA FORMS
// ============================================================================

export const ninjaForms: NinjaFormDefinition[] = [
  // Basic Contact Form
  {
    title: "Contact Form",
    fields: [
      {
        type: "textbox",
        label: "Name",
        key: "name",
        required: true,
        placeholder: "Your name",
      },
      {
        type: "email",
        label: "Email",
        key: "email",
        required: true,
        placeholder: "your@email.com",
      },
      {
        type: "phone",
        label: "Phone",
        key: "phone",
        required: false,
        placeholder: "+1 (555) 000-0000",
      },
      {
        type: "textarea",
        label: "Message",
        key: "message",
        required: true,
        placeholder: "How can we help you?",
      },
    ],
    settings: {
      submit_button_text: "Send Message",
      success_message:
        "Thank you for contacting us! We'll respond within 24 hours.",
      email_to: "admin@example.com",
      email_subject: "New Contact Form Submission",
    },
  },

  // Quote Request Form
  {
    title: "Quote Request Form",
    fields: [
      {
        type: "textbox",
        label: "Name",
        key: "name",
        required: true,
      },
      {
        type: "email",
        label: "Email",
        key: "email",
        required: true,
      },
      {
        type: "textbox",
        label: "Company",
        key: "company",
        required: false,
      },
      {
        type: "phone",
        label: "Phone",
        key: "phone",
        required: false,
      },
      {
        type: "listselect",
        label: "Machine of Interest",
        key: "machine_interest",
        required: false,
        options: {
          // Will be populated dynamically from cnf_machine post type
          dynamic: true,
          post_type: "cnf_machine",
        },
      },
      {
        type: "textarea",
        label: "Project Details",
        key: "project_details",
        required: true,
        description: "Please describe your project and requirements",
      },
      {
        type: "listselect",
        label: "Budget Range",
        key: "budget_range",
        required: false,
        options: {
          choices: [
            { value: "less-10k", label: "Less than $10,000" },
            { value: "10k-50k", label: "$10,000 - $50,000" },
            { value: "50k-100k", label: "$50,000 - $100,000" },
            { value: "more-100k", label: "More than $100,000" },
            { value: "not-sure", label: "Not sure yet" },
          ],
        },
      },
      {
        type: "listselect",
        label: "Timeline",
        key: "timeline",
        required: false,
        options: {
          choices: [
            { value: "urgent", label: "Urgent (ASAP)" },
            { value: "1-3-months", label: "1-3 months" },
            { value: "3-6-months", label: "3-6 months" },
            { value: "6-plus-months", label: "6+ months" },
          ],
        },
      },
      {
        type: "textbox",
        label: "How did you hear about us?",
        key: "referral_source",
        required: false,
      },
    ],
    settings: {
      submit_button_text: "Request Quote",
      success_message:
        "Thank you for your quote request! Our team will contact you within 1 business day.",
      email_to: "sales@example.com",
      email_subject: "New Quote Request",
    },
  },
];

// ============================================================================
// NAVIGATION MENUS
// ============================================================================

export const menus: MenuDefinition[] = [
  {
    location: "primary",
    name: "Primary Menu",
    items: [
      {
        title: "Home",
        url: "/",
        type: "custom",
      },
      {
        title: "Machines",
        url: "/machines",
        type: "post_type",
        object: "cnf_machine",
        children: [
          // Will be populated dynamically with machine categories
        ],
      },
      {
        title: "Applications",
        url: "/uses",
        type: "post_type",
        object: "cnf_use",
      },
      {
        title: "FAQ",
        url: "/faq",
        type: "post_type",
        object: "faq",
      },
      {
        title: "Contact",
        url: "/contact",
        type: "custom",
      },
      {
        title: "Get Quote",
        url: "/quote",
        type: "custom",
      },
    ],
  },
  {
    location: "footer",
    name: "Footer Menu",
    items: [
      {
        title: "About",
        url: "/about",
        type: "custom",
      },
      {
        title: "Privacy Policy",
        url: "/privacy",
        type: "custom",
      },
      {
        title: "Terms of Service",
        url: "/terms",
        type: "custom",
      },
    ],
  },
];

// ============================================================================
// SITE SETTINGS
// ============================================================================

export const siteSettings: SiteSettings = {
  site_name: "CNF Machines",
  tagline: "Industrial Solutions Provider",
  logo: "logo.png", // Will be uploaded from media-library/
  favicon: "favicon.ico",
  contact_email: "info@cnfmachines.com",
  social_links: {
    linkedin: "https://linkedin.com/company/cnf-machines",
    youtube: "https://youtube.com/@cnfmachines",
    facebook: "https://facebook.com/cnfmachines",
  },
};

// ============================================================================
// SAMPLE CONTENT (Optional - for initial setup)
// ============================================================================

export const sampleContent: ContentItem[] = [
  // Sample Machine
  {
    post_type: "cnf_machine",
    title: "CNC Milling Machine Model X500",
    content:
      "<p>High-precision CNC milling machine designed for industrial manufacturing applications. Features advanced servo control and multi-axis capabilities.</p>",
    status: "publish",
    featured_image: "machine-x500.jpg",
    fields: {
      specifications:
        "Professional-grade CNC milling machine with digital control system",
      dimensions: "2000 x 1500 x 1800 mm",
      weight: 3500,
      power_requirements: "380V, 50Hz, 15kW",
      capacity: "1000 x 600 x 500 mm working area",
      performance_metrics:
        "<ul><li>Spindle speed: 0-12,000 RPM</li><li>Feed rate: 1-8000 mm/min</li><li>Positioning accuracy: ±0.005mm</li></ul>",
    },
    terms: {
      machine_category: ["CNC Machines"],
      machine_industry: ["Manufacturing", "Metalworking"],
    },
  },

  // Sample Use Case
  {
    post_type: "cnf_use",
    title: "Precision Parts Manufacturing",
    content:
      "<p>Learn how our CNC machines are used for precision parts manufacturing in the aerospace industry.</p>",
    status: "publish",
    featured_image: "use-precision-parts.jpg",
    fields: {
      industry: "Aerospace",
      application_type: "Precision Manufacturing",
      case_study_title: "Aircraft Component Production",
      case_study_content:
        "<p>A leading aerospace manufacturer needed to produce high-precision aluminum components...</p>",
      case_study_results:
        "<ul><li>50% reduction in production time</li><li>99.98% accuracy rate</li><li>30% cost savings</li></ul>",
      client_testimonial:
        '"The CNF CNC machine transformed our production capabilities. We can now meet the strictest aerospace tolerances consistently."',
    },
    terms: {
      use_category: ["Manufacturing"],
    },
  },

  // Sample FAQ
  {
    post_type: "faq",
    title: "What is the warranty period?",
    content: "",
    status: "publish",
    fields: {
      question: "What is the warranty period for CNF machines?",
      answer:
        "<p>All CNF machines come with a standard 2-year warranty covering parts and labor. Extended warranty options are available for up to 5 years.</p>",
      category: "General",
      order: 1,
    },
    terms: {
      faq_category: ["General"],
    },
  },
];

// ============================================================================
// MEDIA LIBRARY
// ============================================================================

export const mediaLibrary: MediaItem[] = [
  {
    filename: "logo.png",
    title: "CNF Machines Logo",
    alt_text: "CNF Machines company logo",
  },
  {
    filename: "favicon.ico",
    title: "CNF Favicon",
    alt_text: "CNF icon",
  },
  {
    filename: "machine-x500.jpg",
    title: "CNC Milling Machine X500",
    alt_text: "CNC Milling Machine Model X500",
    caption: "High-precision CNC milling machine",
    description:
      "Professional-grade CNC milling machine for industrial applications",
  },
  {
    filename: "use-precision-parts.jpg",
    title: "Precision Parts Manufacturing",
    alt_text: "Precision manufactured parts",
    caption: "High-precision aerospace components",
  },
];

// ============================================================================
// DASHBOARD CUSTOMIZATION
// ============================================================================

export const dashboardCustomization = {
  remove_menu_items: [
    "edit-comments.php",
    "edit.php", // Default posts
    "tools.php",
    "theme-editor.php",
    "plugin-editor.php",
  ],
  custom_branding: {
    admin_logo: "logo.png",
    login_logo: "logo.png",
    admin_color_scheme: "modern",
    login_background: "#f0f0f0",
    footer_text: "Powered by CNF WordPress System",
  },
  menu_order: [
    "index.php", // Dashboard
    "form-submissions", // Custom page
    "edit.php?post_type=cnf_machine",
    "edit.php?post_type=cnf_use",
    "edit.php?post_type=faq",
    "upload.php", // Media
    "nav-menus.php", // Menus
    "users.php",
    "options-general.php", // Settings
  ],
  custom_dashboard_widgets: [
    {
      title: "Recent Form Submissions",
      callback: "display_recent_submissions",
      position: "normal",
    },
    {
      title: "Quick Stats",
      callback: "display_quick_stats",
      position: "side",
    },
  ],
};

// ============================================================================
// INTEGRATIONS
// ============================================================================

export const integrations = {
  google_analytics: {
    enabled: true,
    tracking_id: "G-XXXXXXXXXX", // Replace with actual GA4 tracking ID
    track_events: [
      { event: "form_submission", category: "engagement" },
      { event: "machine_view", category: "product" },
      { event: "quote_request", category: "conversion" },
    ],
  },
  email_marketing: {
    enabled: true,
    provider: "mailchimp", // or 'sendgrid'
    api_key: "", // Set via environment variable
    lists: {
      contact_form: "general-inquiries",
      quote_requests: "sales-leads",
    },
    double_opt_in: true,
  },
};

// ============================================================================
// EXPORT ALL
// ============================================================================

export default {
  pods,
  taxonomies,
  ninjaForms,
  menus,
  siteSettings,
  sampleContent,
  mediaLibrary,
  dashboardCustomization,
  integrations,
};
