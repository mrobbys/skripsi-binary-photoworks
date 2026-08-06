import useUpdateProfile from "./useUpdateProfile.js";
import useChangePassword from "./useChangePassword.js";
import useProfileState from "./useProfileState.js";

export default function Profile(Alpine) {
  const state = useProfileState(Alpine);

  const { submitUpdateProfile, validateField: validateProfileField } = useUpdateProfile({ state });
  const { openPasswordDrawer, closePasswordDrawer, submitChangePassword, validateField: validatePasswordField } = useChangePassword({ state });

  return {
    state,
    submitUpdateProfile,
    validateProfileField,
    openPasswordDrawer,
    closePasswordDrawer,
    submitChangePassword,
    validatePasswordField,
  };
}
