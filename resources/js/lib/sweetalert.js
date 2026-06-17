import Swal from "sweetalert2";

// untuk modal
const Modal = Swal.mixin({
  customClass: {
    title: "swal2-font",
    htmlContainer: "swal2-font",
  },
});

// untuk toast
const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  customClass: {
    title: "swal2-font",
    htmlContainer: "swal2-font",
  },
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  },
});

// confirm 
const confirmModal = (title, text, icon, confirmBtnText) => {
  return Modal.fire({
    title: `${title}`,
    text: `${text}`,
    icon: `${icon}`,
    showCancelButton: true,
    confirmButtonText: `${confirmBtnText}`,
    cancelButtonText: "Tidak",
    confirmButtonColor: "#b91c1c",
    cancelButtonColor: "#78716c",
  });
};

export { Modal, Toast, confirmModal };
