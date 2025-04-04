const addButton = document.querySelector(".sidebar-add-button");
const createButton = document.querySelector("button[type='submit']");
const cancelButton = document.querySelector(".cancel-button");
const modal = document.querySelector(".project-modal");
const modalForm = document.querySelector(".create-project-modal");

addButton.addEventListener("click", () => {
    modal.style.visibility = "visible";
    modalForm.style.visibility = "visible";
    modalForm.classList.add("open");
});

function closeModalEnd() {
    modalForm.classList.remove("closing");
    modalForm.style.visibility = "hidden";
    modal.style.visibility = "hidden";
}

function closeModal() {
    modalForm.classList.remove("open");
    modalForm.classList.add("closing");
    modalForm.addEventListener("animationend", closeModalEnd(), { once: true });
    modalForm.removeEventListener("animationend", closeModalEnd());
}

modal.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeModal();
    }
});

cancelButton.addEventListener("click", (event) => {
    event.preventDefault();
    closeModal();
});