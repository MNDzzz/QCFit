# Design Alignment Plan (QCFit 2.0)

This document outlines the tasks required to match the project's current state with the high-fidelity designs provided (Images 0-4).

## 1. Global & Theme
- [ ] **Dark Mode Foundation**: Ensure `slate-950` is the base for the Studio.
- [ ] **Primary Gradient**: Standardize the "Violet-to-Fuchsia" gradient (`from-violet-600 to-fuchsia-600`) across all buttons and accents.
- [ ] **Typography**: Verify `Space Grotesk` (headings) and `Inter` (body) usage.

## 2. Home Page (Image 0)
- [ ] **Hero Section**:
    - [ ] Implement the "Floating Cards" 3D effect (Nike Dunk & J1 cards).
    - [ ] Update background to the deep dark blue/slate gradient shown.
    - [ ] Style the Search Bar to be fully rounded with an inner purple button.
- [ ] **Live Ticker**:
    - [ ] Style the "JUST CHECKED" ticker to match the dark pill aesthetic.
- [ ] **Product Grid**:
    - [ ] Add the "Studio" (+ button) overlay on product hover.
    - [ ] Ensure "Remix This Fit" button appears on outfit cards.

## 3. Search Results (Image 1)
- [ ] **Sidebar Styling**:
    - [ ] **Price Slider**: Customize PrimeVue Slider to use the purple theme.
    - [ ] **Toggles**: Style PrimeVue `InputSwitch` to match the design (Purple/White).
    - [ ] **Marketplace Checkboxes**: Ensure custom checkbox styling (Purple check).
- [ ] **Header**:
    - [ ] Add the "Products / Outfits" toggle pill.
    - [ ] Match the Breadcrumb style.

## 4. User Profile (Image 2)
- [ ] **Profile Header**:
    - [ ] Implement the large specific Avatar Gradient Circle.
    - [ ] Layout the stats (`Fits Created`, `Wardrobe`, `Views`) exactly as shown.
    - [ ] Add the "Verified" badge.
- [ ] **Auth Modal**:
    - [ ] Create a custom `AuthModal.vue` with Glassmorphism (blur background).
    - [ ] Style standard and Google login buttons.

## 5. Product Detail (Image 3)
- [ ] **Gallery**:
    - [ ] **Ruler Overlay**: Add a toggleable ruler overlay image for "QC Photos" mode.
    - [ ] **Toggle Pill**: Style the "[Active] QC PHOTOS" vs "[Inactive] ORIGINAL" pill.
- [ ] **Info Section**:
    - [ ] **Buy Button**: Style to match the large purple gradient button with the external icon.
    - [ ] **Agent Widget**: Match the specific "Yellow/Amber" styling for the Agent Preference Widget.

## 6. The Studio (Image 4) - MAJOR OVERHAUL
- [ ] **Canvas Area**:
    - [ ] Change background from white to **Transparent Checkerboard**.
    - [ ] Remove the "Paper" shadow look; make it an infinite or large transparent workspace.
- [ ] **Floating Toolbar**:
    - [ ] **Move**: Convert the top static toolbar to a **Floating Context Menu** that appears near the selected item (or fixed bottom/top floating).
    - [ ] **Buttons**: "Remove BG", "Bring Front", "Send Back", "Flip", "Trash".
- [ ] **Right Sidebar (Layers)**:
    - [ ] **New Component**: Create `CanvasLayersPanel.vue`.
    - [ ] **Functionality**: List items with visibility toggle and drag-to-reorder.
- [ ] **Left Sidebar**:
    - [ ] Update styling to match the dark grid aesthetic.
- [ ] **Header**:
    - [ ] Add "Undo/Redo" buttons (requires implementing history support in store).
    - [ ] Add "Publish" button (Gradient).

## 7. Action Plan
1.  **Studio Overhaul**: This is the most complex task. Start here.
2.  **Home & Search**: Visual CSS updates.
3.  **Profile & Product**: Specific component updates.
