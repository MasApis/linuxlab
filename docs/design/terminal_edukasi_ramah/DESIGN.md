---
name: Terminal Edukasi Ramah
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
  secondary: '#ffb95f'
  on-secondary: '#472a00'
  secondary-container: '#ee9800'
  on-secondary-container: '#5b3800'
  tertiary: '#7bd0ff'
  on-tertiary: '#00354a'
  tertiary-container: '#19aee8'
  on-tertiary-container: '#003e55'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#6ffbbe'
  primary-fixed-dim: '#4edea3'
  on-primary-fixed: '#002113'
  on-primary-fixed-variant: '#005236'
  secondary-fixed: '#ffddb8'
  secondary-fixed-dim: '#ffb95f'
  on-secondary-fixed: '#2a1700'
  on-secondary-fixed-variant: '#653e00'
  tertiary-fixed: '#c4e7ff'
  tertiary-fixed-dim: '#7bd0ff'
  on-tertiary-fixed: '#001e2c'
  on-tertiary-fixed-variant: '#004c69'
  background: '#0b1326'
  on-background: '#dae2fd'
  surface-variant: '#2d3449'
typography:
  display-hero:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '800'
    lineHeight: 56px
    letterSpacing: -0.02em
  display-hero-mobile:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '800'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Inter
    fontSize: 22px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
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
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
  code-terminal:
    fontFamily: JetBrains Mono
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 22px
  code-inline:
    fontFamily: JetBrains Mono
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
  label-ui:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
  label-mono:
    fontFamily: JetBrains Mono
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 14px
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-mobile: 1rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

This design system drives an interactive learning ecosystem designed to demystify Linux for Indonesian newcomers. The brand personality blends developer credibility with educational warmth: reassuring rather than intimidating, structured without being austere, and inherently rewarding. 

The aesthetic is a **modern, tactile developer environment** refined by gamified clarity. Deep nocturnal tones evoke authentic command-line interfaces, while lively emerald and amber highlights create energetic affordances for accomplishment, milestones, and real-time execution. The experience feels like an inviting code studio rather than an academic chore, encouraging playful experimentation within safe virtual sandboxes.

## Colors

The palette relies on a default dark canvas to minimize eye strain during extensive reading and terminal practice sessions. 

- **Primary (`#10B981` Emerald Green):** The execution anchor. Denotes primary actions (e.g., "Jalankan Kode", "Lanjut Belajar"), active terminal cursors, successful validation prompts, and completed module checkmarks.
- **Secondary (`#F59E0B` Amber Gold):** The gamification driver. Applied to XP meters, streak flames, achievement badges ("Pencapaian"), and review highlights.
- **Tertiary (`#38BDF8` Sky Blue):** Auxiliary semantic accent for code hyperlinks, parameter highlighting, and community forum tags.
- **Surface Hierarchy:**
  - Base Background: `#0F172A` (deep midnight slate).
  - Raised Container / Card: `#1E293B` (elevated surface).
  - Overlay / Active State: `#334155` (interactive hover, terminal toolbars, elevated badges).
  - Structural Dividing Lines: `#334155` default, stepping up to `#475569` on interactive focus.
- **Text & Foreground Hierarchy:**
  - Headings & Primary Data: `#F8FAFC` (Slate-100).
  - Body & Descriptive Text: `#CBD5E1` (Slate-300).
  - Muted Labels, Meta, & Keyboard Shortcuts: `#94A3B8` (Slate-400).
  - Terminal Inactive Text / Prompts: `#64748B` (Slate-500).

## Typography

Typography establishes an intentional dichotomy between approachable natural language instructions and technical execution syntax.

- **Inter** handles all narrative content, headings, module titles, explanations, and navigational anchors. With high x-height and clear grotesque letterforms, it keeps Bahasa Indonesia technical vocabulary legible and readable.
- **JetBrains Mono** powers interactive CLI terminal sandboxes, inline command definitions (e.g., `sudo apt update`, `chmod 755`), file paths, keyboard shortcut badges, and gamification statistical readouts (XP counters, timer tallies).

## Layout & Spacing

The platform uses a 12-column responsive fluid grid designed around dual-pane workflows (interactive instructions on one side, terminal emulator on the other).

- **Desktop (≥ 1024px):** 12-column grid, `margin: 2rem`, `gutter: 1.5rem`. In practice mode, screen splits into 5 columns for lesson objectives/theory and 7 columns for interactive terminal and validation suites.
- **Tablet (768px – 1023px):** 8-column layout. Split views switch dynamically between stacked tabs ("Panduan Modul" vs. "Konsol Interaktif").
- **Mobile (< 768px):** 4-column layout with `margin-mobile: 1rem` and `gutter-mobile: 1rem`. Interactive modules utilize bottom drawer consoles or toggleable full-screen terminal drawers to preserve typing space.
- Internal component rhythm strictly follows the 4px baseline derived from `space-xs` through `space-xl`.

