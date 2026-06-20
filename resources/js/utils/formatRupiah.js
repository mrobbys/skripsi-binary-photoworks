import currency from "currency.js";

const formatRupiah = (value = 0) => {
  return currency(value, {
    symbol: "Rp ",
    separator: ".",
    decimal: ",",
    precision: 0,
  }).format();
};

export default formatRupiah;