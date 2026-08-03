import faqData from "./faqData.js";

export default function Faq(Alpine) {
  const state = Alpine.reactive({
    activeCategory: "umum",
    openIndex: null,
    get categories() {
      return faqData;
    },
    get activeFaqs() {
      return faqData.find((c) => c.id === this.activeCategory)?.items ?? [];
    },
  });

  const setCategory = (id) => {
    state.activeCategory = id;
    state.openIndex = null; // tutup semua accordion saat ganti kategori
  };

  const toggle = (index) => {
    state.openIndex = state.openIndex === index ? null : index;
  };

  return { state, setCategory, toggle };
}
