/* ====== */
/* Modals */
/* ====== */

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

function closeAddModalEnd() {
    modalForm.classList.remove("closing");
    modalForm.style.visibility = "hidden";
    modal.style.visibility = "hidden";
}

function closeAddModal() {
    modalForm.classList.remove("open");
    modalForm.classList.add("closing");
    modalForm.addEventListener("animationend", closeAddModalEnd(), { once: true });
    modalForm.removeEventListener("animationend", closeAddModalEnd());
}

modal.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeAddModal();
    }
});

cancelButton.addEventListener("click", (event) => {
    event.preventDefault();
    closeAddModal();
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

const projectId = location.search.split("=")[1];

if(!projectId) throw new Error("Project ID not found in URL.");

let canEdit = document.querySelector(".table-header").getAttribute("data-can-edit");
if(canEdit === "true") {
    canEdit = 1;
} else {
    canEdit = 0;
}

const projectMembersButton = document.querySelector("#manage-members-button");
const projectMembersModal = document.querySelector(".members-modal");
const projectMembersModalForm = projectMembersModal?.querySelector("form");
const projectMembersAddButton = projectMembersModal?.querySelector("#add-member-button");

projectMembersButton?.addEventListener("click", () => {
    projectMembersModal.style.visibility = "visible";
    projectMembersModalForm.style.visibility = "visible";
    projectMembersModalForm.classList.add("open");
});

function closeAddMembersModalEnd() {
    projectMembersModalForm.classList.remove("closing");
    projectMembersModalForm.style.visibility = "hidden";
    projectMembersModal.style.visibility = "hidden";
}

function closeAddMembersModal() {
    projectMembersModalForm.classList.remove("open");
    projectMembersModalForm.classList.add("closing");
    projectMembersModalForm.addEventListener("animationend", closeAddMembersModalEnd(), { once: true });
    projectMembersModalForm.removeEventListener("animationend", closeAddMembersModalEnd());
}

projectMembersModal?.addEventListener('click', (event) => {
    if (event.target === projectMembersModal) {
        closeAddMembersModal();
    }
});

projectMembersModalForm?.addEventListener('submit', function(event) {
    event.preventDefault();
    // const formData = new FormData(projectMembersModalForm);

    // fetch('/api/members', {
    //     method: 'POST',
    //     body: JSON.stringify(Object.fromEntries(formData))
    // })
    // .then(response => {
    //     if (!response.ok) {
    //         throw new Error(`HTTP error! status: ${response.status}`);
    //     }
    //     return response.json();
    // })
    // .then(data => {
    //     window.location.href = '/dashboard?id=' + data.project_id;
    // })
    // .catch(e => {
    //     console.error(e);
    // });
});

if(projectMembersAddButton) projectMembersAddButton.addEventListener("click", (e) => {
    e.preventDefault();

    const emailInput = document.createElement("input");
    emailInput.type = "email";
    emailInput.placeholder = "Email";
    emailInput.className = "member-email-input";
    emailInput.autocomplete = "off";
    emailInput.name = "email";
    emailInput.required = true;
    projectMembersModalForm.append(emailInput);
    emailInput.focus();

    projectMembersAddButton.style.display = "none";

    emailInput.addEventListener("blur", (event) => {
        event.preventDefault();
        const email = emailInput.value.trim();
        if(email === "") {
            emailInput.remove();
            projectMembersAddButton.style.display = "block";
            return;
        } else {
            emailInput.setAttribute("disabled", "true");
            fetch('/api/invitation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    project_id: projectId
                })
            }).then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            }).then(data => {
                location.reload();
            }).catch(e => {
                alert(e);
                location.reload();
            });
        }
    });
    emailInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            emailInput.blur();
        }
        if (event.key === "Escape") {
            emailInput.value = "";
            emailInput.blur();
        }
    });
})

/* ================ */
/* Project settings */
/* ================ */

const settingsButton = document.querySelector(".settings-button");
const settingsModal = document.querySelector(".settings-modal");
const settingsModalForm = document.querySelector(".settings-form");
const settingsCancelButton = document.querySelector(".settings-cancel-button");

if(settingsButton) settingsButton.addEventListener("click", () => {
    settingsModal.style.visibility = "visible";
    settingsModalForm.style.visibility = "visible";
    settingsModalForm.classList.add("open");
});

function closeSetingsModalEnd() {
    settingsModalForm.classList.remove("closing");
    settingsModalForm.style.visibility = "hidden";
    settingsModal.style.visibility = "hidden";
}

