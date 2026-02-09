# Converting full-wp.ts to wp-schema.ts

This guide shows how to transform your React app data (`full-wp.ts`) into WordPress schema format.

## Quick Start

```bash
cd /Users/neil/Documents/Wordsco/cnf-wordpress-theme
cp wp-schema.example.ts wp-schema.ts
```

## Data Mapping Guide

### 1. CNF Machines (T50, T70, T95, T150)

**From full-wp.ts:**
```typescript
// app/lib/constants/full-wp.ts
export const cnfMachines = [
  {
    id: 31,
    slug: "t50",
    title: { rendered: "T50 - 500kg Tracked Mini Dumper" },
    pods: {
      title: "COMPACT POWER",
      description: "Perfect for tight spaces...",
      specifications: "Compact design with impressive 500kg payload...",
      dimensions: "1850 x 750 x 1200 mm",
      weight: 650,
      load_capacity: "500kg (1102 lbs)",
      engine: "Honda GX240 or Yanmar L70",
      track_width: "680mm (26.7 inch)",
      speed: "2 speeds: 2.5-3.5 km/h",
      selling_points: [
        "Outstanding performance on rough or soft terrain",
        "Perfect for tight or restricted-access spaces",
        // ...more
      ],
      gallery_images: [
        { ID: 201, guid: "/uploads/cnf-t50-machines-T50-106.webp", alt: "..." }
      ],
      machine_weight_kg: 500,
      machine_width_cm: 68,
      actions: ["high-tip-self-loading", "high-tip", "self-loading"],
      bg_color: "dark"
    }
  }
]
```

**To wp-schema.ts:**
```typescript
// wp-schema.ts - sampleContent array
export const sampleContent: ContentItem[] = [
  {
    post_type: "cnf_machine",
    title: "T50 - 500kg Tracked Mini Dumper",
    content: "<p>The T50 is our compact 500kg capacity tracked mini dumper, perfect for restricted access sites...</p>",
    status: "publish",
    featured_image: "cnf-t50-1.webp", // Just filename, file should exist in uploads/
    fields: {
      // Marketing content
      title: "COMPACT POWER",
      description: "Perfect for tight spaces and residential projects. The T50 delivers exceptional performance...",

      // Specifications
      specifications: "Compact design with impressive 500kg payload capacity. Ideal for landscaping, construction, and agricultural applications.",
      dimensions: "1850 x 750 x 1200 mm",
      weight: 650,
      power_requirements: "Honda GX240 or Yanmar L70",
      capacity: "500kg load capacity",
      load_capacity: "500kg (1102 lbs)",
      operating_weight: "500kg",
      engine: "Honda GX240 or Yanmar L70",
      track_width: "680mm (26.7 inch)",
      speed: "2 speeds: 2.5-3.5 km/h",
      performance_metrics: "<ul><li>Speed: 0-6 km/h</li><li>Tipping angle: 45°</li><li>Ground clearance: 150mm</li></ul>",

      // Array fields (Pods repeatable fields)
      selling_points: [
        "Outstanding performance on rough or soft terrain",
        "Perfect for tight or restricted-access spaces",
        "Highly efficient for repetitive heavy lifting",
        "Simple, intuitive controls",
        "Built for durability and long service life"
      ],

      technical_specs: [
        "3 track rollers with rocking system",
        "Hydrostatic transmission",
        "Electric or manual start",
        "Triple hydraulic gear pump"
      ],

      // Machine characteristics
      machine_weight_kg: 500,
      machine_width_cm: 68,
      actions: ["high-tip-self-loading", "high-tip", "self-loading"],
      bg_color: "dark",
      next_section_id: "t70",

      // Documents (must exist in uploads/)
      brochure_url: "cnf-brochure-1.webp",
      video_url: "https://youtube.com/watch?v=example",
      diagram: "cnf-t50-diagram.webp"
    },
    terms: {
      machine_category: ["Track Mounted"],
      machine_industry: ["Construction", "Landscaping", "Agriculture"]
    }
  },

  // Repeat for T70, T95, T150...
];
```

### 2. Gallery Images

**Important:** Don't include gallery_images in the schema - they'll be auto-linked after media import!

WordPress will automatically associate images with posts based on:
- Featured image: Set via `featured_image: "filename.webp"`
- Gallery: All imported images are available in Media Library

