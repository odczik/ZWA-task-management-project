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
    
    const newWidth = event.clientX - cursorOffset - sidebar.getBoundingClientRect().left;

    sidebar.style.width = `${newWidth}px`;
});

document.addEventListener('mouseup', () => {
    isResizing = false;
});