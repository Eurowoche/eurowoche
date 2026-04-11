# Europäische Jugendwoche - Hugo Website Setup

## Project Overview
Complete Hugo website for "Europäische Jugendwoche" (European Youth Week) - a nonprofit cultural folklore festival at Burg Ludwigstein.

## Design Specifications
- **Primary Color:** #8B6B61 (warm terracotta/brown)
- **Background:** #F5F0E8 (cream)
- **Alternating Sections:** #E8D8D0 (mauve/rose)
- **Text Color:** #2C2C2C
- **Footer Background:** #D4C8B8
- **Fonts:**
  - Headings: Cormorant Garamond (italic)
  - Body: Lato

## Languages
- **German (DE)** - Default language at root `/`
- **English (EN)** - Prefixed with `/en/`

## File Structure

### Core Configuration
- `hugo.toml` - Main Hugo configuration with multilingual setup, navigation menus, and site parameters

### Layout Templates
- `layouts/_default/baseof.html` - Base HTML template with head, header, main content, footer, and script includes
- `layouts/index.html` - Homepage with 9 major sections:
  1. Hero section (split layout with CTA buttons)
  2. Heart of Eurowoche (triangle circle arrangement)
  3. Upcoming events
  4. Markets teaser
  5. Testimonial slider
  6. Get involved (3 image cards)
  7. Photo gallery grid
  8. Shop teaser
  9. Contact form
- `layouts/_default/single.html` - Default single page template

### Partials
- `layouts/partials/header.html` - Header with logo, menu toggle, language switcher
- `layouts/partials/footer.html` - 3-column footer with branding, contact info, social links, legal links
- `layouts/partials/contact-form.html` - Contact form with Formspree integration

### Styling
- `assets/css/style.css` - Complete stylesheet with:
  - CSS custom properties for colors and fonts
  - Responsive grid layouts
  - Menu overlay (fullscreen)
  - Hover effects and animations
  - Mobile-first responsive design
  - Components for all page sections

### JavaScript
- `assets/js/main.js` - Client-side functionality:
  - Menu toggle and overlay handling
  - Testimonial slider with auto-advance (5s interval)
  - Dot navigation for testimonials
  - Smooth scroll for anchor links
  - Intersection Observer for scroll animations

### Internationalization (i18n)
- `i18n/de.toml` - German translations
- `i18n/en.toml` - English translations

### Data Files
- `data/testimonials.toml` - Testimonial quotes with names and countries

### Content
- `content/de/_index.md` - German homepage content
- `content/en/_index.md` - English homepage content

## Getting Started

### Prerequisites
- Hugo 0.87+ (for module support)
- A web server for deployment

### Development
```bash
cd /sessions/epic-awesome-albattani/eurowoche
hugo server -D
```

Visit `http://localhost:1313` in your browser.

### Building for Production
```bash
hugo
```

Output will be in the `public/` directory.

## Key Features

### Responsive Design
- Desktop-first approach with mobile breakpoints at 768px and 480px
- Fluid typography and layouts
- Touch-friendly navigation

### Multilingual Support
- German (default) and English languages
- Language switcher in header
- Automatic hreflang tags for SEO

### Interactive Elements
- Fullscreen navigation overlay (hamburger menu)
- Auto-advancing testimonial slider with dot navigation
- Hover animations on images and cards
- Smooth scrolling

### Accessibility
- Semantic HTML structure
- ARIA labels on buttons
- Proper heading hierarchy
- Alt text for images

## Customization

### Colors
Edit the CSS custom properties at the top of `assets/css/style.css`

### Navigation
Modify menu items in `hugo.toml` under the `[menus]` section

### Testimonials
Add/edit testimonials in `data/testimonials.toml`

### Contact Form
Update the Formspree endpoint in `layouts/partials/contact-form.html`

## Image Requirements

The following image paths are referenced and need to be added:
- `/img/logo.png` - Site logo
- `/img/hero/hero-home.jpg` - Homepage hero image
- `/img/hero/circle-*.jpg` - Three circle images for heart section
- `/img/involvement/*.jpg` - Three involvement/action images
- `/img/gallery/*.jpg` - Gallery images (8 total, gallery-1 through gallery-8)
- Event and market images as needed

## Next Steps
1. Add image assets to `/static/img/` directories
2. Create content pages for all menu items
3. Customize form endpoint with your Formspree account
4. Update contact information in `hugo.toml`
5. Add specific testimonials
6. Deploy to your hosting provider

## Support
For questions about Hugo, visit: https://gohugo.io/documentation/
