import { z } from "zod";
import { Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axios from "@/lib/axiosInstance";
import { getFieldError } from "@/lib/zodHelper";

const scrollToTop = () => {
  const container = document.querySelector("[x-data='Form']");
  if (container) {
    container.scrollIntoView({ behavior: "smooth", block: "start" });
  } else {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const roleSchema = z.object({
  name: z.string().min(1, "Nama role wajib diisi").max(50, "Nama role maksimal 50 karakter"),
});

export default function Form(Alpine) {
  const state = Alpine.reactive({
    form: {
      name: "",
      permissions: [],
    },
    errors: {},
    dismissedErrors: {},
    isFormValid: false,
    isLoading: false,
  });

  Alpine.effect(() => {
    const isNameFilled = Boolean(state.form.name);
    const noErrors = !state.errors.name;
    state.isFormValid = Boolean(isNameFilled && noErrors);
  });

  const setInitialData = (data) => {
    state.form.name = data.name ?? "";
    state.form.permissions = data.permissions ?? [];
    state.errors = {};
    state.dismissedErrors = {};
  };

  const togglePermission = (permName) => {
    const idx = state.form.permissions.indexOf(permName);
    if (idx === -1) {
      state.form.permissions.push(permName);
    } else {
      state.form.permissions.splice(idx, 1);
    }
  };

  const isChecked = (permName) => {
    return state.form.permissions.includes(permName);
  };

  const selectAll = (permNames) => {
    permNames.forEach((name) => {
      if (!state.form.permissions.includes(name)) {
        state.form.permissions.push(name);
      }
    });
  };

  const deselectAll = (permNames) => {
    state.form.permissions = state.form.permissions.filter((p) => !permNames.includes(p));
  };

  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = roleSchema.safeParse(state.form);
    state.errors[field] = result.success ? null : getFieldError(result, field);
  };

  const submit = async (mode, roleId = null) => {
    const result = roleSchema.safeParse(state.form);
    if (!result.success) {
      state.errors = {
        name: getFieldError(result, "name"),
      };
      scrollToTop();
      return;
    }

    state.isLoading = true;

    const url =
      mode === "edit"
        ? route("backdoor.system-settings.roles.update", roleId)
        : route("backdoor.system-settings.roles.store");
    const method = mode === "edit" ? "put" : "post";

    try {
      const res = await axios[method](url, {
        name: state.form.name.trim().toLowerCase(),
        permissions: state.form.permissions,
      });

      Toast.fire({ icon: "success", title: res.data.message });
      window.location.href = res.data.redirect;
    } catch (error) {
      if (error.response?.status === 422) {
        state.errors = error.response.data.errors ?? {};
        scrollToTop();
      } else {
        Toast.fire({ icon: "error", title: "Terjadi kesalahan server" });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return {
    state,
    setInitialData,
    togglePermission,
    isChecked,
    selectAll,
    deselectAll,
    validateField,
    submit,
  };
}
