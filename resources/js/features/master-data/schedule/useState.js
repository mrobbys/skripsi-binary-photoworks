export default function useState(Alpine) {
  return Alpine.reactive({
    // Set untuk tracking baris mana yang sedang disaving
    savingIds: new Set(),
  });
}
