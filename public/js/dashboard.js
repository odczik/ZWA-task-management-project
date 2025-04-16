/* ================ */
/* Project creation */
/* ================ */

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

/* ================ */
/* Project settings */
/* ================ */

const settingsButton = document.querySelector(".settings");
const settingsModal = document.querySelector(".settings-modal");
const settingsModalForm = document.querySelector(".settings-form");
const settingsCancelButton = document.querySelector(".settings-cancel-button");

settingsButton.addEventListener("click", () => {
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

settingsModal.addEventListener('click', (event) => {
    if (event.target === settingsModal) {
        closeSettingsModal();
    }
});

settingsCancelButton.addEventListener("click", (event) => {
    event.preventDefault();
    closeSettingsModal();
});

settingsModalForm.addEventListener('submit', function(event) {
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

        const dragger = document.createElement("div");
        dragger.className = "dragger major-dragger";
        header.appendChild(dragger);
        handleDragger(dragger);

        const titleElement = document.createElement("h3");
        titleElement.className = "task-content";
        titleElement.innerText = majorTask.title;
        header.appendChild(titleElement);

        const deleteButton = document.createElement("span");
        deleteButton.className = "delete-button icon-container";
        deleteButton.innerHTML = `<span class="icon trash"></span>`;
        deleteButton.addEventListener("click", (event) => {
            event.preventDefault();
            deleteTaskRequest(majorTask.id);
            majorTaskElement.remove();
        });
        header.appendChild(deleteButton);

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

            const taskDeleteButton = document.createElement("span");
            taskDeleteButton.className = "delete-button icon-container";
            taskDeleteButton.innerHTML = `<span class="icon trash"></span>`;
            taskDeleteButton.addEventListener("click", (event) => {
                event.preventDefault();
                deleteTaskRequest(task.id);
                taskElement.remove();
            });
            taskElement.appendChild(taskDeleteButton);
        });

        const addTaskButton = document.createElement("div");
        addTaskButton.className = "add-task";
        addTaskButton.innerHTML = `<span>+</span>`;
        majorTaskElement.appendChild(addTaskButton);
        handleAddTaskButton(addTaskButton);
    });

    tasksContainer.appendChild(addMajorTaskButton);

    handleTaskEditing();
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

let draggedTask = null;
let draggedTaskOffset = { x: 0, y: 0 };

function handleDragger(dragger) {
    dragger.addEventListener("mousedown", e => {
        draggedTask = dragger.parentElement;
        tableBody.style.userSelect = "none"; // Disable text selection
        
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
        // placeholder.style.width = bounds.width + "px";
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
                closestTasks.appendChild(placeholder);
            }
        }
    });

    document.addEventListener("mouseup", e => {
        if (draggedTask) {
            if(closestTask) {
                const closestTaskBounds = closestTask.getBoundingClientRect();
                const offsetFromCenter = (closestTaskBounds.top + closestTaskBounds.height / 2) - (e.clientY);

                if(offsetFromCenter < 0) {
                    closestTask.parentNode.insertBefore(draggedTask, placeholder.nextSibling);
                } else {
                    closestTask.parentNode.insertBefore(draggedTask, placeholder);
                }
            } else if(e.target.closest(".header")) {
                closestTasks.appendChild(draggedTask);
            }
            
            if (placeholder.parentNode) {
                placeholder.parentNode.removeChild(placeholder);
            }

            // Calculate position
            const previousSibling = draggedTask.previousElementSibling || 0;
            const nextSibling = draggedTask.nextElementSibling || 0;
            let position = draggedTask.getAttribute("data-task-position") || 0;
            if(!previousSibling) position = 0;
            if(!nextSibling && previousSibling) position = previousSibling.getAttribute("data-task-position") + 1;
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

draggers.forEach(dragger => {
    handleDragger(dragger);
});

// Horizontal scroll on wheel event
tasksContainer.addEventListener('wheel', (e) => {
    e.preventDefault();
    tasksContainer.scrollLeft += e.deltaY / 1.5;
});

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
        console.log(data);
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
        
        taskContainer.appendChild(newTask);
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
            newMajorTask.appendChild(addTaskButton);
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

function handleTaskEditing() {
    const taskContents = document.querySelectorAll(".task-content");

    taskContents.forEach(taskContent => {
        taskContent.addEventListener("dblclick", (event) => {
            event.preventDefault();
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
    });
}