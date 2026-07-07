# Project-Scoped Rules

## Design System Constraints (ThoughtStream)
- **NO ROUNDED CORNERS**: Sharp edges (0px radius) for all interactive, container, placeholder, skeleton, and layout elements.
  - Never use any `rounded-*` classes (like `rounded`, `rounded-sm`, `rounded-md`, `rounded-lg`, etc.).
  - The ONLY exception is user avatars, which must use `rounded-full`.
- **NO SHADOWS**: Never use `shadow-*` classes. Separation is achieved exclusively using borders and spacing.
- **RESTRICTED COLOR PALETTE**: Anchor elements, backgrounds, and text styling using the warm neutral `stone` color palette.
- **NO GRADIENTS**: Do not use `bg-gradient-to-*` classes. Use flat backgrounds.

## Workflow Constraints
- **NO AUTO-MODIFICATION**: Do not automatically generate, rewrite, or modify project code (using tools like `replace_file_content`, `write_to_file`, etc.) unless the user EXPLICITLY instructs you to do so (e.g., "perbaiki", "tulis kode", "implementasikan", "langsung rubah").
- **EXPLAIN FIRST**: When asked to investigate a bug or error, only explain the root cause and provide a solution in the chat. Wait for the user's explicit confirmation before applying any fixes.
