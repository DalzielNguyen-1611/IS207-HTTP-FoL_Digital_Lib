---
name: TechCare Desk
colors:
  surface: '#faf8ff'
  surface-dim: '#d2d9f4'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f3ff'
  surface-container: '#eaedff'
  surface-container-high: '#e2e7ff'
  surface-container-highest: '#dae2fd'
  on-surface: '#131b2e'
  on-surface-variant: '#424656'
  inverse-surface: '#283044'
  inverse-on-surface: '#eef0ff'
  outline: '#737687'
  outline-variant: '#c3c6d8'
  surface-tint: '#0052dd'
  primary: '#004ccd'
  on-primary: '#ffffff'
  primary-container: '#0f62fe'
  on-primary-container: '#f3f3ff'
  inverse-primary: '#b4c5ff'
  secondary: '#00677d'
  on-secondary: '#ffffff'
  secondary-container: '#50d9fe'
  on-secondary-container: '#005c70'
  tertiary: '#006444'
  on-tertiary: '#ffffff'
  tertiary-container: '#007f58'
  on-tertiary-container: '#ccffe3'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174c'
  on-primary-fixed-variant: '#003da9'
  secondary-fixed: '#b3ebff'
  secondary-fixed-dim: '#4cd6fb'
  on-secondary-fixed: '#001f27'
  on-secondary-fixed-variant: '#004e5f'
  tertiary-fixed: '#85f8c4'
  tertiary-fixed-dim: '#68dba9'
  on-tertiary-fixed: '#002114'
  on-tertiary-fixed-variant: '#005137'
  background: '#faf8ff'
  on-background: '#131b2e'
  surface-variant: '#dae2fd'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
    letterSpacing: -0.015em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.005em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 26px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 22px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
    letterSpacing: 0.03em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  space-2xs: 0.25rem
  space-xs: 0.5rem
  space-sm: 0.75rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 3rem
  space-3xl: 4rem
  gutter-mobile: 1rem
  gutter-desktop: 1.5rem
  container-max: 80rem
---

## Brand & Style
This design system targets modern digital professionals aged 22–35 navigating high-stakes, time-sensitive workplace disruptions. It embodies a Modern Tech Minimalist aesthetic: calm under pressure, frictionless, and surgical in its clarity. 

The interface communicates reliability, competence, and velocity. By utilizing an airy layout, high-contrast action drivers, and clear operational status cues, the visual language reduces cognitive fatigue and anxiety during hardware or software failures. The product rejects excessive ornamentation, focusing on crisp typographic hierarchy, precise functional borders, and actionable visual telemetry.

## Colors
The color architecture prioritizes rapid triage and legibility through high contrast and deliberate functional assignments.

- **Primary (`#0F62FE`):** Electric Blue functions as the primary vehicle for interaction, designating focus states, interactive steps, active tabs, and primary action buttons.
- **Secondary (`#00B4D8`):** Cyan Teal handles secondary data visualizations, active connection badges, and contextual accents.
- **Tertiary (`#059669`):** Mint Green is reserved for positive validation states, resolution confirmations, and healthy system status.
- **Neutral Core:** High-depth Slate (`#0F172A`) commands all primary copy. Secondary copy sits at `#475569`, and placeholder text sits at `#94A3B8`.
- **Surfaces & Borders:** Base canvases alternate between crisp white (`#FFFFFF`) for elevated cards and clean slate (`#F8FAFC`) for overall app scaffolding. Borders maintain structural definition through subtle slate (`#E2E8F0`), stepping up to `#CBD5E1` for hover boundaries.
- **Ticket Status Triage:** Dedicated telemetry colors must remain constant across all views:
  - **Pending:** `#F59E0B` (Amber) with tinted badge background `#FEF3C7`
  - **In Progress:** `#3B82F6` (Action Blue) with tinted badge background `#DBEAFE`
  - **Completed:** `#10B981` (Emerald) with tinted badge background `#D1FAE5`

## Typography
The system employs a dual-font pairing strategy to balance dynamic visual authority with dense operational legibility.

- **Headlines (Plus Jakarta Sans):** Selected for structural geometry and friendly precision. Generous counterforms and tight letter-spacing in larger sizes convey forward-thinking professionalism without institutional stiffness.
- **Body & Metadata (Inter):** Deployed across all continuous copy, data readouts, input values, and technical specifications. Its tall x-height and neutral design guarantee clarity across low-glare mobile screens and high-density ticket dashboards.

Numbers inside live ticket queues, SLA countdowns, and queue metrics should always employ tabular numeric alignment (`font-variant-numeric: tabular-nums`).

## Layout & Spacing
The layout adheres to an 8-point base grid system (with a 4px sub-grid for icons and badges), ensuring predictable visual pacing and alignment between dense dashboards and open ticket submission forms.