function closeSettingsModal() {
    settingsModalForm.classList.remove("open");
    settingsModalForm.classList.add("closing");
    settingsModalForm.addEventListener("animationend", closeSetingsModalEnd(), { once: true });
    settingsModalForm.removeEventListener("animationend", closeSetingsModalEnd());
}

if(settingsModal) settingsModal.addEventListener('click', (event) => {
    if (event.target === settingsModal) {
        closeSettingsModal();
    }
});

if(settingsCancelButton) settingsCancelButton.addEventListener("click", (event) => {
    event.preventDefault();
    closeSettingsModal();
});

if(settingsModalForm) settingsModalForm.addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(settingsModalForm);

    fetch('/api/projects', {
        method: 'PATCH',
        body: JSON.stringify(Object.fromEntries(formData))
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

/* ============= */
/* Loading tasks */
/* ============= */

function deleteTaskRequest(taskId) {
    fetch(`/api/tasks`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            task_id: taskId
        })
    }).then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    }).then(data => {
        console.log(data);
    }).catch(e => {
        console.error(e);
    });
}

const tasksContainer = document.querySelector('.table-tasks');
    
fetch(`/api/tasks?project_id=${projectId}`).then(response => {
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
}).then(data => {
    let processedTasks = [];
    data.forEach(majorTask => {
        if(!majorTask.is_major) return;
        majorTask.tasks = [];
        processedTasks.push(majorTask);
    });
    data.forEach(task => {
        if(task.is_major) return;
        processedTasks.find(majorTask => majorTask.id === task.assigned_under).tasks.push(task);
    });
    processedTasks.forEach(majorTask => {
        majorTask.tasks.sort((a, b) => a.position - b.position);
    });
    processedTasks.sort((a, b) => a.position - b.position);
    console.log(processedTasks);

    processedTasks.forEach(majorTask => {
        const majorTaskElement = document.createElement("div");
        majorTaskElement.className = "major-task";
        majorTaskElement.setAttribute("data-major-task-id", majorTask.id);
        majorTaskElement.setAttribute("data-major-task-position", majorTask.position || 0);
        tasksContainer.appendChild(majorTaskElement);

        const header = document.createElement("div");
        header.className = "header";
        majorTaskElement.appendChild(header);

        if(canEdit) {
            const dragger = document.createElement("div");
            dragger.className = "dragger major-dragger";
            header.appendChild(dragger);
            handleMajorDragger(dragger);
        }

        const titleElement = document.createElement("h3");
        titleElement.className = "task-content";
        titleElement.innerText = majorTask.title;
        header.appendChild(titleElement);

        if(canEdit) {
            const deleteButton = document.createElement("span");
            deleteButton.className = "delete-button icon-container";
            deleteButton.innerHTML = `<span class="icon trash"></span>`;
            deleteButton.addEventListener("click", (event) => {
                event.preventDefault();
                if(!confirm("Are you sure? This action cannot be undone.")) return;
                deleteTaskRequest(majorTask.id);
                majorTaskElement.remove();
            });
            header.appendChild(deleteButton);
        }

        const tasksElement = document.createElement("div");
        tasksElement.className = "tasks";
        majorTaskElement.appendChild(tasksElement);

        majorTask.tasks.forEach(task => {
            const taskElement = document.createElement("div");
            taskElement.className = "task";
            taskElement.innerHTML = `
                <div class="dragger"></div>
                <div class="task-content">
                    <span>${task.title}</span>
                </div>
            `;
            taskElement.setAttribute("data-task-id", task.id);
            taskElement.setAttribute("data-task-position", task.position);
            tasksElement.appendChild(taskElement);
            const dragger = taskElement.querySelector(".dragger");
            handleDragger(dragger);
            if(!canEdit) {
                taskElement.removeChild(dragger);
            }

            if(canEdit) {
                const taskDeleteButton = document.createElement("span");
                taskDeleteButton.className = "delete-button icon-container";
                taskDeleteButton.innerHTML = `<span class="icon trash"></span>`;
                taskDeleteButton.addEventListener("click", (event) => {
                    event.preventDefault();
                    deleteTaskRequest(task.id);
                    taskElement.remove();
                });
                taskElement.appendChild(taskDeleteButton);
            }
        });

        if(canEdit) {
            const addTaskButton = document.createElement("div");
            addTaskButton.className = "add-task";
            addTaskButton.innerHTML = `<span>+</span>`;
            tasksElement.appendChild(addTaskButton);
            handleAddTaskButton(addTaskButton);
        }
    });

    tasksContainer.appendChild(addMajorTaskButton);
    if(!canEdit) {
        tasksContainer.removeChild(addMajorTaskButton)
    } else {
        handleTaskEditing();
    }
}).catch(e => {
    console.error(e);
});



