---
name: Quiet Terminal
colors:
  surface: '#0b1326'
  surface-dim: '#0b1326'
  surface-bright: '#31394d'
  surface-container-lowest: '#060e20'
  surface-container-low: '#131b2e'
  surface-container: '#171f33'
  surface-container-high: '#222a3d'
  surface-container-highest: '#2d3449'
  on-surface: '#dae2fd'
  on-surface-variant: '#bbcabf'
  inverse-surface: '#dae2fd'
  inverse-on-surface: '#283044'
  outline: '#86948a'
  outline-variant: '#3c4a42'
  surface-tint: '#4edea3'
  primary: '#4edea3'
  on-primary: '#003824'
  primary-container: '#10b981'
  on-primary-container: '#00422b'
  inverse-primary: '#006c49'
  secondary: '#7bd0ff'
  on-secondary: '#00354a'
  secondary-container: '#00a6e0'
  on-secondary-container: '#00374d'
  tertiary: '#8bd6b4'
  on-tertiary: '#003827'
  tertiary-container: '#67b191'
  on-tertiary-container: '#00422e'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#6ffbbe'
  primary-fixed-dim: '#4edea3'
  on-primary-fixed: '#002113'
  on-primary-fixed-variant: '#005236'
  secondary-fixed: '#c4e7ff'
  secondary-fixed-dim: '#7bd0ff'
  on-secondary-fixed: '#001e2c'
  on-secondary-fixed-variant: '#004c69'
  tertiary-fixed: '#a6f2cf'
  tertiary-fixed-dim: '#8bd6b4'
  on-tertiary-fixed: '#002115'
  on-tertiary-fixed-variant: '#00513a'
  background: '#0b1326'
  on-background: '#dae2fd'
  surface-variant: '#2d3449'
typography:
  headline-xl:
    fontFamily: Geist
    fontSize: 40px
    fontWeight: '600'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-xl-mobile:
    fontFamily: Geist
    fontSize: 30px
    fontWeight: '600'
    lineHeight: 38px
    letterSpacing: -0.015em
  headline-lg:
    fontFamily: Geist
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
    letterSpacing: -0.015em
  headline-lg-mobile:
    fontFamily: Geist
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Geist
    fontSize: 20px
    fontWeight: '500'
    lineHeight: 28px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Geist
    fontSize: 17px
    fontWeight: '400'
    lineHeight: 28px
    letterSpacing: -0.005em
  body-md:
    fontFamily: Geist
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 26px
    letterSpacing: 0em
  body-sm:
    fontFamily: Geist
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: 0em
  label-lg:
    fontFamily: JetBrains Mono
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
    letterSpacing: -0.01em
  label-md:
    fontFamily: JetBrains Mono
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
    letterSpacing: 0em
  label-sm:
    fontFamily: JetBrains Mono
    fontSize: 11px
    fontWeight: '400'
    lineHeight: 16px
    letterSpacing: 0.02em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 1.25rem
  gutter-mobile: 0.75rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style
The design system establishes an immersive, low-fatigue educational workspace tailored for developers, system administrators, and students mastering Linux environments. The identity rejects high-friction gamification, aggressive contrast, and visual noise in favor of an understated, utilitarian aesthetic reminiscent of refined developer tools and archival technical documentation.

### Target Audience & Emotional Intent
- **Audience:** Developers, DevOps practitioners, engineers, and learners who read documentation and interact with terminal emulators for extended periods.
- **Emotional Resonance:** Quiet confidence, mental clarity, focus, and tactile precision. The interface recedes entirely, allowing syntax, command architecture, and pedagogical flow to take center stage.
- **Style Direction:** Modern Technical Minimalism grounded in deep, balanced slate tones, muted emerald telemetry accents, precise borders, and monospaced utilitarian balance.

## Colors
The color architecture mitigates photopic eye fatigue by eliminating stark white-on-pure-black contrasts. Every tone is subtly tempered with warm slate and blue-grey undertones.

### Palette Architecture
- **Primary (`#10b981` - Sage Emerald):** Applied to active states, successful CLI command execution, progress indicators, and primary callouts.
- **Secondary (`#38bdf8` - Soft Cyan):** Dedicated to technical meta-information, directory paths, inline code tokens, and interactive documentation references.
- **Tertiary (`#a7f3d0` - Muted Mint):** Used for micro-badges, highlights within technical copy, and subtle contextual tags.
- **Neutral Foundation (`#0f172a` - Deep Slate Canvas):**
  - Background Base: `#090d16` (Deep canvas to prevent glare)
  - Surface Container Low: `#0f172a` (Default panels, sidebar, document canvas)
  - Surface Container Moderate: `#1e293b` (Elevated cards, terminal shell chrome)
  - Surface Container High: `#334155` (Hover states, borders, terminal prompt pills)
  - Text Primary: `#e2e8f0` (Soft zinc-slate, avoiding harsh 100% white)
  - Text Secondary: `#94a3b8` (Muted technical details and explanations)
  - Text Muted: `#64748b` (Line numbers, disabled states, syntax comments)

