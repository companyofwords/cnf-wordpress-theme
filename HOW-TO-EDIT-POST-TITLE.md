# How to Edit Post Title in WordPress

## Block Editor (Gutenberg)

The post title is the **very first field** at the top of the page:

```
┌──────────────────────────────────────────────────┐
│  ← Back to Machines                   [Update]   │
├──────────────────────────────────────────────────┤
│                                                   │
│  Add title                    ← Click here!      │
│  ‾‾‾‾‾‾‾‾‾                                       │
│                                                   │
│  ┌─────────────────────────────────────────┐     │
│  │ + Start writing or type / to choose...  │     │
│  └─────────────────────────────────────────┘     │
│                                                   │
│  ─── CNF Machine Fields ───                      │
│  Model: [____]                                   │
│  Marketing Title: [____]                         │
│                                                   │
└──────────────────────────────────────────────────┘
```

## Steps:

1. **Open the machine for editing**
   - Go to: Machines → All Machines
   - Click on any machine (e.g., "COMPACT POWER")

2. **Find the title field**
   - Look at the VERY TOP of the page
   - You should see "Add title" or the current title in large text
   - It might say "COMPACT POWER" already

3. **Click on the title**
   - Click directly on the title text
   - The cursor should appear

4. **Edit the title**
   - Select all the text (Cmd+A / Ctrl+A)
   - Type the new title: "T50"

5. **Save**
   - Click the blue "Update" button (top right)

## Can't See Title Field?

### Option 1: You're in Classic Editor
If you see a different layout, you might be using Classic Editor:

```
┌──────────────────────────────────────────┐
│  Edit Machine                             │
├──────────────────────────────────────────┤
│  Title: [COMPACT POWER____________]      │  ← Title here
│                                           │
│  Permalink: /cnf-machine/...              │
│                                           │
│  ┌──────────────────────────────────┐    │
│  │ Content editor (TinyMCE)         │    │
│  │                                  │    │
│  └──────────────────────────────────┘    │
└──────────────────────────────────────────┘
```

### Option 2: Screen Options
1. Look for **"Screen Options"** tab (top right corner)
2. Click it to expand
3. Make sure **"Title"** is checked

### Option 3: Switch Editors
If using Block Editor and can't see title:
- Look for "⋮" (three dots) in top right
- Click → Preferences
- Make sure title is enabled

## Troubleshooting

**Problem:** Title field is grayed out
**Solution:** You might not have permissions. Check with site admin.

**Problem:** Title changes don't save
**Solution:**
1. Check browser console for errors (F12)
2. Try different browser
3. Disable plugins temporarily

**Problem:** Title field missing entirely
**Solution:** Your user role might not have access. Contact admin.
