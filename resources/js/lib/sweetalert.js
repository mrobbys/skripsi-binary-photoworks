import Swal from 'sweetalert2';

const Modal = Swal.mixin({
    customClass: {
        title: 'swal2-font',
        htmlContainer: 'swal2-font',
    }
})

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    customClass: {
        title: 'swal2-font',
        htmlContainer: 'swal2-font',
    },
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    },
});

export { Modal, Toast };