## Typography
Typographic rhythm is calibrated for long reading sessions and unambiguous character distinction.

- **Prose Font (Geist):** Clean geometric sans-serif engineered for developer tools. Delivers maximum legibility across dense explanations and modular step-by-step guides.
- **Technical & Terminal Font (JetBrains Mono):** Provides unambiguous letterforms (0 vs O, 1 vs l vs I) with custom ligature optimization for shell operators, pipes, and environment flags.
- **Reading Comfort:** Body copy leverages a relaxed line height (`1.73x` for `body-md`) and controlled column widths (optimal line length capped at 68 characters) to minimize eye drift.

## Layout & Spacing
The layout follows a fluid split-pane model that replicates a modern IDE: structured documentation on the left and an interactive sandbox or terminal viewport on the right.

### Breakpoints & Adaptive Behavior
- **Desktop (1280px+):** Fixed navigation sidebar (260px), flexible documentation column (max-width 780px), and an interactive shell container (min-width 480px) positioned side-by-side.
- **Tablet (768px - 1279px):** Collapsible navigation drawer; documentation and interactive terminal transition into a vertical stack or tabbed views with `1.5rem` margins.
- **Mobile (< 768px):** Single-column presentation with pinned top control bar. Interactive CLI shifts to a dedicated drawer or toggleable lower panel with `1rem` margins and `0.75rem` gutters.

## Elevation & Depth
Elevation in this design system avoids simulated light sources, glossy skeuomorphism, and heavy drop shadows. Depth is communicated strictly through tonal layering and low-contrast perimeter lines.

### Elevation Hierarchy
- **Canvas Base (`#090d16`):** The non-interactive foundational layer.
- **Level 1 Panels & Cards (`#0f172a`):** Framed with a 1px border of `#1e293b`. No shadow.
- **Level 2 Interactive Containers & Shells (`#131c31`):** Framed with a 1px border of `#334155`. Accompanied by a subtle, ambient glow on active focus: `0 0 0 1px rgba(16, 185, 129, 0.3)`.
- **Level 3 Overlays & Modals (`#1e293b`):** 1px border of `#475569` and an ultra-diffused, ambient ground shadow: `0 16px 32px -8px rgba(0, 0, 0, 0.5)`.

## Shapes
Geometry is disciplined, utilitarian, and calm. Sharp corners feel overly abrasive, while excessive pill shapes evoke informal consumer platforms. 

- Default interactive controls (inputs, terminal tabs, buttons) utilize a precise `0.25rem` (4px) corner radius.
- Cards, code snippet blocks, and terminal emulator viewports employ `0.5rem` (8px).
- Status indicator tags and inline technical shortcuts (`<kbd>`) utilize `0.25rem` (4px) with subtle internal padding.

## Components

### Buttons
- **Primary:** Solid `#10b981` background, `#090d16` text, weight `500`. Hover state transitions to `#059669`. Focus ring uses a 2px offset in `#090d16` with a `#10b981` perimeter.
- **Secondary (Ghost Outline):** Transparent background, 1px `#334155` border, `#e2e8f0` text. Hover shifts background to `#1e293b` and border to `#475569`.
- **Command Action (Inline):** Monospaced label, background `#1e293b`, 1px `#334155` border, accompanied by an action icon (e.g., "Copy Command" or "Run in Shell").

### Input Fields & Terminal Emulators
- **Input Fields:** Dark surface (`#0b1120`), 1px `#334155` border, placeholder text in `#64748b`. Focus shifts the border to `#10b981` without layout shift.
- **Interactive Terminal Sandbox:** `#070b12` background, top window bar containing file paths and execution status. Terminal cursor is a subtle blinking block in `#10b981`. Syntax coloring strictly matches the primary, secondary, and tertiary functional tokens.

### Cards & Learning Modules
- **Module Card:** Background `#0f172a`, border 1px `#1e293b`. Padded with `space-lg`. Contains a progress bar along the bottom border utilizing `#10b981` at 2px height.

### Chips & Badges
- **Environment Status Chip:** Small monospaced tag (`label-sm`). Neutral background `#1e293b`, border `#334155`. Active indicators prepend a solid 6px dot in `#10b981` for running states and `#38bdf8` for tutorial milestones.

### Lists & Step Guides
- **Step List:** Sequential indicators marked by monospaced counters (`01`, `02`, `03`) in `#64748b`. Active step transitions counter to `#10b981` with a connecting vertical trace line in `#1e293b`.

### Checkboxes & Toggle Elements
- **Checkbox:** Square box with 2px corner radius, `#1e293b` fill, 1px `#475569` border. Checked state displays a solid `#10b981` background with a `#090d16` checkmark icon.