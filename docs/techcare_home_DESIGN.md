---
name: TechCare Home System
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#434655'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#737686'
  outline-variant: '#c3c6d7'
  surface-tint: '#0053db'
  primary: '#004ac6'
  on-primary: '#ffffff'
  primary-container: '#2563eb'
  on-primary-container: '#eeefff'
  inverse-primary: '#b4c5ff'
  secondary: '#006c49'
  on-secondary: '#ffffff'
  secondary-container: '#6cf8bb'
  on-secondary-container: '#00714d'
  tertiary: '#784b00'
  on-tertiary: '#ffffff'
  tertiary-container: '#996100'
  on-tertiary-container: '#ffeedd'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea8'
  secondary-fixed: '#6ffbbe'
  secondary-fixed-dim: '#4edea3'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005236'
  tertiary-fixed: '#ffddb8'
  tertiary-fixed-dim: '#ffb95f'
  on-tertiary-fixed: '#2a1700'
  on-tertiary-fixed-variant: '#653e00'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: 56px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '800'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.015em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 34px
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  title-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 26px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: 0.01em
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.03em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  space-xxs: 0.25rem
  space-xs: 0.5rem
  space-sm: 0.75rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 3rem
  space-3xl: 4rem
  gutter-mobile: 1rem
  gutter-desktop: 1.5rem
  margin-mobile: 1rem
  margin-tablet: 2rem
  margin-desktop: 3rem
  container-max-width: 1120px
---

## Brand & Style

This design system expresses a warm, welcoming, and reassuringly dependable aesthetic tailored for everyday homeowners dealing with stressful hardware breakdowns. The experience prioritizes absolute transparency, instant relief, and human accessibility.

### Design Movement & Ethos
- **Warm Minimalist & Humanist Tech:** Expansive white space, tactile rounded cards, and gentle ambient elevations replace technical jargon or utilitarian service dashboards.
- **Tone & Demeanor:** Reassuring, friendly, transparent, and prompt. The interface should feel more like a trusted neighborhood technician ringing the doorbell than an impersonal corporate ticketing portal.
- **Visual Tenets:**
  - Zero intimidating enterprise tables, density toggles, or cryptic diagnostic codes.
  - Large touch targets, explicit status badges, and illustrated hardware categories.
  - Soft-edged containers with cheerful micro-interactions that celebrate problem resolution.

## Colors

The palette is engineered for trust and clarity in a radiant, light-first canvas. Deep royal blue commands confidence and authority, while an energetic emerald green reinforces verified technicians, active bookings, and resolved repairs.

### Palette Architecture
- **Primary (`#2563EB` - Trust Blue):** Used for primary action buttons, focused states, interactive selections, and key navigation highlights.
- **Secondary / Accent (`#10B981` - Resolution Emerald):** Signifies confirmed appointments, completed repairs, verified diagnostic badges, and price guarantees.
- **Tertiary / Warning (`#F59E0B` - Amber Alert):** Applied selectively for in-progress diagnostics, rescheduling notices, and urgent technician notes.
- **Neutrals (Slate Tones):**
  - Surface Foundation: `#F8FAFC` (Canvas background)
  - Surface Elevated: `#FFFFFF` (Pristine white container tiles)
  - Subdued Borders: `#E2E8F0` (Delicate hairline structural guides)
  - Secondary Text: `#64748B` (Explanatory metadata and captions)
  - Primary Text: `#0F172A` (Crisp, high-contrast headings and readable body text)

### Application Rules
Never place primary blue text over tinted blue fills. Pair color-tinted status containers (e.g., `#ECFDF5` emerald tint) with saturated typography (`#065F46`) to preserve high-readability WCAG AAA contrast ratios.

## Typography

Plus Jakarta Sans powers all typographical tiers, ensuring a warm, modern, and open geometric presence across all devices.

### Hierarchy & Legibility Directives
- **Displays & Headlines:** Set in bold and extra-bold weights with subtle negative tracking (`-0.02em` to `-0.01em`) to maintain a friendly punch without feeling aggressive.
- **Body:** Kept generous with an open line-height (`1.5` to `1.55`) so service descriptions, diagnostic questions, and warranty terms read effortlessly for older demographics.
- **Labels & Numbers:** Prices and arrival estimates must pair `title-md` or `headline-sm` with tabular numerical figures to eliminate jitter during real-time cost updates.

## Layout & Spacing

The layout is deliberately uncrowded, adopting an airy, fixed-container grid philosophy capped at `1120px` to maintain a calm, consumer-first perspective.

### Grid & Breakpoint Logic
- **Desktop (1024px+):** Centered 12-column grid, `24px` (`1.5rem`) gutters, with substantial vertical padding (`48px` to `64px` section dividers).
- **Tablet (768px – 1023px):** 8-column layout, `20px` gutters, and `32px` page margins. Side-by-side selection panels collapse to balanced two-up tiles.
- **Mobile (Below 768px):** 4-column layout, `16px` outer margins. Hardware category pickers transition into horizontal snap-scrollers or single-column stackable cards.