/* ================ */
/* Task drag-n-drop */
/* ================ */

const tableBody = document.querySelector('.table-body');
const draggers = tasksContainer.querySelectorAll('.dragger');
const placeholder = document.createElement('div');
placeholder.className = 'placeholder';

let draggedMajorTask = null;

let draggedTaskOffset = { x: 0, y: 0 };

function handleMajorDragger(dragger) {
    dragger.addEventListener("mousedown", e => {
        draggedMajorTask = dragger.parentElement.parentElement;
        tableBody.style.userSelect = "none"; // Disable text selection
        tableBody.style.webkitUserSelect = "none"; // Disable text selection
        
        const bounds = draggedMajorTask.getBoundingClientRect();
        draggedMajorTask.style.position = "absolute";
        draggedMajorTask.style.width = bounds.width + "px";
        draggedMajorTask.style.height = bounds.height + "px";
        draggedMajorTask.style.pointerEvents = "none"; // Disable pointer events to prevent interference with mousemove

        draggedTaskOffset.x = e.clientX - bounds.left;
        draggedTaskOffset.y = e.clientY - bounds.top + 80;

        draggedMajorTask.style.left = (e.clientX - draggedTaskOffset.x) + "px";
        draggedMajorTask.style.top = (e.clientY - draggedTaskOffset.y) + "px";

        // Create a placeholder element
        placeholder.style.width = bounds.width + "px";
        placeholder.style.height = bounds.height + "px";
    })

    let closestTask;
    document.addEventListener("mousemove", e => {
        if (draggedMajorTask) {
            draggedMajorTask.style.left = (e.clientX - draggedTaskOffset.x) + "px";
            draggedMajorTask.style.top = (e.clientY - draggedTaskOffset.y) + "px";

            // Placeholder
            try {
                const elementFromPoint = document.elementFromPoint(e.clientX, e.clientY);
                if(!elementFromPoint || elementFromPoint.classList.contains("placeholder")) return;

                closestTask = elementFromPoint.closest('.major-task');

                if(closestTask) {
                    lastClosestTask = closestTask;
                }
            } catch {
                closestTask = null;
            }

            if (closestTask) {
                const closestTaskBounds = closestTask.getBoundingClientRect();
                const offsetFromCenter = (closestTaskBounds.left + closestTaskBounds.width / 2) - (e.clientX);

                if(offsetFromCenter < 0) {
                    closestTask.parentNode.insertBefore(placeholder, closestTask.nextSibling);
                } else {
                    closestTask.parentNode.insertBefore(placeholder, closestTask);
                }
            }
        }
    });

    document.addEventListener("mouseup", e => {
        if (draggedMajorTask) {            
            // Replace the placeholder with the dragged task
            if (placeholder.parentNode) {
                placeholder.parentNode.insertBefore(draggedMajorTask, placeholder);
                placeholder.parentNode.removeChild(placeholder);
            }

            // Calculate position
            const previousSibling = draggedMajorTask.previousElementSibling || 0;
            let nextSibling = draggedMajorTask.nextElementSibling || 0;
            if(nextSibling.classList.contains("add-major-task")) nextSibling = 0;
            let position = draggedMajorTask.getAttribute("data-major-task-position") || 0;
            if(!previousSibling && nextSibling) position = Number(nextSibling.getAttribute("data-major-task-position")) - 1;
            if(!nextSibling && previousSibling) position = Number(previousSibling.getAttribute("data-major-task-position")) + 1;
            if(!nextSibling && !previousSibling) position = 0;
            if(previousSibling && nextSibling) position = (parseFloat(previousSibling.getAttribute("data-major-task-position")) + parseFloat(nextSibling.getAttribute("data-major-task-position"))) / 2;
            draggedMajorTask.setAttribute("data-major-task-position", position);
            fetch(`/api/tasks`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    task_id: draggedMajorTask.getAttribute("data-major-task-id"),
                    position: position
                })
            }).then(console.log).catch(e => {
                console.error(e);
            });

            tableBody.style = ""; // Reset styles
            draggedMajorTask.style = ""; // Reset styles
            draggedMajorTask = null;
        }
    });
}

let draggedTask = null;

