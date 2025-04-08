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

modalForm.addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(modalForm);

    fetch('/api/projects', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        window.location.href = '/dashboard?id=' + data.project_id;
    })
    .catch(e => {
        console.error(e);
    });
});



/* ================ */
/* Task drag-n-drop */
/* ================ */

const tableBody = document.querySelector('.table-body');
const tasksContainer = document.querySelector('.table-tasks');
const draggers = tasksContainer.querySelectorAll('.dragger');

let draggedTask = null;
let draggedTaskOffset = { x: 0, y: 0 };

draggers.forEach(dragger => {
    dragger.addEventListener("mousedown", e => {
        draggedTask = dragger.parentElement;
        tableBody.style.userSelect = "none"; // Disable text selection
        
        const bounds = draggedTask.getBoundingClientRect();
        draggedTask.style.position = "absolute";
        draggedTask.style.width = bounds.width + "px";
        draggedTask.style.height = bounds.height + "px";
        draggedTask.style.pointerEvents = "none"; // Disable pointer events to prevent interference with mousemove

        // tasksContainer.appendChild(draggedTask);

        draggedTaskOffset.x = e.clientX - bounds.left;
        draggedTaskOffset.y = e.clientY - bounds.top + 80;

        draggedTask.style.left = (e.clientX - draggedTaskOffset.x) + "px";
        draggedTask.style.top = (e.clientY - draggedTaskOffset.y) + "px";
    })

    document.addEventListener("mousemove", e => {
        if (draggedTask) {
            draggedTask.style.left = (e.clientX - draggedTaskOffset.x) + "px";
            draggedTask.style.top = (e.clientY - draggedTaskOffset.y) + "px";
        }
    });

    document.addEventListener("mouseup", e => {
        if (draggedTask) {
            const target = e.target.closest('.tasks');
            if(target) {
                target.appendChild(draggedTask);
            }

            tableBody.style = ""; // Reset styles
            draggedTask.style = ""; // Reset styles
            draggedTask = null;
        }
    });
});