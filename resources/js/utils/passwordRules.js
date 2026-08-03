export const passwordRules = [
  { test: (v) => v.length >= 8, msg: "Password minimal 8 karakter" },
  { test: (v) => /[A-Z]/.test(v), msg: "Password harus mengandung huruf besar" },
  { test: (v) => /[a-z]/.test(v), msg: "Password harus mengandung huruf kecil" },
  { test: (v) => /[0-9]/.test(v), msg: "Password harus mengandung angka" },
];