function handleDragger(dragger) {
    dragger.addEventListener("mousedown", e => {
        draggedTask = dragger.parentElement;
        tableBody.style.userSelect = "none"; // Disable text selection
        tableBody.style.webkitUserSelect = "none"; // Disable text selection
        
        const bounds = draggedTask.getBoundingClientRect();
        draggedTask.style.position = "absolute";
        draggedTask.style.width = bounds.width + "px";
        draggedTask.style.height = bounds.height + "px";
        draggedTask.style.pointerEvents = "none"; // Disable pointer events to prevent interference with mousemove

        draggedTaskOffset.x = e.clientX - bounds.left;
        draggedTaskOffset.y = e.clientY - bounds.top + 80;

        draggedTask.style.left = (e.clientX - draggedTaskOffset.x) + "px";
        draggedTask.style.top = (e.clientY - draggedTaskOffset.y) + "px";

        // Create a placeholder element
        placeholder.style.minWidth = bounds.width + "px";
        placeholder.style.height = bounds.height + "px";
    })

    let closestTask;
    let closestTasks;
    document.addEventListener("mousemove", e => {
        if (draggedTask) {
            draggedTask.style.left = (e.clientX - draggedTaskOffset.x) + "px";
            draggedTask.style.top = (e.clientY - draggedTaskOffset.y) + "px";

            // Placeholder
            try {
                const elementFromPoint = document.elementFromPoint(e.clientX, e.clientY);
                if(!elementFromPoint || elementFromPoint.classList.contains("placeholder")) return;

                closestTask = elementFromPoint.closest('.task');

                if(closestTask) {
                    lastClosestTask = closestTask;
                }
            } catch {
                closestTask = null;
            }

            try {
                closestTasks = e.target.closest(".major-task").querySelector(".tasks");
            } catch {
                closestTasks = null;
            }

            if (closestTask) {
                const closestTaskBounds = closestTask.getBoundingClientRect();
                const offsetFromCenter = (closestTaskBounds.top + closestTaskBounds.height / 2) - (e.clientY);

                if(offsetFromCenter < 0) {
                    closestTask.parentNode.insertBefore(placeholder, closestTask.nextSibling);
                } else {
                    closestTask.parentNode.insertBefore(placeholder, closestTask);
                }
            } else if(e.target.closest(".header")) {
                closestTasks.insertBefore(placeholder, closestTasks.querySelector(".add-task"));
            }
        }
    });

    document.addEventListener("mouseup", e => {
        if (draggedTask) {            
            // Replace the placeholder with the dragged task
            if (placeholder.parentNode) {
                placeholder.parentNode.insertBefore(draggedTask, placeholder);
                placeholder.parentNode.removeChild(placeholder);
            }

            // Calculate position
            const previousSibling = draggedTask.previousElementSibling || 0;
            let nextSibling = draggedTask.nextElementSibling || 0;
            if(nextSibling.classList.contains("add-task")) nextSibling = 0;
            let position = draggedTask.getAttribute("data-task-position") || 0;
            if(!previousSibling && nextSibling) position = Number(nextSibling.getAttribute("data-task-position")) - 1;
            if(!nextSibling && previousSibling) position = Number(previousSibling.getAttribute("data-task-position")) + 1;
            if(!nextSibling && !previousSibling) position = 0;
            if(previousSibling && nextSibling) position = (parseFloat(previousSibling.getAttribute("data-task-position")) + parseFloat(nextSibling.getAttribute("data-task-position"))) / 2;
            draggedTask.setAttribute("data-task-position", position);
            fetch(`/api/tasks`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    task_id: draggedTask.getAttribute("data-task-id"),
                    position: position,
                    assigned_under: draggedTask.parentElement.parentElement.getAttribute("data-major-task-id")
                })
            }).then(console.log).catch(e => {
                console.error(e);
            });

            tableBody.style = ""; // Reset styles
            draggedTask.style = ""; // Reset styles
            draggedTask = null;
        }
    });
}

// Horizontal scroll on wheel event
// tasksContainer.addEventListener('wheel', (e) => {
//     e.preventDefault();
//     tasksContainer.scrollLeft += e.deltaY / 1.5;
// });

/* =========== */
/* Task adding */
/* =========== */

let taskShouldBeAdded = false;

