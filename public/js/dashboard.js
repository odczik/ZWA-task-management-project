const addButton = document.querySelector(".sidebar-add-button");
const modal = document.querySelector(".project-modal");
const modalForm = document.querySelector(".create-project-modal");

addButton.addEventListener("click", () => {
    modal.style.visibility = "visible";
    modalForm.style.visibility = "visible";
    modalForm.classList.add("open");
});

function closeModal() {
    modalForm.classList.remove("closing");
    modalForm.style.visibility = "hidden";
    modal.style.visibility = "hidden";
}

modal.addEventListener('click', (event) => {
    if (event.target === modal) {
        modalForm.classList.remove("open");
        modalForm.classList.add("closing");
        modalForm.addEventListener("animationend", closeModal(), { once: true });
        modalForm.removeEventListener("animationend", closeModal());
    }
});