## Elevation & Depth

Visual depth is achieved through structural color zoning and low-contrast borders rather than pronounced multi-colored shadows.

- **Base Floor (`#0F172A`):** The default application environment.
- **Level 1 (Card & Module Surface):** `#1E293B` surrounded by a 1px solid border of `#334155`. Zero drop shadow, keeping the interface crisp and modern.
- **Level 2 (Terminal Containers, Modals, Overlays):** `#0B1120` inner terminal fill paired with an outer `#1E293B` frame and a subtle diffused ambient shadow: `0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.4)`.
- **Level 3 (Focused Interactive Nodes):** Hover states bring the perimeter border from `#334155` to `#475569` or the accent color with a subtle inner glow: `0 0 0 1px #10B981, 0 0 16px -2px rgba(16, 185, 129, 0.2)`.

## Shapes

The design system consistently utilizes 12px (`rounded-xl` in standard utility mapping; Level 2 in this token schema) for modular panels, lesson cards, code execution boxes, and modal containers. 

- Interactive buttons, navigation pills, and XP badges adopt pill or medium round radii to soften technical rigor.
- Terminal windows carry 12px outer radii, while the internal terminal screen shares a complementary nested radius of 8px.
- Status dots, operational indicator beacons, and window controls remain pure 50% circular geometries.

## Components

### 1. Navigasi Atas (Top Navbar)
- **Container:** Sticky header wrapped in `#0F172A` with 80% backdrop blur, height 68px, bottom 1px border `#334155`.
- **Kiri (Left):** LinuxStart mark (stylized geometric penguin silhouette integrated into a terminal prompt icon `>_`) accompanied by the brand wordmark.
- **Tengah (Center):** Navigational link items: "Modul", "Forum Diskusi", "Pencapaian", and "Tantangan Harian". Active state features `#F8FAFC` text with an emerald bottom indicator bar; inactive states use `#94A3B8`.
- **Kanan (Right):** Gamification status pill showing streak fire icon with count (`7 Hari`) in `#F59E0B`, total XP pill (`1,450 XP`), alongside the user avatar with a green active status border.

### 2. Jendela Terminal Interaktif (Interactive Terminal Window)
- **Header Bar:** Styled in `#1E293B` with border-bottom 1px `#334155`. Left side embeds classic OS control dots: red (`#EF4444`), yellow (`#F59E0B`), and green (`#10B981`), followed by the session title in mono font: `user@linuxstart: ~/latihan-bash`. Right side hosts copy and reset action buttons.
- **Konsol:** Deep matte background (`#0B1120`), JetBrains Mono typography in `#F8FAFC`. Prompt symbol `$` rendered in `#10B981`. Cursor displays an intermittent blinking green block.

### 3. Kartu Modul & Tantangan (Module Cards)
- **Container:** Background `#1E293B`, border 1px `#334155`, padding 24px, radius 12px.
- **Header:** Category chip (`Dasar Navigasi`, `Izin File`, `Scripting`) in tertiary sky blue or emerald tone, along with difficulty metadata (`Pemula`, `Menengah`).
- **Body:** Module title in Indonesian, concise summary, and dynamic progress bar.
- **Bilah Progres (Progress Bar):** Height 6px, track `#334155`, fill `#10B981` with transition animation.
- **Footer:** XP payout indicator (`+50 XP`) in `#F59E0B` and primary action button "Mulai Belajar" or "Lanjutkan".

### 4. Tombol (Buttons)
- **Utama (Primary):** Solid `#10B981` background, `#042F2E` high-contrast bold text. On hover: `#059669`.
- **Sekunder (Secondary / Outline):** Transparent background with 1px border `#475569`, text `#F8FAFC`. On hover: `#334155` background.
- **Gamifikasi (Gamified / Accent):** Solid `#F59E0B` background with `#451A03` text for special streak claims or reward unlocks.
- **Dimensi:** Standard 42px height, 16px horizontal padding, 8px radius.

### 5. Lencana Gamifikasi (Achievement Badges)
- Hexagonal or circular framed avatars rendered in dual-tone metallic gradients with amber highlights (`#F59E0B`). Inactive/locked states are desaturated to `#334155` with lock overlays.

### 6. Bidang Input & Validasi (Input Fields)
- Background `#0F172A`, border 1px `#334155`, placeholder text `#64748B`, font JetBrains Mono for command inputs. Active focus switches border to `#10B981` with ring highlight. Error states switch border to `#EF4444` with Indonesian instructional tips ("Perintah tidak ditemukan, coba: ls -la").