function createTaskRequest(newTask, taskName, projectId, assigned_under) {
    if(!taskShouldBeAdded) return;
    
    fetch('/api/tasks', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            title: taskName,
            project_id: projectId,
            assigned_under: assigned_under
        })
    }).then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    }).then(data => {
        newTask.setAttribute("data-task-id", data.task_id);
        newTask.setAttribute("data-task-position", data.position);

        const deleteButton = document.createElement("span");
        deleteButton.className = "delete-button icon-container";
        deleteButton.innerHTML = `<span class="icon trash"></span>`;
        deleteButton.addEventListener("click", (event) => {
            event.preventDefault();
            deleteTaskRequest(data.task_id);
            newTask.remove();
        });
        newTask.appendChild(deleteButton);
    }).catch(e => {
        console.error(e);
    });
}
function handleAddTaskButton(button) {
    button.addEventListener("click", (event) => {
        event.preventDefault();
        const taskContainer = button.closest(".major-task").querySelector(".tasks");
        const newTask = document.createElement("div");
        newTask.className = "task";

        newTaskInput = document.createElement("input");
        newTaskInput.className = "task-input";
        newTaskInput.type = "text";
        newTaskInput.placeholder = "New Task";
        newTaskInput.autocomplete = "off";
        newTaskInput.name = "Task title";
        
        taskContainer.insertBefore(newTask, button);
        newTask.appendChild(newTaskInput);

        taskShouldBeAdded = true;

        newTaskInput.focus();
        newTaskInput.addEventListener("blur", (event) => {
            event.preventDefault();
            const taskName = newTaskInput.value.trim();
            newTaskInput.remove();

            if(taskName === "") {
                newTask.remove();
                return;
            } else {
                newTask.innerHTML = `
                    <div class="dragger"></div>
                    <div class="task-content">
                        <span>${taskName}</span>
                    </div>
                `;
                const dragger = newTask.querySelector(".dragger");
                handleDragger(dragger);
                const taskContent = newTask.querySelector(".task-content");
                handleTaskEdit(taskContent);

                createTaskRequest(newTask, taskName, location.search.split("=")[1], taskContainer.parentElement.getAttribute("data-major-task-id"));
            }
        });
        newTaskInput.addEventListener("keydown", (event) => {
            if (event.key === "Enter") {
                newTaskInput.blur();
            }
            if (event.key === "Escape") {
                taskShouldBeAdded = false;
                newTaskInput.value = "";
                newTaskInput.blur();
            }
        });
    });
}

const addTaskButtons = document.querySelectorAll(".add-task");
addTaskButtons.forEach(button => {
    handleAddTaskButton(button);
});

function createMajorTaskRequest(newMajorTask, majorTaskName, projectId) {
    if(!taskShouldBeAdded) return;
    
    fetch('/api/tasks', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            title: majorTaskName,
            project_id: projectId,
            is_major: true
        })
    }).then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    }).then(data => {
        newMajorTask.setAttribute("data-major-task-id", data.task_id);
        newMajorTask.setAttribute("data-major-task-position", data.position);

        const deleteButton = document.createElement("span");
        deleteButton.className = "delete-button icon-container";
        deleteButton.innerHTML = `<span class="icon trash"></span>`;
        deleteButton.addEventListener("click", (event) => {
            event.preventDefault();
            deleteTaskRequest(data.task_id);
            newMajorTask.remove();
        });
        newMajorTask.querySelector(".header").appendChild(deleteButton);
    }).catch(e => {
        console.error(e);
    });
}
const addMajorTaskButton = document.querySelector(".add-major-task");
const majorTasksContainer = document.querySelector(".table-tasks");

addMajorTaskButton.addEventListener("click", (event) => {
    event.preventDefault();
    const newMajorTask = document.createElement("div");
    newMajorTask.className = "major-task";
    
    majorTasksContainer.appendChild(newMajorTask);
    majorTasksContainer.appendChild(addMajorTaskButton);

    const newMajorTaskInput = document.createElement("input");
    newMajorTaskInput.className = "major-task-input";
    newMajorTaskInput.type = "text";
    newMajorTaskInput.placeholder = "Title";
    newMajorTaskInput.autocomplete = "off";
    newMajorTaskInput.name = "Major task title";
    newMajorTask.appendChild(newMajorTaskInput);
    
    taskShouldBeAdded = true;

    newMajorTaskInput.focus();
    newMajorTaskInput.addEventListener("blur", (event) => {
        event.preventDefault();
        const majorTaskName = newMajorTaskInput.value.trim();
        newMajorTaskInput.remove();

        if(majorTaskName === "") {
            newMajorTask.remove();
        } else {
            newMajorTask.innerHTML = `
                <div class="header">
                    <h3>${majorTaskName}</h3>
                </div>
                <div class="tasks"></div>
            `;

            const addTaskButton = document.createElement("div");
            addTaskButton.className = "add-task";
            addTaskButton.innerHTML = `<span>+</span>`;
            newMajorTask.querySelector(".tasks").appendChild(addTaskButton);
            handleAddTaskButton(addTaskButton);
            addTaskButton.click();
    
            createMajorTaskRequest(newMajorTask, majorTaskName, location.search.split("=")[1]);
        }
    });
    newMajorTaskInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            newMajorTaskInput.blur();
        }
        if (event.key === "Escape") {
            taskShouldBeAdded = false;
            newMajorTaskInput.value = "";
            newMajorTaskInput.blur();
        }
    });
});

