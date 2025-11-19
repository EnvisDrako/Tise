# Taset Academy - Sticky Header with Scroll Effect

## Behavior Overview

The header changes layout dynamically when you scroll down the page.

### Initial State (Top of Page)

```
┌─────────────────────────────────────┐
│                                     │
│         [TASET ACADEMY LOGO]        │  ← Logo centered
│                                     │
│  Home | About▼ | Courses | Gallery▼│  ← Menu centered
│  Notices | Downloads | Contact      │
│                                     │
└─────────────────────────────────────┘
```

**Features:**
- Logo: Centered at top (50px height)
- Menu: Centered below logo
- Padding: 0.75rem vertical
- Gap between logo and menu: 0.75rem

### Scrolled State (After scrolling 50px)

```
┌─────────────────────────────────────┐
│ [LOGO] Home | About▼ | Courses | Gallery▼ | Notices | Contact │
└─────────────────────────────────────┘
```

**Features:**
- Logo: Moves to LEFT (40px height - smaller)
- Menu: Stays CENTERED (takes remaining space)
- Padding: 0.5rem vertical (more compact)
- Horizontal layout (all in one line)
- Enhanced shadow for depth

## Technical Details

### Scroll Trigger
- Activates when: `window.scrollY > 50` (50 pixels from top)
- Smooth transition: 0.3s ease

### Layout Changes

#### Before Scroll:
- Container: `flex-direction: column` (vertical stack)
- Logo: Centered, 50px height
- Menu: Centered below logo
- Gap: 0.75rem

#### After Scroll:
- Container: `flex-direction: row` (horizontal)
- Logo: Left side, 40px height (20% smaller)
- Menu: Center, flex-grow: 1
- Gap: 2rem between elements
- Padding: Reduced to 0.5rem

### Visual Effects

1. **Logo Shrink**: 50px → 40px (smooth transition)
2. **Layout Shift**: Vertical → Horizontal
3. **Shadow Enhancement**: Stronger shadow when scrolled
4. **Spacing Reduction**: Less white space

### Dropdown Adjustments

**Before Scroll:**
- Dropdowns centered under menu item
- Transform: `translateX(-50%)`

**After Scroll:**
- Dropdowns aligned to left of menu item
- Transform: `translateY(0)` only

### Mobile Behavior

On mobile (≤768px):
- Layout stays CENTERED even when scrolled
- Logo shrinks: 50px → 35px
- Vertical stack maintained
- Hamburger menu in top-right corner

## CSS Classes

### JavaScript Adds:
- `.scrolled` to `.main-header`
- `.scrolled` to `.nav-container`

### Triggered Styles:
```css
.main-header.scrolled { }
.nav-container.scrolled { }
.nav-container.scrolled .nav-logo { }
.nav-container.scrolled .nav-menu { }
.nav-container.scrolled .nav-logo img { }
```

## Benefits

✅ **Less White Space**: Compact header when scrolling
✅ **Better UX**: Logo accessible on left, menu centered
✅ **More Content**: Reduced header = more page content visible
✅ **Professional**: Smooth animations and transitions
✅ **Sticky**: Header stays visible while scrolling
✅ **Responsive**: Works on all screen sizes

## Customization

### Adjust Scroll Trigger Point:
```javascript
if (window.scrollY > 50) { // Change 50 to your preferred value
```

### Adjust Logo Sizes:
```css
/* Initial */
.nav-logo img.logo-img { height: 50px; }

/* Scrolled */
.nav-container.scrolled .nav-logo img.logo-img { height: 40px; }
```

### Adjust Padding:
```css
/* Initial */
.nav-container { padding: 0.5rem 2rem; }

/* Scrolled */
.nav-container.scrolled { padding: 0.5rem 2rem; }
```

## Browser Compatibility

✅ Chrome/Edge (Modern)
✅ Firefox
✅ Safari
✅ Mobile browsers

Uses standard CSS transitions and JavaScript scroll events.

---

**Last Updated**: November 18, 2025