### 3. FAQs

**From full-wp.ts:**
```typescript
// Not in full-wp.ts yet, but structure would be:
export const faqs = [
  {
    question: "What is the warranty period?",
    answer: "All CNF machines come with a standard 2-year warranty...",
    category: "General"
  }
]
```

**To wp-schema.ts:**
```typescript
export const sampleContent: ContentItem[] = [
  // ...machines above

  {
    post_type: "faq",
    title: "What is the warranty period?", // Also used as question
    content: "",
    status: "publish",
    fields: {
      question: "What is the warranty period for CNF machines?",
      answer: "<p>All CNF machines come with a standard 2-year warranty covering parts and labor. Extended warranty options are available for up to 5 years.</p>",
      category: "General",
      key_question: true, // Mark as featured
      order: 1
    },
    terms: {
      faq_category: ["General"]
    }
  }
];
```

### 4. Use Cases / Applications

**From full-wp.ts:**
```typescript
// cnfUses array structure
export const cnfUses = [
  {
    slug: "construction-groundworks",
    title: { rendered: "Construction & Groundworks" },
    pods: {
      intro: "CNF mini dumpers are essential for construction sites...",
      industry: "Construction",
      applications: [
        "Site clearance and preparation",
        "Material transport",
        "Concrete pouring"
      ]
    }
  }
]
```

**To wp-schema.ts:**
```typescript
export const sampleContent: ContentItem[] = [
  // ...machines and faqs above

  {
    post_type: "cnf_use",
    title: "Construction & Groundworks",
    content: "<p>CNF mini dumpers are essential equipment for construction sites, offering versatility and power...</p>",
    status: "publish",
    featured_image: "cnf-construction-use.webp",
    fields: {
      intro: "CNF mini dumpers are essential for construction sites, providing reliable material transport in challenging conditions.",
      industry: "Construction",
      application_type: "Heavy-duty construction work",
      applications: [
        "Site clearance and preparation",
        "Material transport across rough terrain",
        "Concrete pouring and distribution",
        "Groundworks and excavation support"
      ],
      // Optional case study
      case_study_title: "Major Housing Development Project",
      case_study_content: "<p>A leading UK construction firm needed efficient material transport...</p>",
      case_study_results: "<ul><li>50% reduction in labor time</li><li>Improved site safety</li></ul>"
    },
    terms: {
      use_category: ["Construction"]
    }
  }
];
```

## Step-by-Step Conversion Process

### 1. Create wp-schema.ts
```bash
cd /Users/neil/Documents/Wordsco/cnf-wordpress-theme
cp wp-schema.example.ts wp-schema.ts
```

### 2. Add Your Machines

Open `wp-schema.ts` and scroll to the `sampleContent` section (around line 819).

Replace the example content with your T50, T70, T95, T150 data from `full-wp.ts`.

**Key Points:**
- Use `featured_image: "filename.webp"` not full paths
- All images must exist in `uploads/` (already done! ✅)
- Arrays like `selling_points` are straightforward - just copy them
- Remove `gallery_images` - WordPress handles this automatically

### 3. Add Your FAQs

Add FAQ content items to the `sampleContent` array:

```typescript
{
  post_type: "faq",
  title: "Question text here",
  fields: {
    question: "Full question text",
    answer: "<p>HTML answer with formatting</p>",
    category: "General" // or Technical, Purchasing, etc.
  }
}
```

### 4. Add Use Cases (Optional)

If you want to seed use case content, add `cnf_use` items.

### 5. Compile Schema

```bash
npm run build:schema
```

This creates `wp-content/mu-plugins/cnf-setup/schema.json`

### 6. Deploy to WP Engine

```bash
git add .
git commit -m "Add CNF machines content schema"
git push wpengine main
```

### 7. Reset Setup to Re-run

```bash
ssh westgategroup@westgategroup.ssh.wpengine.net
wp option delete cnf_setup_completed --path=/nas/content/live/westgategroup/
```

### 8. Trigger Setup

Visit: https://westgategroup.wpenginepowered.com/wp-admin/

The MU-plugin will:
- Create all Pods (if not already created)
- Seed your machines content
- Link featured images
- Create FAQs
- Set up taxonomies