/* ============ */
/* Task editing */
/* ============ */

function handleTaskEdit(taskContent) {
    taskContent.addEventListener("dblclick", (event) => {
        event.preventDefault();
        
        if(taskContent.firstElementChild?.classList.contains("task-edit-input")) return; // Return if already editing

        let taskElement = taskContent.parentElement;
        if(taskElement.classList.contains("header")) taskElement = taskElement.parentElement;

        const taskId = taskElement.getAttribute("data-task-id") || taskElement.getAttribute("data-major-task-id");

        let taskName = taskContent.querySelector("span");
        if(!taskName){
            taskName = taskContent.innerText.trim();
        } else {
            taskName = taskContent.querySelector("span").innerText.trim();
        }

        const startValue = taskName;

        const input = document.createElement("input");
        input.type = "text";
        input.value = taskName;
        input.className = "task-edit-input";
        input.autocomplete = "off";
        input.name = "Task title";

        taskContent.innerHTML = "";
        taskContent.appendChild(input);
        
        input.focus();
        input.addEventListener("blur", (event) => {
            event.preventDefault();
            const newTaskName = input.value.trim();
            if(newTaskName === startValue) {
                taskContent.innerHTML = `<span>${taskName}</span>`;
                return;
            }
            if(newTaskName === "") {
                taskElement.remove();
                return;
            } else {
                taskContent.innerHTML = `<span>${newTaskName}</span>`;
            }
            fetch('/api/tasks', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    task_id: taskId,
                    title: newTaskName
                })
            }).then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            }).then(data => {
                console.log(data);
            }).catch(e => {
                console.error(e);
            });
        });
        input.addEventListener("keydown", (event) => {
            if (event.key === "Enter") {
                input.blur();
            }
            if (event.key === "Escape") {
                taskContent.innerHTML = `<span>${taskName}</span>`;
            }
        });
    });
}

function handleTaskEditing() {
    const taskContents = document.querySelectorAll(".task-content");

    taskContents.forEach(taskContent => {
        handleTaskEdit(taskContent);
    });
}

/* ============== */
/* Project delete */
/* ============== */

const deleteProjectButton = document.querySelector(".settings-delete-button");

deleteProjectButton?.addEventListener("click", (event) => {
    event.preventDefault();
    const confirmation = confirm("Are you sure you want to delete this project? This action cannot be undone.");
    if(confirmation) {
        fetch(`/api/projects`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: projectId
            })
        }).then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        }).then(data => {
            window.location.href = '/dashboard';
        }).catch(e => {
            console.error(e);
        });
    }
});

/* ================== */
/* Member role editor */
/* ================== */

const memberRoleButtons = document.querySelectorAll(".member-role-button");

memberRoleButtons.forEach(button => {
    button.addEventListener("click", (e) => {
        e.preventDefault();
        const memberId = button.getAttribute("data-member-id");
        let role = button.getAttribute("data-member-role");

        if(role == "viewer"){
            role = "editor";
        } else {
            role = "viewer";
        }

        fetch('/api/members', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                project_id: projectId,
                member_id: memberId,
                role: role
            })
        }).then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        }).then(data => {
            window.location.reload();
        }).catch(e => {
            console.error(e);
        });
    });
});

/* ============= */
/* Project leave */
/* ============= */

const leaveProjectButton = document.querySelector(".leave-button");

leaveProjectButton?.addEventListener("click", (event) => {
    event.preventDefault();
    const confirmation = confirm("Are you sure you want to leave this project? You will no longer have access to it.");
    if(confirmation) {
        fetch(`/api/members`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                project_id: projectId
            })
        }).then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        }).then(data => {
            window.location.href = '/dashboard';
        }).catch(e => {
            console.error(e);
        });
    }
});