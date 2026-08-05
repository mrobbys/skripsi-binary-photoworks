import faqData from "../faq/faqData.js";

export default function Home(Alpine) {
  // Ambil 5 data
  const faqs = [
    faqData[0].items[0], // Apa itu Binary Photoworks?
    faqData[1].items[0], // Bagaimana alur pemesanan di Binary Photoworks?
    faqData[1].items[1], // Berapa lama durasi untuk satu sesi foto?
    faqData[2].items[0], // Metode pembayaran apa saja yang tersedia?
    faqData[3].items[0], // Kapan hasil foto saya dikirim?
  ];

  const state = Alpine.reactive({
    openIndex: null, // index yang sedang terbuka
    faqs,
  });

  const toggle = (index) => {
    state.openIndex = state.openIndex === index ? null : index;
  };

  return {
    state,
    toggle,
  };
}