### Spacing Cadence
Employ an 8pt base grid for layout structure, with 4pt allowances for inline micro-spacing (icon-to-label gaps, tag paddings). Avoid tight stacks; every card must breathe with at least `24px` internal padding on mobile and `32px` on desktop.

## Elevation & Depth

This design system avoids harsh, dark drop shadows. Visual depth relies on a layered surface approach combining crisp white containers against soft slate tint backgrounds, complemented by ultra-soft, color-tinted ambient shadows.

### Surface Elevation Tiers
1. **Level 0 (Canvas Base):** Flat `#F8FAFC`. Houses the global backdrop and background sections.
2. **Level 1 (Card Rest):** Solid `#FFFFFF` elevated with a diffuse shadow: `0 4px 20px -2px rgba(100, 116, 139, 0.08)`, edged with a delicate `#F1F5F9` stroke.
3. **Level 2 (Hover / Active Cards):** Slight upward translation (`-2px` Y-axis) paired with an expanded ambient glow: `0 12px 32px -4px rgba(37, 99, 235, 0.12)`.
4. **Level 3 (Modals & Sticky Booking Drawers):** `0 20px 48px -8px rgba(15, 23, 42, 0.14)` combined with backdrop blur (`backdrop-filter: blur(12px)`) for underlying page context.

### Atmospheric Edge Treatment
Containers use a subtle dual-layer trick: a 1px border utilizing `#E2E8F0` at 60% opacity paired with the ambient shadow. This ensures soft cards remain legible against both pure white modals and off-white backdrops.

## Shapes

The form language is welcoming, curbed, and friendly. Sharp vertices are eliminated across all customer touchpoints to invoke comfort and safe handling.

### Corner Radii Guidelines
- **Primary Content Cards & Category Selectors:** `rounded-2xl` (`1.5rem` / `24px`). Creates distinct, friendly modules for TV, PC, and Printer repair offerings.
- **Interactive Controls (Inputs, Selectors, Alerts):** `rounded-xl` (`0.75rem` / `12px` to `1rem` / `16px`).
- **Interactive Badges, Category Chips, and CTAs:** Fully pill-shaped (`rounded-full` / `9999px`) to emphasize touch friendliness and quick choice confirmation.

## Components

### Buttons
- **Primary Button:** Pill-shaped (`rounded-full`), filled with `#2563EB`, text in white `label-lg`. Height: `52px` for prominent mobile tap targets. Includes a buoyant micro-interaction on press: `scale(0.98)` with a smooth `cubic-bezier(0.34, 1.56, 0.64, 1)` bounce back.
- **Secondary Button:** White surface, 1.5px border `#E2E8F0`, slate-800 text. Hover adds a slight blue tint: `#EFF6FF` border and text `#2563EB`.
- **Accent/Success Action:** Used for "Confirm Arrival Window" or "Approve Diagnostic." Filled `#10B981` with white text.

### Hardware Category Selector Cards
- Oversized `rounded-2xl` white cards presenting device graphics (PC, TV, Printer, Audio).
- Internal layout: Centered or split-row format featuring an icon container with a soft `#EFF6FF` circular background, large title (`title-md`), and starting price indicator (`body-sm`).
- Selected State: Border transitions from `#E2E8F0` to a vibrant 2px `#2563EB`, background becomes `#F8FAFC`, with an emerald checkmark pill positioned at the top right.

### Chips & Badges
- **Status Pills:** Compact rounded-full pills (`px-3 py-1`).
  - *Technician Dispatched:* Light amber background (`#FEF3C7`), text `#B45309`.
  - *Verified / Completed:* Light emerald background (`#D1FAE5`), text `#065F46`.
  - *Diagnostic In Review:* Light blue background (`#DBEAFE`), text `#1E40AF`.
- **Filter Chips:** Pill shapes with `1px` border `#E2E8F0`. When toggled, they fill with `#2563EB` with white text.

### Input Fields & Booking Pickers
- **Text & Select Inputs:** Min-height `52px`, `rounded-xl`, pristine white fill, border `#CBD5E1`. On focus: 2px ring in `#2563EB` with `0 0 0 4px rgba(37, 99, 235, 0.15)`.
- **Date & Time Slot Selector:** Horizontal grid of pill cards. Available slots display clean neutral typography; selected slot transitions to bold white text over `#2563EB`.

### Lists & Technician Cards
- Never format service checklists as austere data tables.
- **Technician Profile Card:** `rounded-2xl` container displaying verified photo thumbnail, star rating badge, arrival ETA counter, and direct phone/chat pill buttons.
- **Issue Checklist:** Grouped row items separated by spacious `16px` intervals with rounded-xl soft grey panels (`#F8FAFC`), accompanied by custom emerald checkboxes.