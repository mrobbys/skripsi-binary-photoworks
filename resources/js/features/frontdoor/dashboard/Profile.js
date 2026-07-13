import useUpdateProfile from "./useUpdateProfile.js";
import useChangePassword from "./useChangePassword.js";
import useProfileState from "./useProfileState.js";

export default function Profile(Alpine) {
  const state = useProfileState(Alpine);

  const { submitUpdateProfile } = useUpdateProfile({ state });
  const { openPasswordDrawer, closePasswordDrawer, submitChangePassword } = useChangePassword({ state });

  return {
    state,
    submitUpdateProfile,
    openPasswordDrawer,
    closePasswordDrawer,
    submitChangePassword,
  };
}
