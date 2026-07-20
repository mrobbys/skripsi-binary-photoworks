import { z } from "zod";
import { Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axios from "@/lib/axiosInstance";

const scrollToTop = () => {
  const container = document.querySelector("[x-data='Form']");
  if (container) {
    container.scrollIntoView({ behavior: "smooth", block: "start" });
  } else {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const schema = z.object({
  name: z.string().min(1, "Nama role wajib diisi.").max(50, "Nama role maksimal 50 karakter."),
});

export default function Form(Alpine) {
  return {
    name: "",
    permissions: [],
    errors: {},
    isLoading: false,

    setInitialData(data) {
      this.name = data.name ?? "";
      this.permissions = data.permissions ?? [];
    },

    togglePermission(permName) {
      const idx = this.permissions.indexOf(permName);
      if (idx === -1) {
        this.permissions.push(permName);
      } else {
        this.permissions.splice(idx, 1);
      }
    },

    isChecked(permName) {
      return this.permissions.includes(permName);
    },

    selectAll(permNames) {
      permNames.forEach((name) => {
        if (!this.permissions.includes(name)) this.permissions.push(name);
      });
    },

    deselectAll(permNames) {
      this.permissions = this.permissions.filter((p) => !permNames.includes(p));
    },

    async submit(mode, roleId = null) {
      this.errors = {};

      const parsed = schema.safeParse({
        name: this.name,
        permissions: this.permissions,
      });

      if (!parsed.success) {
        parsed.error.issues.forEach((issue) => {
          const key = issue.path[0];
          if (!this.errors[key]) this.errors[key] = issue.message;
        });
        scrollToTop();
        return;
      }

      this.isLoading = true;

      try {
        const url =
          mode === "edit"
            ? route("backdoor.system-settings.roles.update", roleId)
            : route("backdoor.system-settings.roles.store");
        const method = mode === "edit" ? "put" : "post";

        const res = await axios[method](url, {
          name: this.name,
          permissions: this.permissions,
        });

        Toast.fire({ icon: "success", title: res.data.message });
        window.location.href = res.data.redirect;
      } catch (error) {
        if (error.response?.status === 422) {
          const serverErrors = error.response.data.errors ?? {};
          Object.keys(serverErrors).forEach((key) => {
            this.errors[key] = serverErrors[key][0];
          });
          scrollToTop();
        } else {
          const msg = error.response?.data?.message ?? "Terjadi kesalahan server.";
          Toast.fire({ icon: "error", title: msg });
        }
      } finally {
        this.isLoading = false;
      }
    },
  };
}