## Quick Reference: Field Mapping

| full-wp.ts (pods field) | wp-schema.ts (fields object) | Type |
|-------------------------|------------------------------|------|
| `title` | `title` | string |
| `description` | `description` | string |
| `specifications` | `specifications` | string |
| `dimensions` | `dimensions` | string |
| `weight` | `weight` | number |
| `selling_points` | `selling_points` | string[] |
| `technical_specs` | `technical_specs` | string[] |
| `gallery_images` | ❌ Skip (auto-linked) | - |
| `machine_weight_kg` | `machine_weight_kg` | number |
| `actions` | `actions` | string[] |
| `featured_media` (ID) | `featured_image` (filename) | string |

## Example: Complete T50 Entry

```typescript
{
  post_type: "cnf_machine",
  title: "T50 - 500kg Tracked Mini Dumper",
  content: "<p>The T50 is our compact 500kg capacity tracked mini dumper, perfect for restricted access sites and tight working spaces. Combining Italian engineering with practical British requirements, the T50 delivers exceptional performance with its compact design.</p>",
  status: "publish",
  featured_image: "cnf-t50-1.webp",
  fields: {
    title: "COMPACT POWER",
    description: "Perfect for tight spaces and residential projects. The T50 delivers exceptional performance with its compact design, making it ideal for landscaping and small construction sites. Double-speed translation and triple hydraulic pump distinguish the T50 model, with its 1102 lbs capacity being perfect for soft or rough ground work.",
    specifications: "Compact design with impressive 500kg payload capacity. Ideal for landscaping, construction, and agricultural applications.",
    dimensions: "1850 x 750 x 1200 mm",
    weight: 650,
    power_requirements: "Honda GX240 or Yanmar L70",
    capacity: "500kg load capacity",
    load_capacity: "500kg (1102 lbs)",
    operating_weight: "500kg",
    engine: "Honda GX240 or Yanmar L70",
    track_width: "680mm (26.7 inch)",
    speed: "2 speeds: 2.5-3.5 km/h",
    performance_metrics: "<ul><li>Speed: 0-6 km/h</li><li>Tipping angle: 45°</li><li>Ground clearance: 150mm</li></ul>",
    selling_points: [
      "Outstanding performance on rough or soft terrain",
      "Perfect for tight or restricted-access spaces",
      "Highly efficient for repetitive heavy lifting",
      "Simple, intuitive controls",
      "Built for durability and long service life"
    ],
    technical_specs: [
      "3 track rollers with rocking system",
      "Hydrostatic transmission",
      "Electric or manual start",
      "Triple hydraulic gear pump",
      "Manual or electric starting"
    ],
    machine_weight_kg: 500,
    machine_width_cm: 68,
    actions: ["high-tip-self-loading", "high-tip", "self-loading"],
    bg_color: "dark",
    next_section_id: "t70",
    brochure_url: "cnf-brochure-1.webp",
    diagram: "cnf-t50-diagram.webp"
  },
  terms: {
    machine_category: ["Track Mounted"],
    machine_industry: ["Construction", "Landscaping", "Agriculture"]
  }
}
```

## Troubleshooting

### Images Not Showing
- Verify filename matches exactly: `cnf-t50-1.webp` not `/uploads/cnf-t50-1.webp`
- Check file exists: `ssh westgategroup@westgategroup.ssh.wpengine.net "ls sites/westgategroup/wp-content/uploads/cnf-t50-1.webp"`

### Fields Not Saving
- Check field names match Pods definition in `wp-schema.ts` (around line 146)
- Verify data types match (string vs array vs number)

### Setup Not Running
- Reset: `wp option delete cnf_setup_completed --path=/nas/content/live/westgategroup/`
- Check log: `cat sites/westgategroup/wp-content/mu-plugins/cnf-setup/setup.log`

## Next Steps

1. Create `wp-schema.ts` with your machines
2. Compile: `npm run build:schema`
3. Deploy: `git push wpengine main`
4. Reset setup: `wp option delete cnf_setup_completed`
5. Visit `/wp-admin/` to trigger setup
6. Verify in WordPress admin
7. Test Bootstrap API
8. Connect React app!

---

**Need help?** Check the example in `wp-schema.example.ts` or the full data structure in `../app/lib/constants/full-wp.ts`
