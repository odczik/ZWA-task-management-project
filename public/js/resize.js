const divider = document.querySelector('.divider');
const sidebar = document.querySelector('.sidebar');

let isResizing = false;
let cursorOffset = 0;

divider.addEventListener('mousedown', (event) => {
    isResizing = true;

    cursorOffset = event.clientX - sidebar.getBoundingClientRect().right;

    event.preventDefault();
});

document.addEventListener('mousemove', (event) => {
    if (!isResizing) return;

    let newWidth = event.clientX - cursorOffset - sidebar.getBoundingClientRect().left;

    newWidth = Math.floor(newWidth / 10) * 10; // round to nearest 10px

    sidebar.style.width = `${newWidth}px`;
});

document.addEventListener('mouseup', () => {
    isResizing = false;
});