- **Grid Framework:** A 12-column responsive fluid grid governs desktop environments (max-width `80rem` / 1280px), collapsing to an 8-column layout on tablet (`768px - 1023px`), and a single-column 4-grid unit on mobile screens (`<768px`).
- **Gutters & Padding:** Mobile interfaces preserve a mandatory `1rem` outer margin; desktop interfaces expand to `1.5rem` gutters with nested panel padding scaling between `1.5rem` and `2rem`.
- **Reflow Architecture:** The primary support layout splits into a 2/3 main stage (ticket timeline, troubleshooting flow) and a 1/3 persistent sidebar (technician info, device telemetry, SLA timer). Below `1024px`, the sidebar condenses into a sticky bottom summary sheet.

## Elevation & Depth
Depth is constructed through flat, structural containment supported by restrained, cool-tinted ambient drop shadows rather than heavy skeuomorphic shading.

- **Level 0 (Base Canvas):** Background tone `#F8FAFC`. Zero elevation.
- **Level 1 (Cards & Modules):** Pure white `#FFFFFF` surface bounded by a crisp `1px solid #E2E8F0` border and an ambient shadow: `0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.02)`.
- **Level 2 (Dropdowns, Floating Palettes, Popovers):** Elevated `#FFFFFF` surface with a `1px solid #CBD5E1` border and a diffuse shadow: `0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.03)`.
- **Level 3 (Modals & Critical Overlays):** `#FFFFFF` surface backed by a translucent slate scrim (`#0F172A` at 40% opacity), paired with `0 20px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.04)`.
- **Active Focus Elevation:** Interactive items under focus gain no extra shadow but instead receive a crisp 2px offset focus ring in `#0F62FE`.

## Shapes
The shape language uses balanced rounded geometry (`roundedness: 2`), reflecting a modern software aesthetic that avoids harsh technical edges while preventing playful, child-like softness.

- Standard inputs, buttons, and alert callouts employ an 8px (`0.5rem`) corner radius.
- Enclosing structural cards, sliding triage panels, and dialog surfaces adopt a 16px (`1rem`) corner radius (`rounded-lg`).
- Status chips, agent presence indicators, and category tags utilize full capsule pill shapes (`9999px`) to immediately distinguish contextual metadata from clickable box components.
- Progress tracks maintain a fully rounded inner and outer stroke to communicate smooth fluid status movement.

## Components

### Buttons
- **Primary:** Background `#0F62FE`, foreground `#FFFFFF`, font `label-lg`. Hover brings background to `#0043CE`. Active state scales subtly down to 0.98. Height is 44px (touch-compliant) with 16px horizontal padding and an 8px corner radius.
- **Secondary:** Surface `#FFFFFF`, border `1px solid #E2E8F0`, foreground `#0F172A`. Hover transitions background to `#F8FAFC` and border to `#CBD5E1`.
- **Destructive/Urgent:** High-contrast tint `#EF4444` background with pure white text, reserved strictly for canceling open support requests or reporting system outages.

### Chips & Status Indicators
- **Pill Badges:** 24px height, uppercase `label-sm`, 8px padding. Composed of tinted 10% background fills with solid text:
  - *Pending:* Background `#FEF3C7`, text `#92400E`, with a pulsing 6px amber dot indicator.
  - *In Progress:* Background `#DBEAFE`, text `#1E40AF`, with an animated 6px blue radar ring.
  - *Completed:* Background `#D1FAE5`, text `#065F46`, paired with a solid green checkmark icon.

### Input Fields
- Heights default to 44px on a pure white `#FFFFFF` surface with a 1px `#E2E8F0` border and 8px corner radius.
- **States:** Placeholder color `#94A3B8`. Focus switches border color to `#0F62FE` and introduces a 3px halo `rgba(15, 98, 254, 0.15)`. Error states apply a `#EF4444` border and append a dedicated helper caption below the field.

### Checkboxes & Radio Buttons
- 20px x 20px hit target inside a 44px accessible container.
- Unchecked: White fill with a 1.5px `#CBD5E1` border.
- Checked: `#0F62FE` fill featuring a crisp white geometric check or center point. Smooth 150ms ease-out transitions on toggle.

### Cards
- Base background `#FFFFFF`, enclosed in a 1px `#E2E8F0` hairline border with a 16px radius and Level 1 elevation.
- Interactive ticket summary cards feature an inset 4px vertical status rail along the left edge reflecting ticket state (Amber, Blue, or Emerald), elevating to Level 2 on pointer hover.

### IT Support Diagnostics (Specialized Components)
- **Live SLA Countdown Bar:** Compact component with a segmented horizontal timeline, remaining time displayed in monospace-aligned `label-md`, and dynamic color shifts as response limits near expiration.
- **Agent Presence Avatar:** 36px circular technician portrait flanked by an anchored 10px connectivity badge (green for online, slate for offline) bordered by a 2px white mask.