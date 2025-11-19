# Scroll to Top Button

## Overview

Replaced the floating share button with a "Scroll to Top" button that appears when scrolling down the page.

## Features

### Appearance
- **Icon**: Arrow up (↑) instead of share icon
- **Color**: Blue (#3498db) instead of red
- **Position**: Fixed bottom-right corner (20px from edges)
- **Size**: 50px circle (45px on mobile)
- **Style**: Circular button with shadow

### Behavior

#### Show/Hide Logic:
- **Hidden**: When at top of page (< 300px scroll)
- **Visible**: When scrolled down 300px or more
- **Smooth**: Fade in/out with scale animation

#### Animations:
- **Appear**: Fade in + scale from 0.8 to 1.0
- **Disappear**: Fade out + scale to 0.8
- **Hover**: Scale to 1.1 + darker blue
- **Click**: Scale to 0.95 (press effect)

#### Functionality:
- **Click**: Smooth scroll to top of page
- **Smooth Scroll**: Uses `behavior: 'smooth'`
- **Performance**: Debounced scroll listener (100ms)

## Technical Details

### HTML (in public_footer.php):
```html
<button id="scroll-to-top-btn" class="scroll-to-top-btn">
    <i class="fas fa-arrow-up"></i>
</button>
```

### CSS Styling:
- Fixed positioning
- Circular shape (border-radius: 50%)
- Box shadow for depth
- Smooth transitions (0.3s)
- Responsive sizing

### JavaScript:
- Show/hide based on scroll position (300px threshold)
- Smooth scroll to top on click
- Hover effects
- Debounced scroll event for performance

## Thresholds

- **Show Button**: 300px scroll down
- **Hide Button**: Less than 300px from top
- **Debounce**: 100ms delay on scroll events

## Styling States

### Default (Hidden):
```css
display: none;
opacity: 0;
transform: scale(0.8);
```

### Visible:
```css
display: flex;
opacity: 1;
transform: scale(1);
```

### Hover:
```css
transform: scale(1.1);
background: #2980b9;
box-shadow: 0 6px 16px rgba(0,0,0,0.3);
```

### Active (Clicking):
```css
transform: scale(0.95);
```

## Mobile Responsive

### Desktop:
- Size: 50px × 50px
- Position: 20px from bottom/right
- Font size: 20px

### Mobile (≤768px):
- Size: 45px × 45px
- Position: 15px from bottom/right
- Font size: 18px

## Performance Optimizations

1. **Debouncing**: Scroll events debounced to 100ms
2. **Passive Listener**: `{ passive: true }` for better scroll performance
3. **Will-change**: CSS hint for GPU acceleration
4. **RAF**: Uses requestAnimationFrame for smooth animations

## Browser Compatibility

✅ Chrome/Edge
✅ Firefox
✅ Safari
✅ Mobile browsers

Uses standard CSS and JavaScript features.

## User Experience

### Benefits:
- Quick return to top without manual scrolling
- Only appears when needed (not cluttering top of page)
- Smooth animations feel professional
- Intuitive arrow icon
- Hover feedback confirms interactivity

### Accessibility:
- `aria-label="Scroll to top"` for screen readers
- Keyboard accessible (can be focused and activated)
- Clear visual feedback on hover/click
- High contrast colors

## Changes from Previous

### Before:
- Share button (red, share icon)
- Always visible when scrolling
- Opened share modal

### After:
- Scroll to top button (blue, arrow up)
- Only visible after 300px scroll
- Scrolls page to top smoothly

---

**Last Updated**: November 18, 2025
