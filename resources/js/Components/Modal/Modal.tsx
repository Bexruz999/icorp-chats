import { router } from '@inertiajs/react';

type ModalProps = {
  children: React.ReactNode;
  label: string;
}

export function toggleModal(id: string, link: string) {
  const modalElement = document.getElementById(id) as HTMLDialogElement;
  const modalIsOpen = modalElement?.open

  if (modalElement) {

    if (modalIsOpen) {
      modalElement.close()
    } else if (!modalIsOpen) {
      modalElement.querySelector('.delete-button').setAttribute('href', link);
      modalElement.showModal()
    }
  }
}

export default ({ children, label }: ModalProps) => (
  <>
    <dialog className="modal" id={label}>
      {children}
    </dialog>
  </>
)
